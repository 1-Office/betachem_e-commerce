<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Display and filter customer orders.
     */
    public function index(Request $request): View
    {
        $totalOrders = DB::table('orders')->count();

        $pendingOrders = DB::table('orders')
            ->where('status', 'pending')
            ->count();

        $processingOrders = DB::table('orders')
            ->whereIn('status', ['processing', 'ready', 'shipped'])
            ->count();

        $awaitingPayment = DB::table('orders')
            ->whereIn('payment_status', ['pending', 'partial'])
            ->count();

        $query = DB::table('orders');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhere('shipping_first_name', 'LIKE', "%{$search}%")
                    ->orWhere('shipping_last_name', 'LIKE', "%{$search}%")
                    ->orWhere('shipping_email', 'LIKE', "%{$search}%")
                    ->orWhere('shipping_phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $orders = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'awaitingPayment'
        ));
    }

    /**
     * Display one order and its payment information.
     */
    public function show(int $order): View
    {
        $order = DB::table('orders')
            ->where('id', $order)
            ->first();

        abort_unless($order, 404);

        $items = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $payments = DB::table('payments')
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->get();

        $payment = $payments->first();

        return view('admin.orders.show', compact(
            'order',
            'items',
            'payments',
            'payment'
        ));
    }

    /**
     * Update the fulfilment status of an order.
     */
    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,processing,ready,shipped,completed,cancelled',
            ],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $currentOrder = DB::table('orders')
                ->where('id', $order)
                ->lockForUpdate()
                ->first();

            abort_unless($currentOrder, 404);

            $oldStatus = $currentOrder->status;
            $newStatus = $validated['status'];

            if ($oldStatus === $newStatus) {
                return;
            }

            /*
             * Restore stock when an order is cancelled.
             */
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                $items = DB::table('order_items')
                    ->where('order_id', $order)
                    ->get();

                foreach ($items as $item) {
                    if ($item->product_id) {
                        DB::table('products')
                            ->where('id', $item->product_id)
                            ->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            /*
             * Re-deduct stock if a cancelled order is reopened.
             */
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                $items = DB::table('order_items')
                    ->where('order_id', $order)
                    ->get();

                foreach ($items as $item) {
                    if (!$item->product_id) {
                        continue;
                    }

                    $product = DB::table('products')
                        ->where('id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$product || $product->stock_quantity < $item->quantity) {
                        throw new \RuntimeException(
                            'Insufficient stock to reopen this order.'
                        );
                    }

                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            DB::table('orders')
                ->where('id', $order)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

            /*
             * Notify the customer about the order status change.
             */
            if ($currentOrder->user_id) {
                DB::table('notifications')->insert([
                    'user_id' => $currentOrder->user_id,
                    'order_id' => $order,
                    'type' => 'order_status',
                    'title' => 'Order status updated',
                    'message' => 'Your order ' . $currentOrder->order_number .
                        ' status is now ' . ucfirst($newStatus) . '.',
                    'is_read' => 0,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Verify or reject an order payment.
     *
     * Payment verification requires an uploaded PDF that
     * physically exists in private storage.
     */
    public function updatePaymentStatus(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => [
                'required',
                'in:successful,failed',
            ],
        ]);

        DB::transaction(function () use ($order, $validated) {

            /*
             * Lock the order while processing payment verification.
             */
            $currentOrder = DB::table('orders')
                ->where('id', $order)
                ->lockForUpdate()
                ->first();

            abort_unless($currentOrder, 404);

            /*
             * Never overwrite an order already marked as paid.
             */
            if ($currentOrder->payment_status === 'paid') {
                abort(
                    422,
                    'This order has already been marked as paid.'
                );
            }

            /*
             * Retrieve the latest payment record.
             */
            $payment = DB::table('payments')
                ->where('order_id', $order)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            /*
             * A payment record and uploaded proof are mandatory.
             */
            if (!$payment || !$payment->proof_of_payment) {
                abort(
                    422,
                    'Payment cannot be verified because no proof-of-payment PDF has been uploaded.'
                );
            }

            /*
             * Confirm the private PDF actually exists.
             */
            if (!Storage::disk('local')->exists($payment->proof_of_payment)) {
                abort(
                    422,
                    'Payment cannot be verified because the uploaded proof-of-payment PDF could not be found.'
                );
            }

            /*
             * Update the payment record.
             */
            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'status' => $validated['payment_status'],
                    'paid_at' => $validated['payment_status'] === 'successful'
                        ? ($payment->paid_at ?? now())
                        : null,
                    'updated_at' => now(),
                ]);

            /*
             * Update the corresponding order payment status.
             */
            $orderPaymentStatus = $validated['payment_status'] === 'successful'
                ? 'paid'
                : 'failed';

            DB::table('orders')
                ->where('id', $order)
                ->update([
                    'payment_status' => $orderPaymentStatus,
                    'updated_at' => now(),
                ]);

            /*
             * Notify the customer about the payment decision.
             */
            if ($currentOrder->user_id) {

                $message = $orderPaymentStatus === 'paid'
                    ? 'Payment for your order ' . $currentOrder->order_number .
                        ' has been verified successfully.'
                    : 'Payment for your order ' . $currentOrder->order_number .
                        ' could not be verified. Please contact Betachem.';

                DB::table('notifications')->insert([
                    'user_id' => $currentOrder->user_id,
                    'order_id' => $order,
                    'type' => 'payment_status',
                    'title' => $orderPaymentStatus === 'paid'
                        ? 'Payment verified'
                        : 'Payment not verified',
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                $validated['payment_status'] === 'successful'
                    ? 'Payment verified successfully.'
                    : 'Payment marked as unsuccessful.'
            );
    }

    /**
     * Securely download a customer's private proof-of-payment PDF.
     */
    public function downloadProof(int $order): StreamedResponse
    {
        $payment = DB::table('payments')
            ->where('order_id', $order)
            ->whereNotNull('proof_of_payment')
            ->orderByDesc('id')
            ->first();

        abort_unless($payment, 404);

        $path = $payment->proof_of_payment;

        abort_unless(
            Storage::disk('local')->exists($path),
            404,
            'Proof of payment could not be found.'
        );

        return Storage::disk('local')->download(
            $path,
            'Betachem-Proof-' . $order . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}

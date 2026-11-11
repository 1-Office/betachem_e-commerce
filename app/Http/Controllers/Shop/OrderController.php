<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    /**
     * Display the authenticated customer's order history.
     */
    public function index(): View
    {
        $user = Auth::user();

        $orders = DB::table('orders')
            ->where('user_id', $user->id)
            ->select([
                'id',
                'order_number',
                'status',
                'subtotal',
                'delivery_fee',
                'discount',
                'total',
                'payment_status',
                'payment_method',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('shop.account.orders.index', compact(
            'user',
            'orders'
        ));
    }

    /**
     * Display an individual order and its payment information.
     */
    public function show(int $id): View
    {
        $user = Auth::user();

        $order = DB::table('orders')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        abort_unless($order, 404);

        $items = DB::table('order_items')
            ->where('order_id', $order->id)
            ->select([
                'id',
                'product_id',
                'product_name',
                'sku',
                'price',
                'quantity',
                'subtotal',
            ])
            ->get();

        $payment = DB::table('payments')
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->first();

        return view('shop.account.orders.show', compact(
            'user',
            'order',
            'items',
            'payment'
        ));
    }

    /**
     * Upload EFT proof of payment for an order.
     */
    public function uploadProof(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        // Confirm that this order belongs to the logged-in customer.
        $order = DB::table('orders')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        abort_unless($order, 404);

        // Validate the uploaded PDF and EFT reference.
        $validated = $request->validate([
            'proof_of_payment' => [
                'required',
                'file',
                'mimes:pdf',
                'max:5120',
            ],
            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'proof_of_payment.required' => 'Please select your proof of payment PDF.',
            'proof_of_payment.mimes' => 'Only PDF documents are accepted.',
            'proof_of_payment.max' => 'Your PDF must not exceed 5 MB.',
        ]);

        $file = $request->file('proof_of_payment');

        $newPath = null;

        try {
            DB::transaction(function () use (
                $order,
                $validated,
                $file,
                &$newPath
            ) {
                // Lock the existing payment record while processing.
                $payment = DB::table('payments')
                    ->where('order_id', $order->id)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($payment && $payment->status === 'successful') {
                    abort(422, 'This order has already been marked as paid.');
                }

                // Store the PDF privately.
                $filename = Str::uuid() . '.pdf';

                $newPath = $file->storeAs(
                    'proof-of-payment',
                    $filename,
                    'local'
                );

                if (!$newPath) {
                    throw new \RuntimeException(
                        'The proof of payment could not be saved.'
                    );
                }

                $paymentData = [
                    'transaction_reference' =>
                        $validated['transaction_reference'] ?? null,
                    'proof_of_payment' => $newPath,
                    'proof_uploaded_at' => now(),
                    'payment_method' => 'EFT',
                    'updated_at' => now(),
                ];

                if ($payment) {
                    DB::table('payments')
                        ->where('id', $payment->id)
                        ->update($paymentData);
                } else {
                    DB::table('payments')->insert(array_merge(
                        $paymentData,
                        [
                            'order_id' => $order->id,
                            'amount' => $order->total,
                            'status' => 'pending',
                            'created_at' => now(),
                        ]
                    ));
                }
            });

            return redirect()
                ->route('shop.account.orders.show', $order->id)
                ->with('success', 'Your proof of payment has been uploaded successfully. It is awaiting verification.');
        } catch (Throwable $e) {
            // Remove the newly uploaded file if the database operation failed.
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                throw $e;
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'proof_of_payment' =>
                        'We could not upload your proof of payment. Please try again.',
                ]);
        }
    }
}

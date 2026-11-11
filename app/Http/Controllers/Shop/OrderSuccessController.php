<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderSuccessController extends Controller
{
    /**
     * Display the successful order confirmation.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $orderId = (int) $request->query('id', 0);

        if ($orderId <= 0) {
            return redirect()
                ->route('shop.account')
                ->with('error', 'The requested order could not be found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Load Customer Order
        |--------------------------------------------------------------------------
        */

        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return redirect()
                ->route('shop.account')
                ->with('error', 'The requested order could not be found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Load Order Items
        |--------------------------------------------------------------------------
        */

        $orderItems = DB::table('order_items')
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Payment Status Badge
        |--------------------------------------------------------------------------
        */

        $paymentStatus = strtolower(
            $order->payment_status ?? 'pending'
        );

        $paymentBadges = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'secondary',
            'partial' => 'info',
        ];

        $paymentBadge = $paymentBadges[$paymentStatus] ?? 'secondary';

        /*
        |--------------------------------------------------------------------------
        | Order Status Badge
        |--------------------------------------------------------------------------
        */

        $orderStatus = strtolower(
            $order->status ?? 'pending'
        );

        $orderBadges = [
            'pending' => 'warning',
            'processing' => 'info',
            'ready' => 'primary',
            'shipped' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        $orderBadge = $orderBadges[$orderStatus] ?? 'secondary';

        /*
        |--------------------------------------------------------------------------
        | Payment Method
        |--------------------------------------------------------------------------
        */

        $paymentMethod = $order->payment_method ?? 'pending';

        $paymentMethodLabel = match ($paymentMethod) {
            'eft' => 'EFT / Bank Transfer',
            'card' => 'Card Payment',
            'pending' => 'Payment Pending',
            default => ucfirst($paymentMethod),
        };

        return view('shop.checkout.success', compact(
            'order',
            'orderItems',
            'orderStatus',
            'orderBadge',
            'paymentStatus',
            'paymentBadge',
            'paymentMethodLabel'
        ));
    }
}

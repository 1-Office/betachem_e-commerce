<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the customer's account dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Order statistics.
        $orderStats = DB::table('orders')
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) AS total_orders')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN status IN (
                            'pending',
                            'processing',
                            'ready',
                            'shipped'
                        )
                        THEN 1
                        ELSE 0
                    END
                ) AS pending_orders
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN status = 'completed'
                        THEN 1
                        ELSE 0
                    END
                ) AS completed_orders
            ")
            ->first();

        $orderCount = (int) ($orderStats->total_orders ?? 0);
        $pendingOrders = (int) ($orderStats->pending_orders ?? 0);
        $completedOrders = (int) ($orderStats->completed_orders ?? 0);

        // Latest five customer orders.
        $recentOrders = DB::table('orders')
            ->where('user_id', $user->id)
            ->select([
                'id',
                'order_number',
                'status',
                'total',
                'payment_status',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('shop.account.index', compact(
            'user',
            'orderCount',
            'pendingOrders',
            'completedOrders',
            'recentOrders'
        ));
    }
}

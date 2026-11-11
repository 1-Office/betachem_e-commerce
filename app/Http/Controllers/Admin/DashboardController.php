<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Sales and Order Statistics
        |--------------------------------------------------------------------------
        | Revenue is based on paid orders only. Cancelled orders are excluded.
        */

        $paidOrdersQuery = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled');

        $totalSales = (clone $paidOrdersQuery)->sum('total');

        $totalOrders = DB::table('orders')->count();

        $pendingOrders = DB::table('orders')
            ->where('status', 'pending')
            ->count();

        $processingOrders = DB::table('orders')
            ->where('status', 'processing')
            ->count();

        $paidOrders = (clone $paidOrdersQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | Product Statistics
        |--------------------------------------------------------------------------
        */

        $totalProducts = DB::table('products')->count();

        $activeProducts = DB::table('products')
            ->where('is_active', 1)
            ->count();

        $lowStockProducts = DB::table('products')
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('stock_quantity', '>', 0)
            ->count();

        $outOfStockProducts = DB::table('products')
            ->where('stock_quantity', '<=', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Category and Customer Statistics
        |--------------------------------------------------------------------------
        */

        $totalCategories = DB::table('categories')->count();

        $totalCustomers = DB::table('users')
            ->where('role', 'customer')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = DB::table('orders')
            ->select(
                'id',
                'order_number',
                'status',
                'total',
                'payment_status',
                'shipping_first_name as first_name',
                'shipping_last_name as last_name',
                'shipping_email as email',
                'created_at'
            )
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Best-Selling Products
        |--------------------------------------------------------------------------
        */

        $bestSellingProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereNotIn('orders.payment_status', ['refunded'])
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy(
                'order_items.product_id',
                'order_items.product_name'
            )
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Stock Alerts
        |--------------------------------------------------------------------------
        */

        $stockAlerts = DB::table('products')
            ->select(
                'id',
                'name',
                'sku',
                'stock_quantity',
                'minimum_stock'
            )
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Contact Message Notifications
        |--------------------------------------------------------------------------
        */

        $unreadMessages = DB::table('contact_messages')
            ->where('status', 'new')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Order Status Chart
        |--------------------------------------------------------------------------
        */

        $statusLabels = [
            'pending',
            'processing',
            'ready',
            'shipped',
            'completed',
            'cancelled',
        ];

        $orderStatusCounts = DB::table('orders')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $orderStatusChart = [
            'labels' => array_map(
                fn ($status) => ucfirst($status),
                $statusLabels
            ),
            'values' => array_map(
                fn ($status) => (int) ($orderStatusCounts[$status] ?? 0),
                $statusLabels
            ),
        ];

        $orderStatuses = DB::table('orders')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Payment Status Chart
        |--------------------------------------------------------------------------
        */

        $paymentLabels = [
            'pending',
            'paid',
            'partial',
            'failed',
            'refunded',
        ];

        $paymentCounts = DB::table('orders')
            ->select(
                'payment_status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $paymentStatusChart = [
            'labels' => [
                'Pending',
                'Paid',
                'Partial',
                'Failed',
                'Refunded',
            ],
            'values' => array_map(
                fn ($status) => (int) ($paymentCounts[$status] ?? 0),
                $paymentLabels
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Daily Revenue - Last 30 Days
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();
        $dailyStart = $today->copy()->subDays(29);

        $dailyRevenueRows = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [
                $dailyStart->copy()->startOfDay(),
                $today->copy()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as sales_date, SUM(total) as revenue')
            ->groupBy('sales_date')
            ->get()
            ->keyBy('sales_date');

        $dailyRevenueLabels = [];
        $dailyRevenueValues = [];

        for ($date = $dailyStart->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');

            $dailyRevenueLabels[] = $date->format('d M');

            $dailyRevenueValues[] = round(
                (float) ($dailyRevenueRows[$key]->revenue ?? 0),
                2
            );
        }

        $dailyRevenueChart = [
            'labels' => $dailyRevenueLabels,
            'values' => $dailyRevenueValues,
        ];

        /*
        |--------------------------------------------------------------------------
        | Monthly Revenue - Last 12 Months
        |--------------------------------------------------------------------------
        */

        $firstMonth = Carbon::today()
            ->startOfMonth()
            ->subMonths(11);

        $monthlyRevenueRows = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [
                $firstMonth->copy()->startOfMonth(),
                Carbon::today()->endOfDay(),
            ])
            ->selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as sales_month,
                SUM(total) as revenue"
            )
            ->groupBy('sales_month')
            ->get()
            ->keyBy('sales_month');

        $monthlyRevenueLabels = [];
        $monthlyRevenueValues = [];

        for ($month = $firstMonth->copy(); $month->lte(Carbon::today()); $month->addMonth()) {
            $key = $month->format('Y-m');

            $monthlyRevenueLabels[] = $month->format('M Y');

            $monthlyRevenueValues[] = round(
                (float) ($monthlyRevenueRows[$key]->revenue ?? 0),
                2
            );
        }

        $monthlyRevenueChart = [
            'labels' => $monthlyRevenueLabels,
            'values' => $monthlyRevenueValues,
        ];

        /*
        |--------------------------------------------------------------------------
        | Last Updated
        |--------------------------------------------------------------------------
        */

        $lastUpdated = now()->format('d M Y, H:i:s');

        return view('admin.dashboard.index', compact(
            'totalSales',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'paidOrders',
            'totalProducts',
            'activeProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'totalCategories',
            'totalCustomers',
            'recentOrders',
            'bestSellingProducts',
            'stockAlerts',
            'unreadMessages',
            'orderStatuses',
            'orderStatusChart',
            'paymentStatusChart',
            'dailyRevenueChart',
            'monthlyRevenueChart',
            'lastUpdated'
        ));
    }
}

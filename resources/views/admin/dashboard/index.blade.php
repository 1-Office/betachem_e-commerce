@extends('layouts.admin')

@section('title', 'Admin Dashboard | Betachem')

@section('content')

<style>
.admin-dashboard {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 35px 0 60px;
    color: #24344d;
}

.admin-dashboard .dashboard-heading {
    font-weight: 800;
    color: #173b70;
}

.admin-dashboard .dashboard-subtitle {
    color: #718096;
}

.admin-dashboard .admin-hero {
    background: linear-gradient(120deg, #123b75, #2478cf);
    color: #fff;
    border-radius: 18px;
    padding: 30px;
    margin-bottom: 28px;
    box-shadow: 0 10px 25px rgba(25, 75, 140, .15);
}

.admin-dashboard .admin-hero h2 {
    font-weight: 800;
}

.admin-dashboard .admin-hero p {
    color: rgba(255,255,255,.82);
}

.admin-dashboard .dashboard-card,
.admin-dashboard .section-card,
.admin-dashboard .analytics-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 15px;
    padding: 22px;
    height: 100%;
    box-shadow: 0 4px 15px rgba(30, 55, 90, .04);
}

.admin-dashboard .stat-label {
    color: #718096;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.admin-dashboard .stat-value {
    font-size: 27px;
    font-weight: 800;
    color: #183b70;
}

.admin-dashboard .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.admin-dashboard .icon-blue {
    background: #e8f1ff;
    color: #2478cf;
}

.admin-dashboard .icon-green {
    background: #e4f8ed;
    color: #159957;
}

.admin-dashboard .icon-orange {
    background: #fff2df;
    color: #e28a19;
}

.admin-dashboard .icon-red {
    background: #ffe8e8;
    color: #dc3545;
}

.admin-dashboard .section-title {
    font-size: 17px;
    font-weight: 800;
    color: #183b70;
    margin-bottom: 20px;
}

.admin-dashboard .table th {
    color: #718096;
    font-size: 12px;
    text-transform: uppercase;
    white-space: nowrap;
}

.admin-dashboard .table td {
    font-size: 13px;
    vertical-align: middle;
}

.admin-dashboard .table > :not(caption) > * > * {
    padding: 13px 10px;
}

.admin-dashboard .quick-action {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    color: #263f60;
    text-decoration: none;
    background: #fff;
    transition: .2s ease;
    height: 100%;
}

.admin-dashboard .quick-action:hover {
    border-color: #2478cf;
    background: #f5f9ff;
    transform: translateY(-2px);
}

.admin-dashboard .quick-action i {
    font-size: 20px;
    color: #2478cf;
    width: 25px;
    text-align: center;
}

.admin-dashboard .quick-action span {
    font-size: 13px;
    font-weight: 700;
}

.admin-dashboard .status-badge {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
}

.admin-dashboard .status-pending {
    background: #fff2d9;
    color: #a66b00;
}

.admin-dashboard .status-processing {
    background: #e5f0ff;
    color: #1764b0;
}

.admin-dashboard .status-ready {
    background: #e8e6ff;
    color: #5745a8;
}

.admin-dashboard .status-shipped {
    background: #e1f5f5;
    color: #087f83;
}

.admin-dashboard .status-completed {
    background: #e2f7e9;
    color: #16834b;
}

.admin-dashboard .status-cancelled,
.admin-dashboard .status-refunded {
    background: #ffe5e5;
    color: #bd3030;
}

.admin-dashboard .stock-item {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 0;
    border-bottom: 1px solid #edf0f5;
    font-size: 13px;
}

.admin-dashboard .stock-item:last-child {
    border-bottom: 0;
}

.admin-dashboard .stock-count {
    font-weight: 800;
    color: #dc3545;
}

.admin-dashboard .empty-state {
    color: #8a97a9;
    text-align: center;
    padding: 25px 10px;
    font-size: 13px;
}

.admin-dashboard .btn-dashboard {
    border-radius: 9px;
    font-weight: 700;
    padding: 10px 16px;
}

.admin-dashboard .analytics-card {
    min-height: 340px;
}

.admin-dashboard .analytics-chart {
    position: relative;
    width: 100%;
    height: 270px;
}

.admin-dashboard .analytics-chart.chart-small {
    height: 250px;
}

.admin-dashboard .analytics-subtitle {
    color: #8491a3;
    font-size: 12px;
    margin-top: -12px;
    margin-bottom: 20px;
}

.admin-dashboard .dashboard-refresh {
    color: #718096;
    font-size: 12px;
}

.admin-dashboard .refresh-button {
    border-radius: 9px;
    font-weight: 700;
}

@media (max-width: 767px) {
    .admin-dashboard {
        padding-top: 22px;
    }

    .admin-dashboard .admin-hero {
        padding: 23px;
    }

    .admin-dashboard .stat-value {
        font-size: 23px;
    }

    .admin-dashboard .analytics-card {
        min-height: 320px;
    }

    .admin-dashboard .analytics-chart {
        height: 240px;
    }
}
</style>

<div class="admin-dashboard">

    <div class="container-fluid px-3 px-lg-4">

        {{-- Dashboard Heading --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

            <div>
                <h2 class="dashboard-heading mb-1">
                    <i class="fa fa-dashboard me-2"></i>
                    Admin Dashboard
                </h2>

                <p class="dashboard-subtitle mb-0">
                    Manage and monitor your Betachem online store.
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('shop.home') }}"
                   class="btn btn-outline-primary btn-dashboard"
                   target="_blank">
                    <i class="fa fa-external-link me-1"></i>
                    View Store
                </a>
            </div>

        </div>

        {{-- Welcome Hero --}}
        <div class="admin-hero">

            <div class="row align-items-center">

                <div class="col-lg-8">
                    <h2 class="mb-2">
                        Welcome to Betachem Administration
                    </h2>

                    <p class="mb-0">
                        Manage products, orders, customers, stock and your
                        online store from one central dashboard.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ url('/admin/products/create') }}"
                       class="btn btn-light btn-dashboard">
                        <i class="fa fa-plus me-1"></i>
                        Add Product
                    </a>
                </div>

            </div>

        </div>

        {{-- Business Insights Heading --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">

            <div>
                <h4 class="dashboard-heading mb-1">
                    <i class="fa fa-line-chart me-2"></i>
                    Business Insights
                </h4>

                <p class="dashboard-subtitle mb-0">
                    Monitor sales, orders and payment performance.
                </p>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">

                <span class="dashboard-refresh">
                    Last updated:
                    <strong id="dashboardLastUpdated">{{ $lastUpdated }}</strong>
                </span>

                <button type="button"
                        id="dashboardRefreshButton"
                        class="btn btn-outline-primary btn-sm refresh-button">
                    <i class="fa fa-refresh me-1"></i>
                    Refresh
                </button>

            </div>

        </div>

        {{-- Revenue Charts --}}
        <div class="row g-4 mb-4">

            <div class="col-xl-7">

                <div class="analytics-card">

                    <h5 class="section-title">
                        <i class="fa fa-line-chart me-2"></i>
                        Daily Revenue
                    </h5>

                    <p class="analytics-subtitle">
                        Paid sales over the last 30 days.
                    </p>

                    <div class="analytics-chart">
                        <canvas id="dailyRevenueChart"></canvas>
                    </div>

                </div>

            </div>

            <div class="col-xl-5">

                <div class="analytics-card">

                    <h5 class="section-title">
                        <i class="fa fa-bar-chart me-2"></i>
                        Monthly Revenue
                    </h5>

                    <p class="analytics-subtitle">
                        Paid sales over the last 12 months.
                    </p>

                    <div class="analytics-chart">
                        <canvas id="monthlyRevenueChart"></canvas>
                    </div>

                </div>

            </div>

        </div>

        {{-- Order and Payment Charts --}}
        <div class="row g-4 mb-4">

            <div class="col-md-6">

                <div class="analytics-card">

                    <h5 class="section-title">
                        <i class="fa fa-shopping-bag me-2"></i>
                        Order Status Distribution
                    </h5>

                    <p class="analytics-subtitle">
                        See how your orders are progressing.
                    </p>

                    <div class="analytics-chart chart-small">
                        <canvas id="orderStatusChart"></canvas>
                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="analytics-card">

                    <h5 class="section-title">
                        <i class="fa fa-credit-card me-2"></i>
                        Payment Status Distribution
                    </h5>

                    <p class="analytics-subtitle">
                        Monitor customer payment progress.
                    </p>

                    <div class="analytics-chart chart-small">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>

                </div>

            </div>

        </div>

        {{-- Main Statistics --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Paid Sales</div>
                            <div class="stat-value">
                                R{{ number_format((float) $totalSales, 2) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fa fa-money"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Total Orders</div>
                            <div class="stat-value">
                                {{ number_format($totalOrders) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fa fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Pending Orders</div>
                            <div class="stat-value">
                                {{ number_format($pendingOrders) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-orange">
                            <i class="fa fa-clock-o"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Processing Orders</div>
                            <div class="stat-value">
                                {{ number_format($processingOrders) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fa fa-refresh"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Total Products</div>
                            <div class="stat-value">
                                {{ number_format($totalProducts) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fa fa-cubes"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Active Products</div>
                            <div class="stat-value">
                                {{ number_format($activeProducts) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Customers</div>
                            <div class="stat-value">
                                {{ number_format($totalCustomers) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Paid Orders</div>
                            <div class="stat-value">
                                {{ number_format($paidOrders) }}
                            </div>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fa fa-credit-card"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Stock Statistics --}}
        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="stat-label">Total Categories</div>
                    <div class="stat-value">
                        {{ number_format($totalCategories) }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="stat-label">Low Stock Products</div>
                    <div class="stat-value text-warning">
                        {{ number_format($lowStockProducts) }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="stat-label">Out of Stock</div>
                    <div class="stat-value text-danger">
                        {{ number_format($outOfStockProducts) }}
                    </div>
                </div>
            </div>

        </div>

        {{-- Quick Actions --}}
        <div class="section-card mb-4">

            <h5 class="section-title">
                <i class="fa fa-bolt me-2"></i>
                Quick Actions
            </h5>

            <div class="row g-3">

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/products') }}" class="quick-action">
                        <i class="fa fa-cubes"></i>
                        <span>Manage Products</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/categories') }}" class="quick-action">
                        <i class="fa fa-tags"></i>
                        <span>Manage Categories</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/orders') }}" class="quick-action">
                        <i class="fa fa-shopping-cart"></i>
                        <span>Manage Orders</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/customers') }}" class="quick-action">
                        <i class="fa fa-users"></i>
                        <span>Manage Customers</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/contact-messages') }}" class="quick-action">
                        <i class="fa fa-envelope"></i>
                        <span>Contact Messages</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/orders?status=pending') }}" class="quick-action">
                        <i class="fa fa-clock-o"></i>
                        <span>Pending Orders</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ url('/admin/products?stock=low') }}" class="quick-action">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span>Stock Management</span>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <a href="{{ route('shop.home') }}" target="_blank" class="quick-action">
                        <i class="fa fa-globe"></i>
                        <span>Visit Online Store</span>
                    </a>
                </div>

            </div>

        </div>

        {{-- Recent Orders and Stock Alerts --}}
        <div class="row g-4 mb-4">

            <div class="col-xl-8">

                <div class="section-card">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title mb-0">
                            <i class="fa fa-shopping-bag me-2"></i>
                            Recent Orders
                        </h5>

                        <a href="{{ url('/admin/orders') }}"
                           class="small text-primary text-decoration-none">
                            View All
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">

                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $order->order_number ?? '#'.$order->id }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ trim(($order->first_name ?? '').' '.($order->last_name ?? '')) ?: ($order->email ?? 'Customer') }}
                                        </td>

                                        <td>
                                            {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d M Y') : '—' }}
                                        </td>

                                        <td>
                                            <strong>
                                                R{{ number_format((float) $order->total, 2) }}
                                            </strong>
                                        </td>

                                        <td>
                                            @php
                                                $status = strtolower($order->status ?? 'pending');
                                            @endphp

                                            <span class="status-badge status-{{ $status }}">
                                                {{ str_replace('_', ' ', $status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            No orders have been placed yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

            <div class="col-xl-4">

                <div class="section-card">

                    <h5 class="section-title">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        Stock Alerts
                    </h5>

                    @forelse($stockAlerts as $product)

                        <div class="stock-item">
                            <div>
                                <strong>{{ $product->name }}</strong>

                                <div class="text-muted small">
                                    SKU: {{ $product->sku ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="stock-count">
                                {{ $product->stock_quantity }} left
                            </div>
                        </div>

                    @empty

                        <div class="empty-state">
                            <i class="fa fa-check-circle text-success fa-2x mb-2"></i>
                            <div>All products have sufficient stock.</div>
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Best Sellers and Order Status --}}
        <div class="row g-4">

            <div class="col-xl-7">

                <div class="section-card">

                    <h5 class="section-title">
                        <i class="fa fa-line-chart me-2"></i>
                        Best-Selling Products
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($bestSellingProducts as $product)
                                    <tr>
                                        <td>
                                            <strong>{{ $product->product_name }}</strong>
                                        </td>

                                        <td>
                                            {{ number_format($product->units_sold) }}
                                        </td>

                                        <td>
                                            R{{ number_format((float) $product->revenue, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty-state">
                                            No product sales recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

            <div class="col-xl-5">

                <div class="section-card">

                    <h5 class="section-title">
                        <i class="fa fa-bar-chart me-2"></i>
                        Order Status Summary
                    </h5>

                    @forelse($orderStatuses as $statusItem)

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                            <span class="text-capitalize fw-semibold">
                                {{ str_replace('_', ' ', $statusItem->status) }}
                            </span>

                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                {{ number_format($statusItem->total) }}
                            </span>

                        </div>

                    @empty

                        <div class="empty-state">
                            No order status information available.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Notifications Summary --}}
        <div class="section-card mt-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <h5 class="section-title mb-1">
                        <i class="fa fa-bell me-2"></i>
                        Notifications
                    </h5>

                    <p class="text-muted small mb-0">
                        You have {{ $unreadMessages }} unread contact message(s).
                    </p>
                </div>

                <a href="{{ url('/admin/contact-messages') }}"
                   class="btn btn-outline-primary btn-dashboard">
                    <i class="fa fa-envelope me-1"></i>
                    View Messages
                </a>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Chart Data From Laravel
    |--------------------------------------------------------------------------
    */

    const dailyRevenue = @json($dailyRevenueChart);
    const monthlyRevenue = @json($monthlyRevenueChart);
    const orderStatuses = @json($orderStatusChart);
    const paymentStatuses = @json($paymentStatusChart);

    const moneyFormat = function (value) {
        return 'R' + Number(value || 0).toLocaleString('en-ZA', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Chart Defaults
    |--------------------------------------------------------------------------
    */

    if (typeof Chart !== 'undefined') {

        Chart.defaults.font.family = 'Arial, sans-serif';
        Chart.defaults.color = '#718096';

        /*
        |--------------------------------------------------------------------------
        | Daily Revenue Chart
        |--------------------------------------------------------------------------
        */

        const dailyCanvas = document.getElementById('dailyRevenueChart');

        if (dailyCanvas) {
            new Chart(dailyCanvas, {
                type: 'line',

                data: {
                    labels: dailyRevenue.labels,

                    datasets: [{
                        label: 'Paid Revenue',
                        data: dailyRevenue.values,
                        borderColor: '#2478cf',
                        backgroundColor: 'rgba(36, 120, 207, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        pointHoverRadius: 6
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },

                    plugins: {
                        legend: {
                            display: false
                        },

                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return moneyFormat(context.parsed.y);
                                }
                            }
                        }
                    },

                    scales: {
                        y: {
                            beginAtZero: true,

                            ticks: {
                                callback: function (value) {
                                    return 'R' + Number(value).toLocaleString('en-ZA');
                                }
                            },

                            grid: {
                                color: '#edf1f7'
                            }
                        },

                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Monthly Revenue Chart
        |--------------------------------------------------------------------------
        */

        const monthlyCanvas = document.getElementById('monthlyRevenueChart');

        if (monthlyCanvas) {
            new Chart(monthlyCanvas, {
                type: 'bar',

                data: {
                    labels: monthlyRevenue.labels,

                    datasets: [{
                        label: 'Paid Revenue',
                        data: monthlyRevenue.values,
                        backgroundColor: '#2478cf',
                        borderRadius: 6,
                        maxBarThickness: 35
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false
                        },

                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return moneyFormat(context.parsed.y);
                                }
                            }
                        }
                    },

                    scales: {
                        y: {
                            beginAtZero: true,

                            ticks: {
                                callback: function (value) {
                                    return 'R' + Number(value).toLocaleString('en-ZA');
                                }
                            },

                            grid: {
                                color: '#edf1f7'
                            }
                        },

                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Order Status Doughnut Chart
        |--------------------------------------------------------------------------
        */

        const orderCanvas = document.getElementById('orderStatusChart');

        if (orderCanvas) {
            new Chart(orderCanvas, {
                type: 'doughnut',

                data: {
                    labels: orderStatuses.labels,

                    datasets: [{
                        data: orderStatuses.values,

                        backgroundColor: [
                            '#f0ad35',
                            '#2478cf',
                            '#7566d9',
                            '#16a6a6',
                            '#159957',
                            '#dc3545'
                        ],

                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 7
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    },

                    cutout: '62%'
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Status Doughnut Chart
        |--------------------------------------------------------------------------
        */

        const paymentCanvas = document.getElementById('paymentStatusChart');

        if (paymentCanvas) {
            new Chart(paymentCanvas, {
                type: 'doughnut',

                data: {
                    labels: paymentStatuses.labels,

                    datasets: [{
                        data: paymentStatuses.values,

                        backgroundColor: [
                            '#f0ad35',
                            '#159957',
                            '#2478cf',
                            '#dc3545',
                            '#7d8794'
                        ],

                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 7
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    },

                    cutout: '62%'
                }
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Manual Refresh
    |--------------------------------------------------------------------------
    */

    const refreshButton = document.getElementById('dashboardRefreshButton');

    if (refreshButton) {
        refreshButton.addEventListener('click', function () {
            refreshButton.disabled = true;
            refreshButton.innerHTML =
                '<i class="fa fa-spinner fa-spin me-1"></i> Refreshing...';

            window.location.reload();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Automatic Dashboard Refresh - Every 60 Seconds
    |--------------------------------------------------------------------------
    */

    window.setInterval(function () {

        if (!document.hidden) {
            window.location.reload();
        }

    }, 60000);

});
</script>

@endpush

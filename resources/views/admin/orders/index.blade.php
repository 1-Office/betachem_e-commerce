@extends('layouts.admin')

@section('title', 'Order Management | Betachem')

@section('content')

<style>
.admin-orders {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-orders .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-orders .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-orders .page-heading p {
    color: #77849a;
    margin: 0;
}

.admin-orders .summary-card,
.admin-orders .filter-card,
.admin-orders .orders-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
}

.admin-orders .summary-card {
    padding: 20px;
    height: 100%;
}

.admin-orders .summary-icon {
    width: 45px;
    height: 45px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    margin-bottom: 13px;
}

.admin-orders .summary-number {
    font-size: 27px;
    font-weight: 800;
    color: #263650;
    line-height: 1.2;
}

.admin-orders .summary-label {
    font-size: 13px;
    color: #7b8799;
    margin-top: 5px;
}

.admin-orders .filter-card {
    padding: 20px;
    margin-bottom: 22px;
}

.admin-orders .filter-card label {
    font-size: 12px;
    font-weight: 700;
    color: #64738b;
    margin-bottom: 7px;
}

.admin-orders .form-control,
.admin-orders .form-select {
    min-height: 43px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-orders .form-control:focus,
.admin-orders .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-orders .btn-search {
    background: #1745ca;
    color: #fff;
    border-radius: 8px;
    min-height: 43px;
    font-weight: 600;
    padding: 0 20px;
    border: 0;
}

.admin-orders .btn-search:hover {
    background: #2d208a;
    color: #fff;
}

.admin-orders .btn-reset {
    background: #eef2f8;
    color: #46566f;
    border-radius: 8px;
    min-height: 43px;
    font-weight: 600;
    padding: 0 17px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.admin-orders .orders-card {
    overflow: hidden;
}

.admin-orders .orders-card-header {
    padding: 19px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    border-bottom: 1px solid #edf0f5;
}

.admin-orders .orders-card-header h5 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}

.admin-orders .orders-card-header span {
    color: #8994a6;
    font-size: 12px;
}

.admin-orders .table {
    margin-bottom: 0;
    vertical-align: middle;
}

.admin-orders .table thead th {
    background: #f8fafd;
    color: #758198;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .45px;
    padding: 15px 16px;
    border-bottom: 1px solid #e9edf4;
    white-space: nowrap;
}

.admin-orders .table tbody td {
    padding: 15px 16px;
    border-color: #edf0f5;
    font-size: 13px;
}

.admin-orders .order-number {
    color: #1745ca;
    font-weight: 800;
    text-decoration: none;
}

.admin-orders .order-number:hover {
    color: #2d208a;
    text-decoration: underline;
}

.admin-orders .customer-name {
    color: #263650;
    font-weight: 700;
    margin-bottom: 4px;
}

.admin-orders .customer-detail {
    color: #8994a6;
    font-size: 11px;
    overflow-wrap: anywhere;
}

.admin-orders .order-total {
    font-weight: 800;
    color: #263650;
    white-space: nowrap;
}

.admin-orders .status-badge {
    display: inline-block;
    border-radius: 30px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-orders .status-pending {
    background: #fff3d9;
    color: #a36b00;
}

.admin-orders .status-processing {
    background: #e8efff;
    color: #2854bd;
}

.admin-orders .status-ready {
    background: #e5f4ff;
    color: #1670a5;
}

.admin-orders .status-shipped {
    background: #f0eaff;
    color: #6843b9;
}

.admin-orders .status-completed {
    background: #e5f7ed;
    color: #18834b;
}

.admin-orders .status-cancelled {
    background: #fde8e8;
    color: #bd3434;
}

.admin-orders .payment-pending {
    background: #fff3d9;
    color: #a36b00;
}

.admin-orders .payment-paid {
    background: #e5f7ed;
    color: #18834b;
}

.admin-orders .payment-failed {
    background: #fde8e8;
    color: #bd3434;
}

.admin-orders .payment-refunded {
    background: #f0eaff;
    color: #6843b9;
}

.admin-orders .payment-partial {
    background: #e8efff;
    color: #2854bd;
}

.admin-orders .btn-view {
    background: #eef2ff;
    color: #1745ca;
    border: 1px solid #dce5ff;
    border-radius: 7px;
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

.admin-orders .btn-view:hover {
    background: #1745ca;
    color: #fff;
}

.admin-orders .empty-state {
    text-align: center;
    padding: 55px 20px;
}

.admin-orders .empty-state i {
    font-size: 42px;
    color: #c5cedc;
    margin-bottom: 15px;
}

.admin-orders .empty-state h5 {
    color: #34445e;
    font-weight: 800;
}

.admin-orders .empty-state p {
    color: #8994a6;
    font-size: 13px;
}

.admin-orders .pagination {
    margin: 0;
}

.admin-orders .pagination .page-link {
    color: #1745ca;
    border-color: #e8edf5;
    font-size: 12px;
}

.admin-orders .pagination .active .page-link {
    background: #1745ca;
    border-color: #1745ca;
    color: #fff;
}

@media (max-width: 767px) {
    .admin-orders {
        padding: 20px 0 40px;
    }

    .admin-orders .page-heading h2 {
        font-size: 23px;
    }

    .admin-orders .summary-number {
        font-size: 23px;
    }
}
</style>

@php
    $orderStatusClasses = [
        'pending' => 'status-pending',
        'processing' => 'status-processing',
        'ready' => 'status-ready',
        'shipped' => 'status-shipped',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled',
    ];

    $paymentStatusClasses = [
        'pending' => 'payment-pending',
        'paid' => 'payment-paid',
        'failed' => 'payment-failed',
        'refunded' => 'payment-refunded',
        'partial' => 'payment-partial',
    ];
@endphp

<div class="admin-orders">
    <div class="container-fluid px-3 px-lg-4">

        {{-- Page Heading --}}
        <div class="page-heading">
            <div>
                <h2>Order Management</h2>
                <p>Monitor customer orders, process fulfilment and verify payments.</p>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#e8efff;color:#1745ca;">
                        <i class="fa fa-shopping-bag"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($totalOrders) }}
                    </div>
                    <div class="summary-label">Total Orders</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#fff3d9;color:#a36b00;">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($pendingOrders) }}
                    </div>
                    <div class="summary-label">Pending Orders</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#e5f4ff;color:#1670a5;">
                        <i class="fa fa-truck"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($processingOrders) }}
                    </div>
                    <div class="summary-label">Being Processed</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#fde8e8;color:#bd3434;">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($awaitingPayment) }}
                    </div>
                    <div class="summary-label">Awaiting Payment</div>
                </div>
            </div>

        </div>

        {{-- Search and Filters --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-lg-5">
                        <label for="search">Search Orders</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Order number, customer name, email or phone"
                        >
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="status">Order Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'processing', 'ready', 'shipped', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}"
                                    {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="payment_status">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All payments</option>
                            @foreach (['pending', 'paid', 'partial', 'failed', 'refunded'] as $status)
                                <option value="{{ $status }}"
                                    {{ request('payment_status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-8 col-lg-2">
                        <button type="submit" class="btn btn-search w-100">
                            <i class="fa fa-search me-1"></i> Search
                        </button>
                    </div>

                    <div class="col-4 col-lg-1">
                        <a href="{{ route('admin.orders.index') }}"
                           class="btn-reset w-100">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- Orders Table --}}
        <div class="orders-card">

            <div class="orders-card-header">
                <div>
                    <h5>Customer Orders</h5>
                    <span>
                        Showing {{ $orders->firstItem() ?? 0 }}
                        to {{ $orders->lastItem() ?? 0 }}
                        of {{ number_format($orders->total()) }} orders
                    </span>
                </div>
            </div>

            @if ($orders->count())

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Order Total</th>
                                <th>Order Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                           class="order-number">
                                            {{ $order->order_number }}
                                        </a>
                                        <div class="customer-detail">
                                            Order #{{ $order->id }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="customer-name">
                                            {{ $order->shipping_first_name }}
                                            {{ $order->shipping_last_name }}
                                        </div>
                                        <div class="customer-detail">
                                            {{ $order->shipping_email }}
                                        </div>
                                        <div class="customer-detail">
                                            {{ $order->shipping_phone }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="customer-name">
                                            {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                                        </div>
                                        <div class="customer-detail">
                                            {{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}
                                        </div>
                                    </td>

                                    <td class="order-total">
                                        R {{ number_format((float) $order->total, 2) }}
                                    </td>

                                    <td>
                                        <span class="status-badge {{ $orderStatusClasses[$order->status] ?? 'status-pending' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-badge {{ $paymentStatusClasses[$order->payment_status] ?? 'payment-pending' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                           class="btn-view">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <small class="text-muted">
                        Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}
                    </small>

                    {{ $orders->links() }}
                </div>

            @else

                <div class="empty-state">
                    <i class="fa fa-shopping-bag"></i>
                    <h5>No Orders Found</h5>
                    <p>
                        There are no orders matching your search or filter criteria.
                    </p>

                    @if (request()->hasAny(['search', 'status', 'payment_status']))
                        <a href="{{ route('admin.orders.index') }}"
                           class="btn btn-primary btn-sm">
                            Clear Filters
                        </a>
                    @endif
                </div>

            @endif

        </div>

    </div>
</div>

@endsection

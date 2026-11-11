@extends('layouts.admin')

@section('title', 'Customer Details | Betachem')

@section('content')

<style>
.admin-customer-details {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-customer-details .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-customer-details .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-customer-details .page-heading p {
    color: #77849a;
    margin: 0;
    font-size: 13px;
}

.admin-customer-details .panel,
.admin-customer-details .summary-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
    margin-bottom: 22px;
}

.admin-customer-details .panel {
    overflow: hidden;
}

.admin-customer-details .panel-header {
    padding: 18px 22px;
    border-bottom: 1px solid #edf0f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-customer-details .panel-header h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #263650;
}

.admin-customer-details .panel-body {
    padding: 22px;
}

.admin-customer-details .profile-header {
    display: flex;
    align-items: center;
    gap: 17px;
}

.admin-customer-details .profile-avatar {
    width: 68px;
    height: 68px;
    border-radius: 16px;
    background: #edf1ff;
    color: #1745ca;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
    font-weight: 900;
    flex-shrink: 0;
}

.admin-customer-details .profile-name {
    font-size: 20px;
    font-weight: 900;
    color: #263650;
    margin-bottom: 5px;
}

.admin-customer-details .profile-email {
    font-size: 12px;
    color: #7b8799;
    overflow-wrap: anywhere;
}

.admin-customer-details .summary-card {
    padding: 20px;
    height: 100%;
    margin-bottom: 0;
}

.admin-customer-details .summary-icon {
    width: 43px;
    height: 43px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 13px;
}

.admin-customer-details .summary-number {
    font-size: 25px;
    font-weight: 900;
    color: #263650;
    line-height: 1.3;
}

.admin-customer-details .summary-label {
    font-size: 12px;
    color: #7b8799;
    margin-top: 5px;
}

.admin-customer-details .detail-block {
    margin-bottom: 20px;
}

.admin-customer-details .detail-block:last-child {
    margin-bottom: 0;
}

.admin-customer-details .detail-label {
    font-size: 10px;
    font-weight: 800;
    color: #8994a6;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 6px;
}

.admin-customer-details .detail-value {
    font-size: 13px;
    font-weight: 600;
    color: #263650;
    overflow-wrap: anywhere;
}

.admin-customer-details .status-badge {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 800;
    text-transform: capitalize;
}

.admin-customer-details .status-active {
    background: #e6f7ed;
    color: #16834a;
}

.admin-customer-details .status-inactive {
    background: #edf0f5;
    color: #657187;
}

.admin-customer-details .status-blocked {
    background: #fdebec;
    color: #c03945;
}

.admin-customer-details .form-select {
    min-height: 43px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-customer-details .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-customer-details .btn-update {
    min-height: 42px;
    padding: 0 18px;
    border-radius: 8px;
    background: #1745ca;
    border: 1px solid #1745ca;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
}

.admin-customer-details .btn-update:hover {
    background: #2d208a;
    color: #fff;
}

.admin-customer-details .table {
    margin: 0;
    vertical-align: middle;
}

.admin-customer-details .table thead th {
    background: #f8fafd;
    color: #77849a;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 15px 17px;
    border-bottom: 1px solid #e9eef5;
    white-space: nowrap;
}

.admin-customer-details .table tbody td {
    padding: 15px 17px;
    border-bottom: 1px solid #f0f2f6;
    font-size: 12px;
    color: #53627a;
}

.admin-customer-details .status-order {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 30px;
    background: #edf1ff;
    color: #1745ca;
    font-size: 10px;
    font-weight: 800;
    text-transform: capitalize;
}

.admin-customer-details .payment-paid {
    background: #e6f7ed;
    color: #16834a;
}

.admin-customer-details .payment-pending {
    background: #fff4df;
    color: #a96b00;
}

.admin-customer-details .payment-failed,
.admin-customer-details .payment-refunded {
    background: #fdebec;
    color: #c03945;
}

.admin-customer-details .btn-order {
    background: #edf2ff;
    color: #1745ca;
    border: 1px solid #dce5ff;
    padding: 7px 11px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
}

.admin-customer-details .btn-order:hover {
    background: #1745ca;
    color: #fff;
}

.admin-customer-details .empty-state {
    padding: 45px 20px;
    text-align: center;
    color: #8994a6;
    font-size: 13px;
}

.admin-customer-details .table-footer {
    padding: 17px 22px;
    border-top: 1px solid #edf0f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.admin-customer-details .pagination {
    margin: 0;
}

.admin-customer-details .pagination .page-link {
    color: #1745ca;
    font-size: 12px;
}

.admin-customer-details .pagination .active .page-link {
    background: #1745ca;
    border-color: #1745ca;
    color: #fff;
}

.admin-customer-details .alert {
    border-radius: 9px;
    font-size: 13px;
}

@media(max-width:767px) {
    .admin-customer-details {
        padding: 20px 0 40px;
    }

    .admin-customer-details .page-heading h2 {
        font-size: 23px;
    }

    .admin-customer-details .panel-body {
        padding: 17px;
    }
}
</style>

<div class="admin-customer-details">
    <div class="container-fluid px-3 px-lg-4">

        {{-- Heading --}}
        <div class="page-heading">
            <div>
                <h2>Customer Details</h2>
                <p>View customer information, account activity and order history.</p>
            </div>

            <a href="{{ route('admin.customers.index') }}"
               class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="fa fa-arrow-left me-1"></i> Back to Customers
            </a>
        </div>

        {{-- Notifications --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Customer Profile --}}
        <div class="panel">
            <div class="panel-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div class="profile-header">
                        <div class="profile-avatar">
                            {{ strtoupper(substr($customer->first_name ?? '', 0, 1) . substr($customer->last_name ?? '', 0, 1)) }}
                        </div>

                        <div>
                            <div class="profile-name">
                                {{ $customer->first_name }} {{ $customer->last_name }}
                            </div>

                            <div class="profile-email">
                                {{ $customer->email }}
                            </div>

                            <div class="mt-2">
                                <span class="status-badge status-{{ $customer->status }}">
                                    {{ ucfirst($customer->status) }} Account
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted small">
                        Customer ID: <strong>#{{ $customer->id }}</strong>
                    </div>

                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row g-3 mb-4">

            <div class="col-md-6 col-xl-4">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#edf1ff;color:#1745ca;">
                        <i class="fa fa-shopping-bag"></i>
                    </div>

                    <div class="summary-number">
                        {{ number_format($orderSummary['count']) }}
                    </div>

                    <div class="summary-label">Total Orders</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#e6f7ed;color:#16834a;">
                        <i class="fa fa-money"></i>
                    </div>

                    <div class="summary-number">
                        R{{ number_format((float) $orderSummary['total_spent'], 2) }}
                    </div>

                    <div class="summary-label">Total Paid Orders</div>
                </div>
            </div>

            <div class="col-md-12 col-xl-4">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#fff4df;color:#a96b00;">
                        <i class="fa fa-calendar"></i>
                    </div>

                    <div class="summary-number" style="font-size:20px;">
                        {{ $customer->created_at
                            ? \Carbon\Carbon::parse($customer->created_at)->format('d M Y')
                            : '—' }}
                    </div>

                    <div class="summary-label">Customer Since</div>
                </div>
            </div>

        </div>

        <div class="row g-4">

            {{-- Customer Information --}}
            <div class="col-lg-7">

                <div class="panel">
                    <div class="panel-header">
                        <h5>
                            <i class="fa fa-user-circle me-2 text-primary"></i>
                            Personal Information
                        </h5>
                    </div>

                    <div class="panel-body">
                        <div class="row">

                            <div class="col-md-6 detail-block">
                                <div class="detail-label">First Name</div>
                                <div class="detail-value">{{ $customer->first_name }}</div>
                            </div>

                            <div class="col-md-6 detail-block">
                                <div class="detail-label">Last Name</div>
                                <div class="detail-value">{{ $customer->last_name }}</div>
                            </div>

                            <div class="col-md-6 detail-block">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value">{{ $customer->email }}</div>
                            </div>

                            <div class="col-md-6 detail-block">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value">{{ $customer->phone ?: 'Not provided' }}</div>
                            </div>

                            <div class="col-12 detail-block">
                                <div class="detail-label">Street Address</div>
                                <div class="detail-value">{{ $customer->address ?: 'Not provided' }}</div>
                            </div>

                            <div class="col-md-4 detail-block">
                                <div class="detail-label">City</div>
                                <div class="detail-value">{{ $customer->city ?: 'Not provided' }}</div>
                            </div>

                            <div class="col-md-4 detail-block">
                                <div class="detail-label">Province</div>
                                <div class="detail-value">{{ $customer->province ?: 'Not provided' }}</div>
                            </div>

                            <div class="col-md-4 detail-block">
                                <div class="detail-label">Postal Code</div>
                                <div class="detail-value">{{ $customer->postal_code ?: 'Not provided' }}</div>
                            </div>

                            <div class="col-md-6 detail-block">
                                <div class="detail-label">Account Created</div>
                                <div class="detail-value">
                                    {{ $customer->created_at
                                        ? \Carbon\Carbon::parse($customer->created_at)->format('d M Y, H:i')
                                        : '—' }}
                                </div>
                            </div>

                            <div class="col-md-6 detail-block">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value">
                                    {{ $customer->updated_at
                                        ? \Carbon\Carbon::parse($customer->updated_at)->format('d M Y, H:i')
                                        : '—' }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Order History --}}
                <div class="panel">
                    <div class="panel-header">
                        <h5>
                            <i class="fa fa-shopping-cart me-2 text-primary"></i>
                            Customer Order History
                        </h5>

                        <span class="text-muted small">
                            {{ $orders->total() }} order(s)
                        </span>
                    </div>

                    @if($orders->count())

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Order Status</th>
                                        <th>Payment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $order->order_number }}</strong>
                                            </td>

                                            <td>
                                                {{ $order->created_at
                                                    ? \Carbon\Carbon::parse($order->created_at)->format('d M Y')
                                                    : '—' }}
                                            </td>

                                            <td>
                                                <strong>
                                                    R{{ number_format((float) $order->total, 2) }}
                                                </strong>
                                            </td>

                                            <td>
                                                <span class="status-order">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="status-order payment-{{ $order->payment_status }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </td>

                                            <td>
                                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                                   class="btn-order">
                                                    View Order
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-footer">
                            <small class="text-muted">
                                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }}
                                of {{ $orders->total() }} orders
                            </small>

                            {{ $orders->links() }}
                        </div>

                    @else

                        <div class="empty-state">
                            <i class="fa fa-shopping-bag fa-2x mb-3"></i>
                            <p>This customer has not placed any orders yet.</p>
                        </div>

                    @endif
                </div>

            </div>

            {{-- Account Management --}}
            <div class="col-lg-5">

                <div class="panel">
                    <div class="panel-header">
                        <h5>
                            <i class="fa fa-cog me-2 text-primary"></i>
                            Account Management
                        </h5>
                    </div>

                    <div class="panel-body">

                        <p class="text-muted small mb-3">
                            Update this customer's account access status.
                        </p>

                        <form method="POST"
                              action="{{ route('admin.customers.status.update', $customer->id) }}">

                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold small">
                                    Account Status
                                </label>

                                <select name="status" id="status" class="form-select" required>
                                    <option value="active"
                                        {{ $customer->status === 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="inactive"
                                        {{ $customer->status === 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>

                                    <option value="blocked"
                                        {{ $customer->status === 'blocked' ? 'selected' : '' }}>
                                        Blocked
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn-update w-100">
                                <i class="fa fa-save me-1"></i>
                                Update Customer Status
                            </button>

                        </form>

                        <hr class="my-4">

                        <div class="detail-block">
                            <div class="detail-label">Account Type</div>
                            <div class="detail-value">Customer</div>
                        </div>

                        <div class="detail-block">
                            <div class="detail-label">Customer ID</div>
                            <div class="detail-value">#{{ $customer->id }}</div>
                        </div>

                        <div class="detail-block">
                            <div class="detail-label">Current Status</div>
                            <div class="detail-value">
                                <span class="status-badge status-{{ $customer->status }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h5>
                            <i class="fa fa-info-circle me-2 text-primary"></i>
                            Account Information
                        </h5>
                    </div>

                    <div class="panel-body">
                        <p class="text-muted small mb-0">
                            Customer account status controls whether the customer can
                            access their account. Changing the status does not delete
                            their account, orders, payments or personal information.
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

@endsection

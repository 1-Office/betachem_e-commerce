@extends('layouts.admin')

@section('title', 'Customer Management | Betachem')

@section('content')

<style>
.admin-customers {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-customers .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-customers .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-customers .page-heading p {
    color: #77849a;
    margin: 0;
    font-size: 13px;
}

.admin-customers .summary-card,
.admin-customers .filter-card,
.admin-customers .customers-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
}

.admin-customers .summary-card {
    padding: 20px;
    height: 100%;
}

.admin-customers .summary-icon {
    width: 43px;
    height: 43px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 13px;
}

.admin-customers .summary-number {
    font-size: 27px;
    font-weight: 800;
    color: #263650;
    line-height: 1.2;
}

.admin-customers .summary-label {
    font-size: 13px;
    color: #7b8799;
    margin-top: 5px;
}

.admin-customers .filter-card {
    padding: 20px;
    margin-bottom: 22px;
}

.admin-customers .filter-card label {
    font-size: 12px;
    font-weight: 700;
    color: #64738b;
    margin-bottom: 7px;
}

.admin-customers .form-control,
.admin-customers .form-select {
    min-height: 43px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-customers .form-control:focus,
.admin-customers .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-customers .btn-search {
    min-height: 43px;
    background: #1745ca;
    border: 1px solid #1745ca;
    color: #fff;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    padding: 0 20px;
}

.admin-customers .btn-search:hover {
    background: #2d208a;
    color: #fff;
}

.admin-customers .customers-card {
    overflow: hidden;
}

.admin-customers .card-heading {
    padding: 20px 22px;
    border-bottom: 1px solid #edf0f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-customers .card-heading h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #263650;
}

.admin-customers .table {
    margin: 0;
    vertical-align: middle;
}

.admin-customers .table thead th {
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

.admin-customers .table tbody td {
    padding: 16px 17px;
    border-bottom: 1px solid #f0f2f6;
    font-size: 12px;
    color: #53627a;
}

.admin-customers .customer-name {
    font-weight: 800;
    color: #263650;
    font-size: 13px;
}

.admin-customers .customer-email {
    color: #7b8799;
    font-size: 11px;
    margin-top: 4px;
}

.admin-customers .customer-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #edf1ff;
    color: #1745ca;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    flex-shrink: 0;
}

.admin-customers .status-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 800;
    text-transform: capitalize;
    white-space: nowrap;
}

.admin-customers .status-active {
    background: #e6f7ed;
    color: #16834a;
}

.admin-customers .status-inactive {
    background: #edf0f5;
    color: #657187;
}

.admin-customers .status-blocked {
    background: #fdebec;
    color: #c03945;
}

.admin-customers .btn-view {
    background: #edf2ff;
    color: #1745ca;
    border: 1px solid #dce5ff;
    padding: 7px 12px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
}

.admin-customers .btn-view:hover {
    background: #1745ca;
    color: #fff;
}

.admin-customers .empty-state {
    padding: 55px 20px;
    text-align: center;
}

.admin-customers .empty-state i {
    font-size: 38px;
    color: #c4ccda;
    margin-bottom: 15px;
}

.admin-customers .empty-state h5 {
    color: #263650;
    font-weight: 800;
}

.admin-customers .empty-state p {
    color: #8994a6;
    font-size: 13px;
}

.admin-customers .pagination {
    margin: 0;
}

.admin-customers .pagination .page-link {
    color: #1745ca;
    font-size: 12px;
}

.admin-customers .pagination .active .page-link {
    background: #1745ca;
    border-color: #1745ca;
    color: #fff;
}

.admin-customers .table-footer {
    padding: 17px 22px;
    border-top: 1px solid #edf0f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

@media (max-width: 767px) {
    .admin-customers {
        padding: 20px 0 40px;
    }

    .admin-customers .page-heading h2 {
        font-size: 23px;
    }

    .admin-customers .table thead th,
    .admin-customers .table tbody td {
        padding: 13px 12px;
    }
}
</style>

<div class="admin-customers">
    <div class="container-fluid px-3 px-lg-4">

        {{-- Page Heading --}}
        <div class="page-heading">
            <div>
                <h2>Customer Management</h2>
                <p>Manage customer accounts and view their shopping activity.</p>
            </div>

            <div>
                <span class="badge bg-light text-primary border px-3 py-2">
                    <i class="fa fa-users me-1"></i>
                    Customer Accounts
                </span>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#edf1ff;color:#1745ca;">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($summary['total']) }}
                    </div>
                    <div class="summary-label">Total Customers</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#e6f7ed;color:#16834a;">
                        <i class="fa fa-user-check"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($summary['active']) }}
                    </div>
                    <div class="summary-label">Active Customers</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#edf0f5;color:#657187;">
                        <i class="fa fa-user-times"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($summary['inactive']) }}
                    </div>
                    <div class="summary-label">Inactive Customers</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon" style="background:#fdebec;color:#c03945;">
                        <i class="fa fa-ban"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($summary['blocked']) }}
                    </div>
                    <div class="summary-label">Blocked Customers</div>
                </div>
            </div>

        </div>

        {{-- Search and Filters --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.customers.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-lg-6">
                        <label for="search">Search Customers</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Name, email address or phone number..."
                            value="{{ $search }}"
                        >
                    </div>

                    <div class="col-lg-3">
                        <label for="status">Account Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                            <option value="blocked" {{ $status === 'blocked' ? 'selected' : '' }}>
                                Blocked
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn btn-search flex-grow-1">
                            <i class="fa fa-search me-1"></i> Search
                        </button>

                        <a href="{{ route('admin.customers.index') }}"
                           class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                           style="min-height:43px;border-radius:8px;">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- Customer Table --}}
        <div class="customers-card">

            <div class="card-heading">
                <h5>
                    <i class="fa fa-address-book-o me-2 text-primary"></i>
                    Registered Customers
                </h5>

                <span class="text-muted small">
                    {{ $customers->total() }} record(s) found
                </span>
            </div>

            @if($customers->count())

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Orders</th>
                                <th>Order Value</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($customers as $customer)
                                @php
                                    $initials = strtoupper(
                                        substr($customer->first_name ?? '', 0, 1) .
                                        substr($customer->last_name ?? '', 0, 1)
                                    );

                                    $customerStatus = $customer->status ?? 'inactive';
                                @endphp

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="customer-avatar">
                                                {{ $initials ?: 'C' }}
                                            </div>

                                            <div>
                                                <div class="customer-name">
                                                    {{ $customer->first_name }}
                                                    {{ $customer->last_name }}
                                                </div>

                                                <div class="customer-email">
                                                    {{ $customer->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        {{ $customer->phone ?: 'Not provided' }}
                                    </td>

                                    <td>
                                        {{ $customer->city ?: '—' }}
                                        @if($customer->province)
                                            <div class="text-muted small">
                                                {{ $customer->province }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{ number_format($customer->orders_count) }}</strong>
                                    </td>

                                    <td>
                                        <strong>
                                            R{{ number_format((float) $customer->orders_total, 2) }}
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="status-badge status-{{ $customerStatus }}">
                                            {{ ucfirst($customerStatus) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $customer->created_at
                                            ? \Carbon\Carbon::parse($customer->created_at)->format('d M Y')
                                            : '—' }}
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.customers.show', $customer->id) }}"
                                           class="btn-view">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <small class="text-muted">
                        Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }}
                        of {{ $customers->total() }} customers
                    </small>

                    {{ $customers->links() }}
                </div>

            @else

                <div class="empty-state">
                    <i class="fa fa-users"></i>
                    <h5>No Customers Found</h5>
                    <p>
                        No customer accounts match your search or selected filters.
                    </p>

                    <a href="{{ route('admin.customers.index') }}"
                       class="btn btn-outline-primary btn-sm">
                        Clear Filters
                    </a>
                </div>

            @endif

        </div>

    </div>
</div>

@endsection

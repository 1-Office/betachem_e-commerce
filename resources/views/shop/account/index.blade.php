@extends('layouts.shop')

@section('title', 'My Account | Betachem')

@section('content')

<div class="container py-5">

    {{-- Welcome --}}
    <div class="mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>
                <span class="text-primary fw-semibold">MY ACCOUNT</span>

                <h1 class="fw-bold mb-1">
                    Welcome, {{ $user->first_name }}!
                </h1>

                <p class="text-muted mb-0">
                    Manage your account, view your orders and update your details.
                </p>
            </div>

            <div>
                <a href="{{ route('shop.home') }}" class="btn btn-primary">
                    <i class="bi bi-cart3 me-2"></i>
                    Continue Shopping
                </a>
            </div>

        </div>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Statistics --}}
    <div class="row g-4 mb-5">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-bag fs-3"></i>
                        </div>

                        <div>
                            <div class="text-muted small">Total Orders</div>
                            <div class="fs-3 fw-bold">{{ $orderCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>

                        <div>
                            <div class="text-muted small">Active Orders</div>
                            <div class="fs-3 fw-bold">{{ $pendingOrders }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>

                        <div>
                            <div class="text-muted small">Completed Orders</div>
                            <div class="fs-3 fw-bold">{{ $completedOrders }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        {{-- Personal Information --}}
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h4 class="fw-bold mb-1">Personal Information</h4>

                    <p class="text-muted small mb-0">
                        Your account and contact details.
                    </p>
                </div>

                <div class="card-body p-4">

                    <div class="row g-4">

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Full Name</div>

                            <div class="fw-semibold">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Email Address</div>

                            <div class="fw-semibold">
                                {{ $user->email }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Phone Number</div>

                            <div class="fw-semibold">
                                {{ $user->phone ?: 'Not provided' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Account Status</div>

                            @if($user->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($user->status === 'blocked')
                                <span class="badge bg-danger">Blocked</span>
                            @else
                                <span class="badge bg-secondary">
                                    {{ ucfirst($user->status) }}
                                </span>
                            @endif
                        </div>

                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold mb-3">Delivery Address</h5>

                    <div class="bg-light rounded-3 p-3">

                        <div>
                            {{ $user->address ?: 'No delivery address saved.' }}
                        </div>

                        @if($user->city || $user->province || $user->postal_code)
                            <div class="mt-1">
                                {{ $user->city }}

                                @if($user->city && $user->province)
                                    ,
                                @endif

                                {{ $user->province }}

                                {{ $user->postal_code }}
                            </div>
                        @endif

                    </div>

                    <div class="mt-4">
                        <a href="{{ route('shop.account.edit') }}"
                           class="btn btn-outline-primary">
                            <i class="bi bi-pencil-square me-2"></i>
                            Edit Personal Information
                        </a>
                    </div>

                </div>
            </div>

        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-5">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">Quick Actions</h4>

                    <div class="d-grid gap-2">

                        <a href="{{ route('shop.cart.index') }}"
                           class="btn btn-outline-primary text-start">
                            <i class="bi bi-cart3 me-2"></i>
                            View Shopping Cart
                        </a>

                        <a href="{{ route('shop.home') }}"
                           class="btn btn-outline-primary text-start">
                            <i class="bi bi-shop me-2"></i>
                            Browse Products
                        </a>

                        <a href="{{ route('shop.account.orders') }}"
   class="btn btn-outline-primary text-start">
    <i class="bi bi-box-seam me-2"></i>
    My Orders
</a>

                        <a href="{{ route('shop.account.edit') }}"
                           class="btn btn-outline-primary text-start">
                            <i class="bi bi-person-gear me-2"></i>
                            Edit Account
                        </a>

                    </div>

                </div>
            </div>

            {{-- Help --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <h5 class="fw-bold">Need Help?</h5>

                    <p class="text-muted small">
                        If you have questions about your order, products or delivery,
                        please contact Betachem.
                    </p>

                    <a href="mailto:info@betachem.co.za"
                       class="btn btn-primary w-100">
                        <i class="bi bi-envelope me-2"></i>
                        Contact Betachem
                    </a>

                </div>
            </div>

        </div>

    </div>

    {{-- Recent Orders --}}
    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white border-0 p-4">
            <h4 class="fw-bold mb-1">Recent Orders</h4>

            <p class="text-muted small mb-0">
                Your latest orders with Betachem.
            </p>
        </div>

        <div class="card-body p-0">

            @if($recentOrders->isNotEmpty())

                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Order</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-end px-4">Total</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($recentOrders as $order)

                                @php
                                    $statusClass = match($order->status) {
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'shipped' => 'info',
                                        'ready' => 'primary',
                                        'processing' => 'warning',
                                        default => 'secondary',
                                    };

                                    $paymentClass = match($order->payment_status) {
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'warning',
                                        'partial' => 'info',
                                        default => 'secondary',
                                    };
                                @endphp

                                <tr>

                                    <td class="px-4">
                                        <strong>{{ $order->order_number }}</strong>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $paymentClass }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>

                                    <td class="text-end px-4 fw-semibold">
                                        R {{ number_format((float) $order->total, 2) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5 px-4">

                    <i class="bi bi-bag-x fs-1 text-muted"></i>

                    <h5 class="fw-bold mt-3">No Orders Yet</h5>

                    <p class="text-muted">
                        You haven't placed an order yet.
                    </p>

                    <a href="{{ route('shop.home') }}" class="btn btn-primary">
                        Start Shopping
                    </a>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection

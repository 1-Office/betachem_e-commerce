@extends('layouts.shop')

@section('title', 'My Orders | Betachem')

@section('content')

<div class="container py-5">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <span class="text-primary fw-semibold">MY ACCOUNT</span>

            <h1 class="fw-bold mb-1">My Orders</h1>

            <p class="text-muted mb-0">
                View and track your Betachem purchases.
            </p>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('shop.account') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                My Account
            </a>

            <a href="{{ route('shop.home') }}"
               class="btn btn-primary">
                <i class="bi bi-shop me-2"></i>
                Shop Now
            </a>

        </div>

    </div>

    {{-- Order Count --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center">

                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                    <i class="bi bi-bag-check fs-3"></i>
                </div>

                <div>
                    <div class="text-muted small">Total Orders</div>

                    <div class="fs-3 fw-bold">
                        {{ $orders->total() }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Orders --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 p-4">

            <h4 class="fw-bold mb-1">Order History</h4>

            <p class="text-muted small mb-0">
                Your previous and current orders.
            </p>

        </div>

        <div class="card-body p-0">

            @if($orders->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Order Number</th>
                                <th>Date</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                                <th>Total</th>
                                <th class="text-end px-4">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($orders as $order)

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
                                        <strong>
                                            {{ $order->order_number }}
                                        </strong>
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

                                    <td class="fw-semibold">
                                        R {{ number_format((float) $order->total, 2) }}
                                    </td>

                                    <td class="text-end px-4">
    <a href="{{ route('shop.account.orders.show', $order->id) }}"
       class="btn btn-sm btn-outline-primary">
        <i class="bi bi-eye me-1"></i>
        View Details
    </a>
</td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                @if($orders->hasPages())
                    <div class="p-4">
                        {{ $orders->links() }}
                    </div>
                @endif

            @else

                <div class="text-center py-5 px-4">

                    <i class="bi bi-bag-x fs-1 text-muted"></i>

                    <h5 class="fw-bold mt-3">No Orders Yet</h5>

                    <p class="text-muted">
                        You haven't placed an order with Betachem yet.
                    </p>

                    <a href="{{ route('shop.home') }}"
                       class="btn btn-primary">
                        <i class="bi bi-shop me-2"></i>
                        Start Shopping
                    </a>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection

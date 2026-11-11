@extends('layouts.shop')

@section('title', 'Order Confirmation | Betachem')

@section('content')

<div class="container py-5">

    {{-- Order Success Header --}}
    <div class="text-center mb-5">
        <div class="mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 75px;"></i>
        </div>

        <h2 class="fw-bold">Thank You for Your Order!</h2>

        <p class="text-muted mb-2">
            Your order has been successfully received.
        </p>

        <p class="text-muted">
            Please complete your EFT payment and upload your proof of payment.
        </p>

        <div class="mt-3">
            <span class="badge bg-light text-dark border p-2">
                Order Number:
                <strong>{{ $order->order_number }}</strong>
            </span>
        </div>
    </div>

    {{-- Order Status --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4 text-center">

                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">Order Status</small>
                    <span class="badge bg-primary px-3 py-2">
                        {{ ucfirst($orderStatus) }}
                    </span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">Payment Status</small>
                    <span class="badge bg-primary px-3 py-2">
                        {{ ucfirst($paymentStatus) }}
                    </span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">Order Date</small>
                    <strong>
                        {{ $order->created_at
                            ? \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i')
                            : 'N/A' }}
                    </strong>
                </div>

            </div>
        </div>
    </div>

    {{-- EFT Payment Instructions --}}
    @if(strtolower((string) ($order->payment_method ?? '')) === 'eft'
        && $paymentStatus === 'pending')

        <div class="card border-primary shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bank me-2"></i>
                    EFT Banking Details
                </h5>
            </div>

            <div class="card-body p-4">

                <div class="alert alert-info">
                    <strong>Important:</strong>
                    Please confirm the banking details with Betachem before
                    making any payment. These are temporary placeholder details.
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <small class="text-muted d-block">Bank Name</small>
                        <strong>To be confirmed</strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Account Holder</small>
                        <strong>Betachem</strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Account Number</small>
                        <strong>0000000000</strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Account Type</small>
                        <strong>Cheque</strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Branch Code</small>
                        <strong>000000</strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">Payment Reference</small>
                        <strong class="text-primary">{{ $order->order_number }}</strong>
                    </div>

                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold">
                        <i class="bi bi-cloud-upload me-2"></i>
                        Upload Your Proof of Payment
                    </h6>

                    <p class="mb-2">
                        After making your EFT payment:
                    </p>

                    <ol class="mb-0">
                        <li>Log in to your Betachem customer account.</li>
                        <li>Go to <strong>My Account → My Orders</strong>.</li>
                        <li>Open this order using your order number.</li>
                        <li>Upload your proof of payment as a PDF document.</li>
                    </ol>
                </div>

                <div class="alert alert-primary mb-0 mt-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Your payment will remain pending until Betachem verifies
                    that the funds have reflected in its bank account.
                </div>

            </div>
        </div>

    @elseif($paymentStatus === 'paid')

        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Payment Received:</strong>
            Your payment has been confirmed. Thank you!
        </div>

    @endif

    <div class="row g-4">

        {{-- Order Items --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-bag-check me-2"></i>
                        Order Items
                    </h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th>SKU</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($orderItems as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <strong>{{ $item->product_name }}</strong>
                                        </td>

                                        <td>
                                            <small class="text-muted">
                                                {{ $item->sku ?? 'N/A' }}
                                            </small>
                                        </td>

                                        <td class="text-center">
                                            {{ $item->quantity }}
                                        </td>

                                        <td class="text-end pe-3">
                                            R{{ number_format((float) $item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No order items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            {{-- Delivery Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-truck me-2"></i>
                        Delivery Information
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <small class="text-muted d-block">Customer Name</small>
                            <strong>
                                {{ trim(
                                    ($order->shipping_first_name ?? $order->first_name ?? '') . ' ' .
                                    ($order->shipping_last_name ?? $order->last_name ?? '')
                                ) ?: 'N/A' }}
                            </strong>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">Email Address</small>
                            <strong>
                                {{ $order->shipping_email ?? $order->email ?? 'N/A' }}
                            </strong>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">Phone Number</small>
                            <strong>
                                {{ $order->shipping_phone ?? $order->phone ?? 'N/A' }}
                            </strong>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">Province</small>
                            <strong>
                                {{ $order->shipping_province ?? $order->province ?? 'N/A' }}
                            </strong>
                        </div>

                        <div class="col-12">
                            <small class="text-muted d-block">Delivery Address</small>
                            <strong>
                                {{ $order->shipping_address ?? $order->address ?? 'N/A' }}
                            </strong>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">City</small>
                            <strong>
                                {{ $order->shipping_city ?? $order->city ?? 'N/A' }}
                            </strong>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">Postal Code</small>
                            <strong>
                                {{ $order->shipping_postal_code ?? $order->postal_code ?? 'N/A' }}
                            </strong>
                        </div>

                    </div>

                    @if(!empty($order->notes))
                        <hr>
                        <small class="text-muted d-block mb-1">Order Notes</small>
                        <p class="mb-0">{{ $order->notes }}</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- Order Summary --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        Order Summary
                    </h5>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal</span>
                        <strong>
                            R{{ number_format((float) ($order->subtotal ?? 0), 2) }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Delivery Fee</span>
                        <strong>
                            R{{ number_format((float) ($order->delivery_fee ?? 0), 2) }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Discount</span>
                        <strong class="text-success">
                            - R{{ number_format((float) ($order->discount ?? 0), 2) }}
                        </strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <strong class="fs-5">Total</strong>
                        <strong class="fs-5 text-success">
                            R{{ number_format((float) ($order->total ?? 0), 2) }}
                        </strong>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <small class="text-muted d-block">Payment Method</small>
                        <strong>{{ $paymentMethodLabel }}</strong>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            Keep your order number for future reference.
                        </small>
                    </div>

                </div>
            </div>

            {{-- Navigation --}}
            <div class="d-grid gap-2 mt-4">

                <a href="{{ route('shop.account.orders.show', $order->id) }}"
                   class="btn btn-primary py-2">
                    <i class="bi bi-cloud-upload me-2"></i>
                    Go to Order & Upload POP
                </a>

                <a href="{{ route('shop.account') }}"
                   class="btn btn-outline-dark py-2">
                    <i class="bi bi-person me-2"></i>
                    My Account
                </a>

                <a href="{{ route('shop.index') }}"
                   class="btn btn-outline-secondary py-2">
                    <i class="bi bi-shop me-2"></i>
                    Continue Shopping
                </a>

            </div>

        </div>

    </div>

</div>

@endsection
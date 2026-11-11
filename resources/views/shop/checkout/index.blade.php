@extends('layouts.shop')

@section('title', 'Checkout | Betachem')

@section('content')

<div class="container py-5">

    <div class="mb-4">
        <h1 class="fw-bold">Complete Your Order</h1>
        <p class="text-muted">
            Please confirm your delivery details and payment method.
        </p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('shop.checkout.store') }}" method="POST">
        @csrf

        <div class="row g-4">

            {{-- DELIVERY INFORMATION --}}
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-geo-alt me-2"></i>
                            Delivery Information
                        </h4>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name', $user->first_name) }}"
                                    required
                                >
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name', $user->last_name) }}"
                                    required
                                >
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input
                                    type="tel"
                                    name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $user->phone) }}"
                                    required
                                >
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Street Address *</label>
                                <input
                                    type="text"
                                    name="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    value="{{ old('address', $user->address) }}"
                                    placeholder="House number and street name"
                                    required
                                >
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">City / Town *</label>
                                <input
                                    type="text"
                                    name="city"
                                    class="form-control @error('city') is-invalid @enderror"
                                    value="{{ old('city', $user->city) }}"
                                    required
                                >
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Province *</label>
                                <select
                                    name="province"
                                    class="form-select @error('province') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Select Province</option>

                                    @foreach($provinces as $province)
                                        <option
                                            value="{{ $province }}"
                                            @selected(old('province', $user->province) === $province)
                                        >
                                            {{ $province }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Postal Code *</label>
                                <input
                                    type="text"
                                    name="postal_code"
                                    class="form-control @error('postal_code') is-invalid @enderror"
                                    value="{{ old('postal_code', $user->postal_code) }}"
                                    required
                                >
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Order Notes (Optional)</label>
                                <textarea
                                    name="notes"
                                    rows="3"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Any special delivery instructions?"
                                >{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- PAYMENT METHOD --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-credit-card me-2"></i>
                            Payment Method
                        </h4>

                        <label class="payment-option selected w-100 mb-3">

                            <div class="d-flex align-items-start gap-3">

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="eft"
                                    class="form-check-input mt-1"
                                    @checked(old('payment_method', 'eft') === 'eft')
                                    required
                                >

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <strong>EFT / Bank Transfer</strong>
                                        <i class="bi bi-bank fs-4 text-primary"></i>
                                    </div>

                                    <p class="text-muted small mb-0 mt-2">
                                        Pay by electronic funds transfer.
                                        Banking details and payment instructions
                                        will be provided after your order has
                                        been reviewed.
                                    </p>

                                </div>
                            </div>

                        </label>

                        <div class="payment-option disabled-option w-100">

                            <div class="d-flex align-items-start gap-3">

                                <input
                                    type="radio"
                                    class="form-check-input mt-1"
                                    disabled
                                >

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <strong>Card Payment</strong>
                                        <span class="badge bg-secondary">
                                            Coming Soon
                                        </span>
                                    </div>

                                    <p class="text-muted small mb-0 mt-2">
                                        Card payments will be available
                                        once our secure payment gateway
                                        has been integrated.
                                    </p>

                                </div>
                            </div>

                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Your order will be created with payment
                            pending. Payment instructions will follow
                            after your order has been reviewed.
                        </div>

                    </div>
                </div>

            </div>

            {{-- ORDER SUMMARY --}}
            <div class="col-lg-4">

                <div class="card border-0 shadow-sm checkout-summary">

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">Your Order</h4>

                        <div class="checkout-products mb-4">

                            @foreach($cartItems as $item)

                                @if($item->product && $item->product->is_active)

                                    @php
                                        $product = $item->product;

                                        $storedImage = trim(
                                            (string) $product->main_image
                                        );

                                        $storedImage = ltrim($storedImage, '/');

                                        /*
                                         * Support both:
                                         * uploads/products/example.jpg
                                         * example.jpg
                                         *
                                         * Also preserve other stored relative
                                         * paths instead of forcing a directory.
                                         */

                                        if ($storedImage === '') {
                                            $image = asset('assets/images/product-placeholder.png');
                                        } elseif (
                                            str_starts_with($storedImage, 'http://') ||
                                            str_starts_with($storedImage, 'https://')
                                        ) {
                                            $image = $storedImage;
                                        } elseif (
                                            str_contains($storedImage, '/')
                                        ) {
                                            $image = asset($storedImage);
                                        } else {
                                            $image = asset('uploads/products/' . $storedImage);
                                        }

                                        $itemTotal = (float) $product->price
                                            * (int) $item->quantity;
                                    @endphp

                                    <div class="d-flex align-items-center gap-3 mb-3">

                                        <img
                                            src="{{ $image }}"
                                            alt="{{ $product->name }}"
                                            class="checkout-product-image"
                                            onerror="this.onerror=null;this.src='{{ asset('assets/images/product-placeholder.png') }}';"
                                        >

                                        <div class="flex-grow-1 min-width-0">

                                            <div class="fw-semibold">
                                                {{ $product->name }}
                                            </div>

                                            <div class="small text-muted">
                                                Qty: {{ $item->quantity }}
                                            </div>

                                        </div>

                                        <div class="fw-semibold text-nowrap">
                                            R {{ number_format($itemTotal, 2) }}
                                        </div>

                                    </div>

                                @endif

                            @endforeach

                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span>R {{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Delivery</span>
                            <span>
                                @if($deliveryFee > 0)
                                    R {{ number_format($deliveryFee, 2) }}
                                @else
                                    Free
                                @endif
                            </span>
                        </div>

                        @if($discount > 0)
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Discount</span>
                                <span class="text-success">
                                    - R {{ number_format($discount, 2) }}
                                </span>
                            </div>
                        @endif

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <strong>Total</strong>

                            <strong class="checkout-total">
                                R {{ number_format($total, 2) }}
                            </strong>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100"
                        >
                            <i class="bi bi-lock me-2"></i>
                            Place Order
                        </button>

                        <a
                            href="{{ route('shop.cart.index') }}"
                            class="btn btn-outline-secondary w-100 mt-2"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Back to Cart
                        </a>

                        <div class="text-center small text-muted mt-3">
                            <i class="bi bi-shield-check me-1"></i>
                            Your order details are securely submitted.
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </form>

</div>

<style>
    .checkout-summary {
        position: sticky;
        top: 90px;
    }

    .checkout-product-image {
        width: 65px;
        height: 65px;
        object-fit: contain;
        border-radius: 8px;
        background: #f8f9fa;
        padding: 5px;
        flex-shrink: 0;
    }

    .min-width-0 {
        min-width: 0;
    }

    .checkout-total {
        color: #1D88E9;
        font-size: 1.5rem;
    }

    .payment-option {
        display: block;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 18px;
        cursor: pointer;
        background: #fff;
    }

    .payment-option.selected {
        border-color: #1D88E9;
        background: #f8faff;
    }

    .disabled-option {
        background: #f8f9fa;
        cursor: not-allowed;
        opacity: 0.7;
    }

    @media (max-width: 991px) {
        .checkout-summary {
            position: static;
        }
    }
</style>

@endsection
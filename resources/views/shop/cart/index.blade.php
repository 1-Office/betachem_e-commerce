@extends('layouts.shop')

@section('title', 'Shopping Cart | Betachem')

@push('styles')
<style>
    .cart-heading {
        font-weight: 800;
        color: #17365d;
    }

    .cart-card {
        border: 1px solid #e8edf2;
        border-radius: 14px;
        background: #fff;
        padding: 22px;
        margin-bottom: 16px;
    }

    .cart-product-image {
        width: 100px;
        height: 100px;
        object-fit: contain;
        border-radius: 10px;
        background: #f7f9fb;
        padding: 8px;
    }

    .cart-product-name {
        font-weight: 700;
        color: #17365d;
    }

    .cart-summary {
        background: #f8fafc;
        border: 1px solid #e8edf2;
        border-radius: 14px;
        padding: 24px;
    }

    .quantity-input {
        width: 85px;
        text-align: center;
    }

    .cart-total {
        color: #17365d;
        font-size: 1.6rem;
        font-weight: 800;
    }

    .empty-cart {
        text-align: center;
        padding: 65px 20px;
        background: #f8fafc;
        border-radius: 16px;
    }

    .checkout-button {
        font-weight: 700;
        padding: 13px 20px;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-0">

    {{-- Page Heading --}}
    <div class="mb-4">
        <h1 class="cart-heading">Shopping Cart</h1>

        <p class="text-muted mb-0">
            Review your selected Betachem products before checkout.
        </p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Errors --}}
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

    {{-- Empty Cart --}}
    @if($cart->items->isEmpty())

        <div class="empty-cart">

            <i class="fa fa-shopping-cart fa-4x text-secondary mb-3"></i>

            <h3>Your cart is empty</h3>

            <p class="text-muted">
                You haven't added any products to your cart yet.
            </p>

            <a href="{{ route('shop.index') }}"
               class="btn btn-primary px-4">

                Continue Shopping

            </a>

        </div>

    @else

        <div class="row g-4">

            {{-- Cart Products --}}
            <div class="col-lg-8">

                @foreach($cart->items as $item)

                    <div class="cart-card">

                        <div class="row align-items-center g-3">

                            {{-- Product Image --}}
                            <div class="col-4 col-md-2">

                                @if($item->product && $item->product->main_image)

                                    <img
                                        src="{{ asset('uploads/products/' . ltrim($item->product->main_image, '/')) }}"
                                        alt="{{ $item->product->name }}"
                                        class="cart-product-image">

                                @else

                                    <div class="cart-product-image d-flex align-items-center justify-content-center">

                                        <i class="fa fa-flask fa-2x text-secondary"></i>

                                    </div>

                                @endif

                            </div>

                            {{-- Product Information --}}
                            <div class="col-8 col-md-4">

                                <h6 class="cart-product-name mb-1">

                                    {{ $item->product->name ?? 'Product unavailable' }}

                                </h6>

                                <small class="text-muted">

                                    SKU: {{ $item->product->sku ?? 'N/A' }}

                                </small>

                                <div class="mt-2">

                                    <strong>
                                        R{{ number_format((float)$item->price, 2) }}
                                    </strong>

                                    <small class="text-muted">
                                        each
                                    </small>

                                </div>

                            </div>

                            {{-- Quantity Update --}}
                            <div class="col-7 col-md-3">

                                <form
                                    method="POST"
                                    action="{{ route('shop.cart.update', $item->id) }}">

                                    @csrf
                                    @method('PATCH')

                                    <label class="form-label small text-muted">
                                        Quantity
                                    </label>

                                    <div class="d-flex gap-2">

                                        <input
                                            type="number"
                                            name="quantity"
                                            class="form-control quantity-input"
                                            value="{{ $item->quantity }}"
                                            min="1"
                                            max="{{ $item->product->stock_quantity ?? 1 }}"
                                            required>

                                        <button
                                            class="btn btn-outline-primary"
                                            type="submit"
                                            title="Update quantity">

                                            <i class="fa fa-refresh"></i>

                                        </button>

                                    </div>

                                </form>

                                <small class="text-muted">

                                    {{ $item->product->stock_quantity ?? 0 }} available

                                </small>

                            </div>

                            {{-- Item Total and Remove --}}
                            <div class="col-5 col-md-3 text-end">

                                <div class="fw-bold mb-3">

                                    R{{ number_format((float)$item->price * (int)$item->quantity, 2) }}

                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('shop.cart.remove', $item->id) }}"
                                    onsubmit="return confirm('Remove this product from your cart?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger">

                                        <i class="fa fa-trash"></i>
                                        Remove

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                @endforeach

                {{-- Continue Shopping --}}
                <a href="{{ route('shop.index') }}"
                   class="btn btn-outline-secondary">

                    <i class="fa fa-arrow-left"></i>
                    Continue Shopping

                </a>

            </div>

            {{-- Order Summary --}}
            <div class="col-lg-4">

                <div class="cart-summary">

                    <h4 class="fw-bold mb-4">
                        Order Summary
                    </h4>

                    {{-- Number of Items --}}
                    <div class="d-flex justify-content-between mb-3">

                        <span>Items</span>

                        <strong>
                            {{ $cart->items->sum('quantity') }}
                        </strong>

                    </div>

                    {{-- Subtotal --}}
                    <div class="d-flex justify-content-between mb-3">

                        <span>Subtotal</span>

                        <strong>
                            R{{ number_format($subtotal, 2) }}
                        </strong>

                    </div>

                    {{-- Delivery --}}
                    <div class="d-flex justify-content-between mb-3">

                        <span>Delivery</span>

                        <span class="text-muted">
                            Calculated at checkout
                        </span>

                    </div>

                    <hr>

                    {{-- Total --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <span class="fw-bold">
                            Total
                        </span>

                        <span class="cart-total">

                            R{{ number_format($subtotal, 2) }}

                        </span>

                    </div>

                    {{-- Proceed to Checkout --}}
                    <a
                        href="{{ route('shop.checkout.index') }}"
                        class="btn btn-primary btn-lg w-100 checkout-button">

                        <i class="fa fa-lock me-2"></i>
                        Proceed to Checkout

                    </a>

                    <p class="small text-muted text-center mt-3 mb-0">

                        Your order will be securely processed at checkout.

                    </p>

                </div>

            </div>

        </div>

    @endif

</div>

@endsection
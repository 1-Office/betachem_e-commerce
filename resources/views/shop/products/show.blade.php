@extends('layouts.shop')

@section('title', $product->name . ' | Betachem')

@section('content')

<div class="container py-5">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('shop.index') }}">Shop</a>
            </li>
            <li class="breadcrumb-item">
                {{ $product->category?->name ?? 'Chemicals' }}
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $product->name }}
            </li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa fa-check-circle me-2"></i>
            {{ session('success') }}
            <a href="{{ route('shop.cart.index') }}" class="alert-link ms-2">
                View Cart
            </a>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-5">

        {{-- PRODUCT IMAGE --}}
        <div class="col-lg-6">
            <div class="bg-white border rounded-4 p-4 text-center"
                 style="min-height:420px; display:flex; align-items:center; justify-content:center;">

                @if($product->main_image)
                    <img
                        src="{{ asset('uploads/products/' . $product->main_image) }}"
                        alt="{{ $product->name }}"
                        class="img-fluid"
                        style="max-height:380px; object-fit:contain;">
                @else
                    <i class="fa fa-flask text-secondary" style="font-size:100px;"></i>
                @endif

            </div>
        </div>

        {{-- PRODUCT DETAILS --}}
        <div class="col-lg-6">

            <span class="badge bg-primary mb-3">
                {{ $product->category?->name ?? 'Chemicals' }}
            </span>

            <h1 class="fw-bold mb-3">
                {{ $product->name }}
            </h1>

            @if($product->sku)
                <p class="text-muted">
                    Product SKU: {{ $product->sku }}
                </p>
            @endif

            <h2 class="text-primary fw-bold mb-4">
                R{{ number_format((float)$product->price, 2) }}
            </h2>

            {{-- STOCK STATUS --}}
            <div class="mb-4">
                @if($product->stock_quantity > 0)
                    <span class="text-success fw-bold">
                        <i class="fa fa-check-circle"></i>
                        In Stock
                    </span>

                    <small class="text-muted ms-2">
                        {{ $product->stock_quantity }} available
                    </small>
                @else
                    <span class="text-danger fw-bold">
                        <i class="fa fa-times-circle"></i>
                        Currently Out of Stock
                    </span>
                @endif
            </div>

            {{-- ADD TO CART --}}
            @if($product->stock_quantity > 0)

                <form method="POST"
                      action="{{ route('shop.cart.add', $product->id) }}"
                      class="border rounded-4 p-3 mb-4">

                    @csrf

                    <label for="quantity" class="form-label fw-bold">
                        Select Quantity
                    </label>

                    <div class="d-flex flex-wrap gap-3 align-items-center">

                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            class="form-control"
                            style="width:110px;"
                            value="1"
                            min="1"
                            max="{{ $product->stock_quantity }}"
                            required>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-shopping-cart me-2"></i>
                            Add to Cart
                        </button>

                    </div>

                    <small class="text-muted d-block mt-2">
                        Maximum available quantity: {{ $product->stock_quantity }}
                    </small>

                </form>

            @endif

            @if($product->description)
                <h5 class="fw-bold">Product Description</h5>
                <p class="text-muted" style="line-height:1.8;">
                    {{ $product->description }}
                </p>
            @endif

            @if($product->specifications)
                <h5 class="fw-bold mt-4">Specifications</h5>
                <div class="text-muted" style="line-height:1.8;">
                    {!! nl2br(e($product->specifications)) !!}
                </div>
            @endif

            <hr class="my-4">

            <p class="text-muted">
                Need more information or a quotation for this product?
                Contact our team for assistance.
            </p>

            <div class="d-flex flex-wrap gap-2">
    <a href="mailto:contact@bchem.co.za?subject={{ urlencode('Betachem Shop - Request for Assistance') }}"
       class="btn btn-outline-primary btn-lg">
        <i class="fa fa-envelope me-2"></i>
        Request Assistance
    </a>
</div>

                <a href="{{ route('shop.index') }}"
                   class="btn btn-outline-secondary btn-lg">
                    Back to Shop
                </a>
            </div>

        </div>
    </div>

    {{-- RELATED PRODUCTS --}}
    @if($relatedProducts->isNotEmpty())

        <section class="mt-5 pt-4">

            <h3 class="fw-bold mb-4">Related Products</h3>

            <div class="row g-4">

                @foreach($relatedProducts as $related)

                    <div class="col-6 col-md-4 col-lg-3">

                        <div class="card h-100 border-0 shadow-sm">

                            <div class="p-3 text-center"
                                 style="height:190px; display:flex; align-items:center; justify-content:center;">

                                @if($related->main_image)
                                    <img
                                        src="{{ asset('uploads/products/' . $related->main_image) }}"
                                        alt="{{ $related->name }}"
                                        class="img-fluid"
                                        style="max-height:160px; object-fit:contain;">
                                @else
                                    <i class="fa fa-flask fa-3x text-secondary"></i>
                                @endif

                            </div>

                            <div class="card-body d-flex flex-column">

                                <h6 class="fw-bold">
                                    {{ $related->name }}
                                </h6>

                                <p class="text-primary fw-bold mt-auto">
                                    R{{ number_format((float)$related->price, 2) }}
                                </p>

                                <a href="{{ route('shop.products.show', $related->slug) }}"
                                   class="btn btn-outline-primary w-100">
                                    View Product
                                </a>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>

    @endif

</div>

@endsection

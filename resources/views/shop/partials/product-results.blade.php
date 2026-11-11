{{-- PRODUCT RESULTS PARTIAL --}}

<div class="shop-results-toolbar">
    <p>
        Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
        of {{ $products->total() }} products
    </p>
</div>

<div class="row g-3">

    @forelse($products as $product)

        <div class="col-6 col-md-4 col-xl-3">

            <article class="shop-product-card">

                {{-- PRODUCT IMAGE --}}
                <div class="shop-product-image">

                    @if($product->main_image)
                        <img
                            src="{{ asset('uploads/products/' . $product->main_image) }}"
                            alt="{{ $product->name }}"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                        >

                        <i class="fa fa-flask shop-product-placeholder"
                           style="display:none;"></i>
                    @else
                        <i class="fa fa-flask shop-product-placeholder"></i>
                    @endif

                    @if($product->is_featured)
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3">
                            Featured
                        </span>
                    @endif

                </div>

                {{-- PRODUCT INFORMATION --}}
                <div class="shop-product-info">

                    <span class="shop-product-category">
                        {{ $product->category?->name ?? 'Product' }}
                    </span>

                    <h3 class="shop-product-name">
                        {{ $product->name }}
                    </h3>

                    <div class="shop-product-price">
                        R{{ number_format((float) $product->price, 2) }}
                    </div>

                    {{-- STOCK STATUS --}}
                    <span class="shop-stock {{ $product->stock_quantity > 0 ? 'available' : 'unavailable' }}">
                        <i class="fa fa-circle"></i>
                        {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                    </span>

                    {{-- PRODUCT ACTIONS --}}
                    <div class="product-actions">

                        {{-- VIEW PRODUCT --}}
                        <a href="{{ url('/shop/products/' . $product->slug) }}">
                            <i class="fa fa-eye me-1"></i>
                            View Product
                        </a>

                        {{-- ADD TO CART --}}
                        @if($product->stock_quantity > 0)

                            <form
                                action="{{ route('shop.cart.add', $product->id) }}"
                                method="POST"
                                class="product-cart-form"
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="quantity"
                                    value="1"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg"
                                >
                                    <i class="fa fa-shopping-cart me-2"></i>
                                    Add to Cart
                                </button>

                            </form>

                        @else

                            <button
                                type="button"
                                class="btn btn-secondary btn-lg"
                                disabled
                            >
                                <i class="fa fa-ban me-2"></i>
                                Out of Stock
                            </button>

                        @endif

                    </div>

                </div>

            </article>

        </div>

    @empty

        {{-- NO PRODUCTS FOUND --}}
        <div class="col-12">
            <div class="shop-empty">
                <i class="fa fa-search d-block"></i>
                <h4>No products found</h4>
                <p class="text-muted">
                    Try changing your search or filter selections.
                </p>
            </div>
        </div>

    @endforelse

</div>

{{-- PAGINATION --}}
@if($products->hasPages())
    <div class="d-flex justify-content-center mt-4 shop-pagination">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
@endif
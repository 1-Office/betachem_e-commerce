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

                <div class="shop-product-image">

                    @if($product->main_image)
                        <img
                            src="{{ asset('uploads/products/' . $product->main_image) }}"
                            alt="{{ $product->name }}"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">

                        <i
                            class="fa fa-flask shop-product-placeholder"
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

                    <span class="shop-stock {{ $product->stock_quantity > 0 ? 'available' : 'unavailable' }}">
                        <i class="fa fa-circle"></i>
                        {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                    </span>

                    <div class="product-actions">
                        <a href="{{ url('/shop/products/' . $product->slug) }}">
                            <i class="fa fa-eye me-1"></i>
                            View Product
                        </a>
                    </div>

                </div>

            </article>

        </div>

    @empty

        <div class="col-12">
            <div class="shop-empty">

                <i class="fa fa-search d-block"></i>

                <h4>No products found</h4>

                <p class="text-muted">
                    Try changing your search or filter selections.
                </p>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.resetShopFilters && window.resetShopFilters()">
                    View All Products
                </button>

            </div>
        </div>

    @endforelse

</div>

{{-- AJAX PAGINATION --}}

@if($products->hasPages())

    <div class="d-flex justify-content-center mt-4 shop-pagination">

        {{ $products->links('pagination::bootstrap-5') }}

    </div>

@endif

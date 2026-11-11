@extends('layouts.admin')

@section('title', 'Product Management | Betachem')

@section('content')

<style>
.admin-products {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-products .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-products .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-products .page-heading p {
    color: #77849a;
    margin: 0;
}

.admin-products .summary-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    padding: 20px;
    height: 100%;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
}

.admin-products .summary-icon {
    width: 45px;
    height: 45px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    margin-bottom: 13px;
}

.admin-products .summary-number {
    font-size: 27px;
    font-weight: 800;
    color: #263650;
    line-height: 1.2;
}

.admin-products .summary-label {
    font-size: 13px;
    color: #7b8799;
    margin-top: 5px;
}

.admin-products .filter-card,
.admin-products .products-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
}

.admin-products .filter-card {
    padding: 20px;
    margin-bottom: 22px;
}

.admin-products .filter-card label {
    font-size: 12px;
    font-weight: 700;
    color: #64738b;
    margin-bottom: 7px;
}

.admin-products .form-control,
.admin-products .form-select {
    min-height: 43px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-products .form-control:focus,
.admin-products .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-products .btn-search {
    background: #1745ca;
    color: #fff;
    border-radius: 8px;
    min-height: 43px;
    font-weight: 600;
    padding: 0 20px;
}

.admin-products .btn-search:hover {
    background: #2d208a;
    color: #fff;
}

.admin-products .btn-reset {
    background: #eef2f8;
    color: #46566f;
    border-radius: 8px;
    min-height: 43px;
    font-weight: 600;
    padding: 0 17px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.admin-products .products-card {
    overflow: hidden;
}

.admin-products .products-card-header {
    padding: 19px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    border-bottom: 1px solid #edf0f5;
}

.admin-products .products-card-header h5 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}

.admin-products .products-card-header span {
    color: #8994a6;
    font-size: 12px;
}

.admin-products .table {
    margin-bottom: 0;
    vertical-align: middle;
}

.admin-products .table thead th {
    background: #f8fafd;
    color: #758198;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .45px;
    padding: 15px 16px;
    border-bottom: 1px solid #e9edf4;
    white-space: nowrap;
}

.admin-products .table tbody td {
    padding: 15px 16px;
    border-color: #edf0f5;
    font-size: 13px;
}

.admin-products .product-image {
    width: 55px;
    height: 55px;
    border-radius: 9px;
    border: 1px solid #edf0f5;
    object-fit: contain;
    background: #fff;
    padding: 3px;
}

.admin-products .product-placeholder {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #f0f3f9;
    color: #9aa6b8;
    font-size: 20px;
}

.admin-products .product-name {
    color: #263650;
    font-weight: 700;
    margin-bottom: 4px;
}

.admin-products .product-sku {
    color: #8994a6;
    font-size: 11px;
}

.admin-products .category-name {
    color: #64738b;
    font-size: 12px;
}

.admin-products .product-price {
    font-weight: 800;
    color: #263650;
    white-space: nowrap;
}

.admin-products .stock-number {
    font-weight: 800;
    color: #263650;
}

.admin-products .stock-description {
    font-size: 10px;
    color: #8994a6;
    margin-top: 3px;
}

.admin-products .status-badge {
    display: inline-block;
    border-radius: 30px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-products .status-active {
    background: #e5f7ed;
    color: #18834b;
}

.admin-products .status-inactive {
    background: #f0f1f4;
    color: #697386;
}

.admin-products .stock-ok {
    background: #e5f7ed;
    color: #18834b;
}

.admin-products .stock-low {
    background: #fff3d9;
    color: #a36b00;
}

.admin-products .stock-out {
    background: #fde8e8;
    color: #bd3434;
}

.admin-products .featured-badge {
    display: inline-block;
    background: #f0eaff;
    color: #6843b9;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    padding: 5px 9px;
    margin-top: 5px;
}

.admin-products .empty-state {
    text-align: center;
    padding: 55px 20px;
}

.admin-products .empty-state i {
    font-size: 42px;
    color: #bdc7d6;
    margin-bottom: 15px;
}

.admin-products .empty-state h5 {
    font-weight: 800;
    color: #34445e;
}

.admin-products .empty-state p {
    color: #8994a6;
    font-size: 13px;
}

.admin-products .pagination {
    margin: 0;
}

.admin-products .pagination-area {
    padding: 18px 22px;
    border-top: 1px solid #edf0f5;
}

.admin-products .action-button {
    border-radius: 7px;
    font-size: 11px;
    font-weight: 700;
    padding: 7px 11px;
    white-space: nowrap;
}

.admin-products .action-form {
    display: inline-block;
    margin: 0;
}

@media (max-width: 767px) {
    .admin-products {
        padding: 20px 0 40px;
    }

    .admin-products .page-heading h2 {
        font-size: 22px;
    }

    .admin-products .summary-card {
        padding: 15px;
    }

    .admin-products .summary-number {
        font-size: 23px;
    }
}
</style>

<div class="admin-products">
    <div class="container-fluid px-3 px-lg-4">

        <!-- Page heading -->
        <div class="page-heading">
            <div>
                <h2><i class="fa fa-cubes me-2"></i>Product Management</h2>
                <p>Manage and monitor your Betachem product catalogue.</p>
            </div>

            <a href="{{ route('admin.products.create') }}"
               class="btn btn-primary"
               style="background:#1745ca;border-color:#1745ca;border-radius:8px;padding:11px 20px;font-weight:700;">
                <i class="fa fa-plus me-2"></i>Add New Product
            </a>
        </div>

        <!-- Summary cards -->
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#e9efff;color:#1745ca;">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($totalProducts) }}
                    </div>
                    <div class="summary-label">Total Products</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#e5f7ed;color:#18834b;">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($activeProducts) }}
                    </div>
                    <div class="summary-label">Active Products</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#fff3d9;color:#a36b00;">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($lowStockProducts) }}
                    </div>
                    <div class="summary-label">Low Stock Products</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#fde8e8;color:#bd3434;">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($outOfStockProducts) }}
                    </div>
                    <div class="summary-label">Out of Stock</div>
                </div>
            </div>

        </div>

        <!-- Filters -->
        <div class="filter-card">

            <form method="GET" action="{{ route('admin.products.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-lg-4">
                        <label for="search">Search Products</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Product name, SKU or description..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="category_id">Category</label>
                        <select name="category_id"
                                id="category_id"
                                class="form-select">
                            <option value="">All Categories</option>

                            @foreach($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="stock">Stock Status</label>
                        <select name="stock" id="stock" class="form-select">
                            <option value="">All Stock</option>
                            <option value="available"
                                {{ request('stock') === 'available' ? 'selected' : '' }}>
                                Available
                            </option>
                            <option value="low"
                                {{ request('stock') === 'low' ? 'selected' : '' }}>
                                Low Stock
                            </option>
                            <option value="out"
                                {{ request('stock') === 'out' ? 'selected' : '' }}>
                                Out of Stock
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="status">Product Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active"
                                {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive"
                                {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-search flex-grow-1">
                            <i class="fa fa-search me-1"></i> Filter
                        </button>

                        <a href="{{ route('admin.products.index') }}"
                           class="btn-reset"
                           title="Reset filters">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>

                </div>

            </form>

        </div>

        <!-- Product table -->
        <div class="products-card">

            <div class="products-card-header">
                <div>
                    <h5><i class="fa fa-list me-2"></i>Product Catalogue</h5>
                </div>

                <span>
                    Showing {{ $products->firstItem() ?? 0 }}
                    – {{ $products->lastItem() ?? 0 }}
                    of {{ number_format($products->total()) }} products
                </span>
            </div>

            <div class="table-responsive">

                <table class="table">

                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($products as $product)

                            <tr>

                                <!-- Product image and details -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">

                                        @if($product->main_image)
                                            <img
                                                src="{{ asset('uploads/products/' . $product->main_image) }}"
                                                alt="{{ $product->name }}"
                                                class="product-image"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="product-placeholder">
                                                <i class="fa fa-picture-o"></i>
                                            </div>
                                        @endif

                                        <div>
                                            <div class="product-name">
                                                {{ $product->name }}
                                            </div>

                                            <div class="product-sku">
                                                SKU: {{ $product->sku }}
                                            </div>

                                            @if($product->is_featured)
                                                <span class="featured-badge">
                                                    <i class="fa fa-star me-1"></i>
                                                    Featured
                                                </span>
                                            @endif
                                        </div>

                                    </div>
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="category-name">
                                        {{ $product->category?->name ?? 'Uncategorised' }}
                                    </span>
                                </td>

                                <!-- Price -->
                                <td>
                                    <span class="product-price">
                                        R {{ number_format((float) $product->price, 2) }}
                                    </span>
                                </td>

                                <!-- Stock -->
                                <td>
                                    <div class="stock-number">
                                        {{ number_format($product->stock_quantity) }}
                                    </div>

                                    <div class="stock-description">
                                        Minimum: {{ number_format($product->minimum_stock) }}
                                    </div>

                                    @if($product->stock_quantity <= 0)
                                        <span class="status-badge stock-out">
                                            Out of Stock
                                        </span>
                                    @elseif($product->stock_quantity <= $product->minimum_stock)
                                        <span class="status-badge stock-low">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="status-badge stock-ok">
                                            In Stock
                                        </span>
                                    @endif
                                </td>

                                <!-- Product status -->
                                <td>
                                    @if($product->is_active)
                                        <span class="status-badge status-active">
                                            <i class="fa fa-check-circle me-1"></i>
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="fa fa-ban me-1"></i>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Product actions -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        <!-- Edit -->
                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                           class="btn btn-sm btn-outline-primary action-button"
                                           title="Edit Product">
                                            <i class="fa fa-pencil me-1"></i>
                                            Edit
                                        </a>

                                        <!-- Activate / Deactivate -->
                                        <form action="{{ route('admin.products.toggle-status', $product->id) }}"
                                              method="POST"
                                              class="action-form"
                                              onsubmit="return confirm('Are you sure you want to {{ $product->is_active ? 'deactivate' : 'activate' }} this product?');">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm {{ $product->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} action-button">

                                                @if($product->is_active)
                                                    <i class="fa fa-ban me-1"></i>
                                                    Deactivate
                                                @else
                                                    <i class="fa fa-check me-1"></i>
                                                    Activate
                                                @endif

                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6">

                                    <div class="empty-state">
                                        <i class="fa fa-search d-block"></i>

                                        <h5>No Products Found</h5>

                                        <p>
                                            No products match your current search
                                            or filter selections.
                                        </p>

                                        <a href="{{ route('admin.products.index') }}"
                                           class="btn btn-outline-primary btn-sm">
                                            Clear Filters
                                        </a>
                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="pagination-area">
                    {{ $products->links() }}
                </div>
            @endif

        </div>

    </div>
</div>

@endsection

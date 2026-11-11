@extends('layouts.admin')

@section('title', 'Category Management | Betachem')

@section('content')

<style>
.admin-categories {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-categories .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-categories .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-categories .page-heading p {
    color: #77849a;
    margin: 0;
}

.admin-categories .summary-card,
.admin-categories .filter-card,
.admin-categories .categories-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
}

.admin-categories .summary-card {
    padding: 20px;
    height: 100%;
}

.admin-categories .summary-icon {
    width: 45px;
    height: 45px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    margin-bottom: 13px;
}

.admin-categories .summary-number {
    font-size: 27px;
    font-weight: 800;
    color: #263650;
    line-height: 1.2;
}

.admin-categories .summary-label {
    font-size: 13px;
    color: #7b8799;
    margin-top: 5px;
}

.admin-categories .filter-card {
    padding: 20px;
    margin-bottom: 22px;
}

.admin-categories .filter-card label {
    font-size: 12px;
    font-weight: 700;
    color: #64738b;
    margin-bottom: 7px;
}

.admin-categories .form-control,
.admin-categories .form-select {
    min-height: 43px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-categories .form-control:focus,
.admin-categories .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-categories .btn-search {
    background: #1745ca;
    color: #fff;
    border-radius: 8px;
    min-height: 43px;
    font-weight: 600;
    padding: 0 20px;
}

.admin-categories .btn-search:hover {
    background: #2d208a;
    color: #fff;
}

.admin-categories .btn-reset {
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

.admin-categories .categories-card {
    overflow: hidden;
}

.admin-categories .categories-card-header {
    padding: 19px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    border-bottom: 1px solid #edf0f5;
}

.admin-categories .categories-card-header h5 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}

.admin-categories .categories-card-header span {
    color: #8994a6;
    font-size: 12px;
}

.admin-categories .table {
    margin-bottom: 0;
    vertical-align: middle;
}

.admin-categories .table thead th {
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

.admin-categories .table tbody td {
    padding: 15px 16px;
    border-color: #edf0f5;
    font-size: 13px;
}

.admin-categories .category-image {
    width: 55px;
    height: 55px;
    border-radius: 9px;
    border: 1px solid #edf0f5;
    object-fit: cover;
    background: #fff;
    padding: 2px;
}

.admin-categories .category-placeholder {
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

.admin-categories .category-name {
    color: #263650;
    font-weight: 700;
    margin-bottom: 4px;
}

.admin-categories .category-slug {
    color: #8994a6;
    font-size: 11px;
}

.admin-categories .category-description {
    color: #64738b;
    font-size: 12px;
    max-width: 300px;
}

.admin-categories .product-count {
    display: inline-block;
    background: #e9efff;
    color: #1745ca;
    border-radius: 20px;
    padding: 6px 11px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-categories .status-badge {
    display: inline-block;
    border-radius: 30px;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-categories .status-active {
    background: #e5f7ed;
    color: #18834b;
}

.admin-categories .status-inactive {
    background: #f0f1f4;
    color: #697386;
}

.admin-categories .sort-number {
    font-weight: 800;
    color: #64738b;
}

.admin-categories .empty-state {
    text-align: center;
    padding: 55px 20px;
}

.admin-categories .empty-state i {
    font-size: 42px;
    color: #bdc7d6;
    margin-bottom: 15px;
}

.admin-categories .empty-state h5 {
    font-weight: 800;
    color: #34445e;
}

.admin-categories .empty-state p {
    color: #8994a6;
    font-size: 13px;
}

.admin-categories .pagination {
    margin: 0;
}

.admin-categories .pagination-area {
    padding: 18px 22px;
    border-top: 1px solid #edf0f5;
}

.admin-categories .action-button {
    border-radius: 7px;
    font-size: 11px;
    font-weight: 700;
    padding: 7px 11px;
    white-space: nowrap;
}

.admin-categories .action-form {
    display: inline-block;
    margin: 0;
}

.admin-categories .alert {
    border-radius: 9px;
    font-size: 13px;
}

@media (max-width: 767px) {
    .admin-categories {
        padding: 20px 0 40px;
    }

    .admin-categories .page-heading h2 {
        font-size: 22px;
    }

    .admin-categories .summary-card {
        padding: 15px;
    }

    .admin-categories .summary-number {
        font-size: 23px;
    }
}
</style>

<div class="admin-categories">
    <div class="container-fluid px-3 px-lg-4">

        <!-- Page heading -->
        <div class="page-heading">
            <div>
                <h2><i class="fa fa-folder-open me-2"></i>Category Management</h2>
                <p>Manage and organise your Betachem product categories.</p>
            </div>

            <a href="{{ route('admin.categories.create') }}"
               class="btn btn-primary"
               style="background:#1745ca;border-color:#1745ca;border-radius:8px;padding:11px 20px;font-weight:700;">
                <i class="fa fa-plus me-2"></i>Add New Category
            </a>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <strong>Please check the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Summary cards -->
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#e9efff;color:#1745ca;">
                        <i class="fa fa-folder-open"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($totalCategories) }}
                    </div>
                    <div class="summary-label">Total Categories</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#e5f7ed;color:#18834b;">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($activeCategories) }}
                    </div>
                    <div class="summary-label">Active Categories</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#f0f1f4;color:#697386;">
                        <i class="fa fa-ban"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($inactiveCategories) }}
                    </div>
                    <div class="summary-label">Inactive Categories</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="summary-card">
                    <div class="summary-icon"
                         style="background:#fff3d9;color:#a36b00;">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <div class="summary-number">
                        {{ number_format($totalProducts) }}
                    </div>
                    <div class="summary-label">Products in Catalogue</div>
                </div>
            </div>

        </div>

        <!-- Filters -->
        <div class="filter-card">

            <form method="GET" action="{{ route('admin.categories.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-lg-7">
                        <label for="search">Search Categories</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Category name, slug or description..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="status">Category Status</label>
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

                        <a href="{{ route('admin.categories.index') }}"
                           class="btn-reset"
                           title="Reset filters">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>

                </div>

            </form>

        </div>

        <!-- Category table -->
        <div class="categories-card">

            <div class="categories-card-header">
                <div>
                    <h5><i class="fa fa-list me-2"></i>Category Catalogue</h5>
                </div>

                <span>
                    Showing {{ $categories->firstItem() ?? 0 }}
                    – {{ $categories->lastItem() ?? 0 }}
                    of {{ number_format($categories->total()) }} categories
                </span>
            </div>

            <div class="table-responsive">

                <table class="table">

                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($categories as $category)

                            <tr>

                                <!-- Category image and details -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">

                                        @if($category->image)
                                            <img
                                                src="{{ asset('uploads/categories/' . $category->image) }}"
                                                alt="{{ $category->name }}"
                                                class="category-image"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="category-placeholder">
                                                <i class="fa fa-folder"></i>
                                            </div>
                                        @endif

                                        <div>
                                            <div class="category-name">
                                                {{ $category->name }}
                                            </div>

                                            <div class="category-slug">
                                                {{ $category->slug }}
                                            </div>
                                        </div>

                                    </div>
                                </td>

                                <!-- Description -->
                                <td>
                                    <div class="category-description">
                                        {{ $category->description
                                            ? \Illuminate\Support\Str::limit($category->description, 90)
                                            : 'No description provided' }}
                                    </div>
                                </td>

                                <!-- Product count -->
                                <td>
                                    <span class="product-count">
                                        <i class="fa fa-cubes me-1"></i>
                                        {{ number_format($category->products_count) }}
                                        {{ $category->products_count === 1 ? 'Product' : 'Products' }}
                                    </span>
                                </td>

                                <!-- Sort order -->
                                <td>
                                    <span class="sort-number">
                                        {{ $category->sort_order }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td>
                                    @if($category->is_active)
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

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                           class="btn btn-sm btn-outline-primary action-button"
                                           title="Edit Category">
                                            <i class="fa fa-pencil me-1"></i>
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.categories.toggle-status', $category->id) }}"
                                              method="POST"
                                              class="action-form"
                                              onsubmit="return confirm('Are you sure you want to {{ $category->is_active ? 'deactivate' : 'activate' }} this category?');">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm {{ $category->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} action-button">

                                                @if($category->is_active)
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
                                        <i class="fa fa-folder-open d-block"></i>

                                        <h5>No Categories Found</h5>

                                        <p>
                                            No categories match your current search
                                            or filter selections.
                                        </p>

                                        <a href="{{ route('admin.categories.index') }}"
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
            @if($categories->hasPages())
                <div class="pagination-area">
                    {{ $categories->links() }}
                </div>
            @endif

        </div>

    </div>
</div>

@endsection

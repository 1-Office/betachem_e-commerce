@extends('layouts.admin')

@section('title', 'Add Product | Betachem')

@section('content')

<style>
.product-form-page {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}
.product-form-page .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}
.product-form-page h2 {
    font-size: 26px;
    font-weight: 800;
    color: #202d45;
}
.product-form-page .form-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(30,50,90,.035);
}
.product-form-page .section-title {
    font-size: 16px;
    font-weight: 800;
    color: #263650;
    padding-bottom: 14px;
    margin-bottom: 20px;
    border-bottom: 1px solid #edf0f5;
}
.product-form-page label {
    font-size: 12px;
    font-weight: 700;
    color: #64738b;
    margin-bottom: 7px;
}
.product-form-page .form-control,
.product-form-page .form-select {
    min-height: 44px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}
.product-form-page textarea.form-control {
    min-height: 110px;
}
.product-form-page .form-control:focus,
.product-form-page .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23,69,202,.08);
}
.product-form-page .btn-primary {
    background: #1745ca;
    border-color: #1745ca;
    border-radius: 8px;
    padding: 11px 22px;
    font-weight: 700;
}
.product-form-page .btn-primary:hover {
    background: #2d208a;
}
.product-form-page .btn-light {
    border-radius: 8px;
    padding: 11px 20px;
}
.product-form-page .form-check-input:checked {
    background-color: #1745ca;
    border-color: #1745ca;
}
.product-form-page .form-check-label {
    font-size: 13px;
    color: #46566f;
}
.product-form-page .help-text {
    font-size: 11px;
    color: #8994a6;
    margin-top: 6px;
}
.product-form-page .required {
    color: #dc3545;
}
@media(max-width:767px) {
    .product-form-page { padding: 20px 0 40px; }
    .product-form-page .form-card { padding: 17px; }
    .product-form-page h2 { font-size: 22px; }
}
</style>

<div class="product-form-page">
    <div class="container-fluid px-3 px-lg-4">

        <div class="page-heading">
            <div>
                <h2><i class="fa fa-plus-circle me-2"></i>Add New Product</h2>
                <p class="text-muted mb-0">
                    Add a product to the Betachem catalogue.
                </p>
            </div>

            <a href="{{ route('admin.products.index') }}"
               class="btn btn-light border">
                <i class="fa fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>

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

        <form action="{{ route('admin.products.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <!-- Product information -->
            <div class="form-card">
                <h5 class="section-title">
                    <i class="fa fa-info-circle me-2 text-primary"></i>
                    Product Information
                </h5>

                <div class="row g-3">

                    <div class="col-12 col-lg-6">
                        <label>Product Name <span class="required">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               placeholder="Enter product name"
                               required>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label>SKU <span class="required">*</span></label>
                        <input type="text"
                               name="sku"
                               class="form-control"
                               value="{{ old('sku') }}"
                               placeholder="Enter unique product SKU"
                               required>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label>Product Category <span class="required">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label>Product Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  placeholder="Describe the product, its uses and benefits...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label>Product Specifications</label>
                        <textarea name="specifications"
                                  class="form-control"
                                  placeholder="Enter technical specifications, concentration, grade, packaging, etc.">{{ old('specifications') }}</textarea>
                    </div>

                </div>
            </div>

            <!-- Pricing and inventory -->
            <div class="form-card">
                <h5 class="section-title">
                    <i class="fa fa-money me-2 text-primary"></i>
                    Pricing & Inventory
                </h5>

                <div class="row g-3">

                    <div class="col-12 col-md-4">
                        <label>Selling Price (R) <span class="required">*</span></label>
                        <input type="number"
                               name="price"
                               class="form-control"
                               value="{{ old('price', '0.00') }}"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label>Stock Quantity <span class="required">*</span></label>
                        <input type="number"
                               name="stock_quantity"
                               class="form-control"
                               value="{{ old('stock_quantity', 0) }}"
                               min="0"
                               required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label>Minimum Stock Level <span class="required">*</span></label>
                        <input type="number"
                               name="minimum_stock"
                               class="form-control"
                               value="{{ old('minimum_stock', 0) }}"
                               min="0"
                               required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label>Product Weight (kg)</label>
                        <input type="number"
                               name="weight"
                               class="form-control"
                               value="{{ old('weight') }}"
                               min="0"
                               step="0.01"
                               placeholder="e.g. 5.00">
                    </div>

                </div>
            </div>

            <!-- Product images -->
            <div class="form-card">
                <h5 class="section-title">
                    <i class="fa fa-picture-o me-2 text-primary"></i>
                    Product Images
                </h5>

                <div class="row g-3">

                    <div class="col-12 col-lg-6">
                        <label>Main Product Image</label>
                        <input type="file"
                               name="main_image"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,image/*">
                        <div class="help-text">
                            JPG, PNG or WEBP. Maximum 5 MB.
                            This image appears as the main product image.
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label>Additional Product Images</label>
                        <input type="file"
                               name="additional_images[]"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,image/*"
                               multiple>
                        <div class="help-text">
                            You may select up to 10 additional images,
                            each up to 5 MB.
                        </div>
                    </div>

                </div>
            </div>

            <!-- Publishing options -->
            <div class="form-card">
                <h5 class="section-title">
                    <i class="fa fa-cog me-2 text-primary"></i>
                    Product Settings
                </h5>

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   id="is_active"
                                   {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Product
                            </label>
                        </div>
                        <div class="help-text">
                            Active products can be displayed in the shop.
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_featured"
                                   value="1"
                                   id="is_featured"
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Featured Product
                            </label>
                        </div>
                        <div class="help-text">
                            Mark this product as featured.
                        </div>
                    </div>

                </div>
            </div>

            <!-- Form actions -->
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('admin.products.index') }}"
                   class="btn btn-light border">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-2"></i>Save Product
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

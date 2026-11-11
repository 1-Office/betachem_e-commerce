@extends('layouts.admin')

@section('title', 'Add Category | Betachem')

@section('content')

<style>
.admin-category-form {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-category-form .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-category-form .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-category-form .page-heading p {
    color: #77849a;
    margin: 0;
}

.admin-category-form .form-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
    overflow: hidden;
    margin-bottom: 22px;
}

.admin-category-form .form-card-header {
    padding: 19px 22px;
    border-bottom: 1px solid #edf0f5;
}

.admin-category-form .form-card-header h5 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
    color: #263650;
}

.admin-category-form .form-card-body {
    padding: 25px;
}

.admin-category-form label {
    font-size: 13px;
    font-weight: 700;
    color: #52627b;
    margin-bottom: 8px;
}

.admin-category-form .form-control,
.admin-category-form .form-select {
    min-height: 44px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-category-form textarea.form-control {
    min-height: 125px;
    resize: vertical;
}

.admin-category-form .form-control:focus,
.admin-category-form .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-category-form .form-text {
    font-size: 11px;
    color: #8994a6;
}

.admin-category-form .form-check {
    padding: 14px 15px 14px 40px;
    background: #f8fafd;
    border: 1px solid #edf0f5;
    border-radius: 9px;
}

.admin-category-form .form-check-input {
    margin-top: 5px;
}

.admin-category-form .form-check-input:checked {
    background-color: #1745ca;
    border-color: #1745ca;
}

.admin-category-form .form-check-label {
    font-size: 13px;
    color: #34445e;
    font-weight: 700;
}

.admin-category-form .image-preview {
    width: 110px;
    height: 110px;
    border: 1px dashed #cbd5e3;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafd;
    overflow: hidden;
    margin-top: 12px;
}

.admin-category-form .image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.admin-category-form .image-preview i {
    color: #a7b2c2;
    font-size: 28px;
}

.admin-category-form .btn-save {
    background: #1745ca;
    color: #fff;
    border: 1px solid #1745ca;
    border-radius: 8px;
    padding: 11px 22px;
    font-size: 13px;
    font-weight: 700;
}

.admin-category-form .btn-save:hover {
    background: #2d208a;
    color: #fff;
}

.admin-category-form .btn-cancel {
    background: #eef2f8;
    color: #46566f;
    border-radius: 8px;
    padding: 11px 22px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.admin-category-form .alert {
    border-radius: 9px;
    font-size: 13px;
}

@media (max-width: 767px) {
    .admin-category-form {
        padding: 20px 0 40px;
    }

    .admin-category-form .page-heading h2 {
        font-size: 22px;
    }

    .admin-category-form .form-card-body {
        padding: 18px;
    }
}
</style>

<div class="admin-category-form">
    <div class="container-fluid px-3 px-lg-4">

        <!-- Page heading -->
        <div class="page-heading">
            <div>
                <h2><i class="fa fa-plus-circle me-2"></i>Add New Category</h2>
                <p>Create a new category for your Betachem product catalogue.</p>
            </div>

            <a href="{{ route('admin.categories.index') }}"
               class="btn-cancel">
                <i class="fa fa-arrow-left me-2"></i>Back to Categories
            </a>
        </div>

        <!-- Validation errors -->
        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.categories.store') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="row">

                <!-- Main category details -->
                <div class="col-12 col-lg-8">

                    <div class="form-card">

                        <div class="form-card-header">
                            <h5><i class="fa fa-folder-open me-2"></i>Category Information</h5>
                        </div>

                        <div class="form-card-body">

                            <div class="mb-4">
                                <label for="name">Category Name <span class="text-danger">*</span></label>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="e.g. Industrial Chemicals"
                                    value="{{ old('name') }}"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="description">Category Description</label>

                                <textarea
                                    name="description"
                                    id="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Describe the products that belong to this category..."
                                >{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="image">Category Image</label>

                                <input
                                    type="file"
                                    name="image"
                                    id="image"
                                    class="form-control @error('image') is-invalid @enderror"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <div class="form-text">
                                    Accepted formats: JPG, JPEG, PNG and WEBP. Maximum size: 5MB.
                                </div>

                                @error('image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                <div class="image-preview" id="imagePreview">
                                    <i class="fa fa-picture-o"></i>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Category settings -->
                <div class="col-12 col-lg-4">

                    <div class="form-card">

                        <div class="form-card-header">
                            <h5><i class="fa fa-sliders me-2"></i>Category Settings</h5>
                        </div>

                        <div class="form-card-body">

                            <div class="mb-4">
                                <label for="sort_order">Display Order</label>

                                <input
                                    type="number"
                                    name="sort_order"
                                    id="sort_order"
                                    class="form-control @error('sort_order') is-invalid @enderror"
                                    min="0"
                                    value="{{ old('sort_order', 0) }}"
                                >

                                <div class="form-text">
                                    Lower numbers appear before higher numbers when sorted by display order.
                                </div>

                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    id="is_active"
                                    value="1"
                                    class="form-check-input"
                                    {{ old('is_active', '1') ? 'checked' : '' }}
                                >

                                <label class="form-check-label" for="is_active">
                                    Active Category
                                </label>

                                <div class="form-text">
                                    Active categories can be made available in the shop.
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Form actions -->
            <div class="d-flex justify-content-end flex-wrap gap-2 mt-2">

                <a href="{{ route('admin.categories.index') }}"
                   class="btn-cancel">
                    Cancel
                </a>

                <button type="submit" class="btn-save">
                    <i class="fa fa-save me-2"></i>Save Category
                </button>

            </div>

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const preview = document.getElementById('imagePreview');

    if (!imageInput || !preview) {
        return;
    }

    imageInput.addEventListener('change', function () {
        const file = this.files && this.files[0];

        if (!file) {
            preview.innerHTML = '<i class="fa fa-picture-o"></i>';
            return;
        }

        if (!file.type.startsWith('image/')) {
            preview.innerHTML = '<i class="fa fa-picture-o"></i>';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            preview.innerHTML = '';

            const image = document.createElement('img');
            image.src = event.target.result;
            image.alt = 'Category image preview';

            preview.appendChild(image);
        };

        reader.readAsDataURL(file);
    });
});
</script>

@endsection

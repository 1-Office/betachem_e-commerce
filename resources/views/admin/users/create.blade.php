@extends('layouts.admin')

@section('title', 'Add Staff User')

@section('content')

<style>
    .user-form-card {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
    }

    .user-form-title {
        font-weight: 700;
        color: #263248;
    }

    .user-form-subtitle {
        color: #8992a5;
        font-size: 13px;
    }

    .user-form-card label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 7px;
        color: #39445a;
    }

    .user-form-card .form-control,
    .user-form-card .form-select {
        min-height: 44px;
        border-color: #dfe4ed;
        border-radius: 8px;
    }

    .user-form-card .form-control:focus,
    .user-form-card .form-select:focus {
        border-color: #2D208A;
        box-shadow: 0 0 0 .2rem rgba(45,32,138,.10);
    }

    .user-security-note {
        padding: 15px;
        background: #fff8eb;
        border: 1px solid #f3dfb8;
        color: #825b19;
        border-radius: 9px;
        font-size: 12px;
        line-height: 1.6;
    }

    .user-submit-btn {
        background: #2D208A;
        color: #fff;
        border: 0;
        border-radius: 8px;
        padding: 11px 20px;
        font-weight: 600;
    }

    .user-submit-btn:hover {
        background: #1745CA;
        color: #fff;
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Add Staff User</h2>
        <div class="user-form-subtitle">
            Create a new Betachem staff or administrator account.
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
        <i class="fa fa-arrow-left me-2"></i> Back to Staff
    </a>
</div>

<div class="user-form-card">

    <h5 class="user-form-title mb-1">Account Information</h5>
    <p class="user-form-subtitle mb-4">
        Complete the details below to create the account.
    </p>

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

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="row g-3">

            <div class="col-md-6">
                <label for="first_name">First Name *</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    class="form-control"
                    value="{{ old('first_name') }}"
                    maxlength="100"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="last_name">Last Name *</label>
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    class="form-control"
                    value="{{ old('last_name') }}"
                    maxlength="100"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="email">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="phone">Phone Number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control"
                    value="{{ old('phone') }}"
                    maxlength="30"
                >
            </div>

            <div class="col-md-6">
                <label for="role">Access Role *</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="">Select access role</option>
                    <option value="user" @selected(old('role') === 'user')>User</option>
                    <option value="manager" @selected(old('role') === 'manager')>Manager</option>
                    <option value="administrator" @selected(old('role') === 'administrator')>Administrator</option>
                    <option value="superuser" @selected(old('role') === 'superuser')>Superuser</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="status">Account Status *</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    <option value="blocked" @selected(old('status') === 'blocked')>Blocked</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="password">Password *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                <small class="text-muted">Minimum 8 characters.</small>
            </div>

            <div class="col-md-6">
                <label for="password_confirmation">Confirm Password *</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

        </div>

        <div class="user-security-note mt-4">
            <i class="fa fa-shield me-2"></i>
            <strong>Important access control:</strong>
            Superusers have the highest level of access. They can manage staff accounts and assign roles.
            Only assign this role to trusted individuals.
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
                Cancel
            </a>

            <button type="submit" class="user-submit-btn">
                <i class="fa fa-user-plus me-2"></i>
                Create Staff Account
            </button>
        </div>

    </form>

</div>

@endsection

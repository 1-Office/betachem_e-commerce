@extends('layouts.admin')

@section('title', 'Manage Staff User')

@section('content')

<style>
    .user-edit-card {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
    }

    .user-edit-card label {
        font-size: 13px;
        font-weight: 600;
        color: #39445a;
        margin-bottom: 7px;
    }

    .user-edit-card .form-control,
    .user-edit-card .form-select {
        min-height: 44px;
        border-color: #dfe4ed;
        border-radius: 8px;
    }

    .user-edit-card .form-control:focus,
    .user-edit-card .form-select:focus {
        border-color: #2D208A;
        box-shadow: 0 0 0 .2rem rgba(45,32,138,.10);
    }

    .user-edit-subtitle {
        color: #8992a5;
        font-size: 13px;
    }

    .user-security-note {
        padding: 14px 16px;
        background: #fff8eb;
        border: 1px solid #f3dfb8;
        color: #825b19;
        border-radius: 9px;
        font-size: 12px;
        line-height: 1.6;
    }

    .user-info-note {
        padding: 14px 16px;
        background: #f1f5ff;
        border: 1px solid #dce5ff;
        color: #38558f;
        border-radius: 9px;
        font-size: 12px;
        line-height: 1.6;
    }

    .user-save-btn {
        background: #2D208A;
        color: #fff;
        border: 0;
        border-radius: 8px;
        padding: 11px 20px;
        font-weight: 600;
    }

    .user-save-btn:hover {
        background: #1745CA;
        color: #fff;
    }

    .user-role-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        background: #f0edff;
        color: #5a40ad;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
    }
</style>

@php
    $isCurrentUser = (int) auth()->id() === (int) $staff->id;
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Manage Staff User</h2>
        <div class="user-edit-subtitle">
            Update account information, access role and status.
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
        <i class="fa fa-arrow-left me-2"></i> Back to Staff
    </a>
</div>

<div class="user-edit-card">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                {{ $staff->first_name }} {{ $staff->last_name }}
            </h5>

            <div class="user-edit-subtitle">
                Staff account #{{ $staff->id }} · {{ $staff->email }}
            </div>
        </div>

        <span class="user-role-badge">
            {{ ucfirst($staff->role) }}
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa fa-check-circle me-2"></i>
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

    <form method="POST" action="{{ route('admin.users.update', $staff->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">

            <div class="col-md-6">
                <label for="first_name">First Name *</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    class="form-control"
                    value="{{ old('first_name', $staff->first_name) }}"
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
                    value="{{ old('last_name', $staff->last_name) }}"
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
                    value="{{ old('email', $staff->email) }}"
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
                    value="{{ old('phone', $staff->phone) }}"
                    maxlength="30"
                >
            </div>

            <div class="col-md-6">
                <label for="role">Access Role *</label>

                <select
                    id="role"
                    name="role"
                    class="form-select"
                    {{ $isCurrentUser ? 'disabled' : '' }}
                    required
                >
                    <option value="user" @selected(old('role', $staff->role) === 'user')>User</option>
                    <option value="manager" @selected(old('role', $staff->role) === 'manager')>Manager</option>
                    <option value="administrator" @selected(old('role', $staff->role) === 'administrator')>Administrator</option>
                    <option value="superuser" @selected(old('role', $staff->role) === 'superuser')>Superuser</option>
                </select>

                @if($isCurrentUser)
                    <input type="hidden" name="role" value="{{ $staff->role }}">
                    <small class="text-muted">
                        You cannot change your own access role.
                    </small>
                @endif
            </div>

            <div class="col-md-6">
                <label for="status">Account Status *</label>

                <select
                    id="status"
                    name="status"
                    class="form-select"
                    {{ $isCurrentUser ? 'disabled' : '' }}
                    required
                >
                    <option value="active" @selected(old('status', $staff->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $staff->status) === 'inactive')>Inactive</option>
                    <option value="blocked" @selected(old('status', $staff->status) === 'blocked')>Blocked</option>
                </select>

                @if($isCurrentUser)
                    <input type="hidden" name="status" value="{{ $staff->status }}">
                    <small class="text-muted">
                        You cannot change your own account status.
                    </small>
                @endif
            </div>

        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-1">Change Password</h6>
        <p class="user-edit-subtitle mb-3">
            Leave both password fields empty to keep the existing password.
        </p>

        <div class="row g-3">

            <div class="col-md-6">
                <label for="password">New Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password"
                >
                <small class="text-muted">Minimum 8 characters.</small>
            </div>

            <div class="col-md-6">
                <label for="password_confirmation">Confirm New Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    minlength="8"
                    autocomplete="new-password"
                >
            </div>

        </div>

        @if($isCurrentUser)
            <div class="user-info-note mt-4">
                <i class="fa fa-info-circle me-2"></i>
                You are editing your own account. Your role and account status are protected.
                You can still update your contact details and password.
            </div>
        @endif

        <div class="user-security-note mt-4">
            <i class="fa fa-shield me-2"></i>
            <strong>Security:</strong>
            Only assign administrator or superuser access to trusted staff.
            The system also protects the last active superuser from being demoted or deactivated.
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
                Cancel
            </a>

            <button type="submit" class="user-save-btn">
                <i class="fa fa-save me-2"></i>
                Save Changes
            </button>
        </div>

    </form>

</div>

@endsection

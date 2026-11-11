@extends('layouts.shop')

@section('title', 'Edit Account | Betachem')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Edit My Account</h2>
                    <p class="text-muted mb-0">
                        Update your personal information and delivery details.
                    </p>
                </div>

                <a href="{{ route('shop.account') }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('shop.account.update') }}"
                  method="POST">

                @csrf
                @method('PUT')

                {{-- Personal Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-person me-2"></i>
                            Personal Information
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    First Name
                                </label>
                                <input type="text"
                                       name="first_name"
                                       class="form-control"
                                       value="{{ old('first_name', $user->first_name) }}"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Last Name
                                </label>
                                <input type="text"
                                       name="last_name"
                                       class="form-control"
                                       value="{{ old('last_name', $user->last_name) }}"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Email Address
                                </label>
                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       value="{{ old('email', $user->email) }}"
                                       maxlength="190"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Phone Number
                                </label>
                                <input type="text"
                                       name="phone"
                                       class="form-control"
                                       value="{{ old('phone', $user->phone) }}"
                                       maxlength="50"
                                       required>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Delivery Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            Delivery Information
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Street Address
                                </label>
                                <input type="text"
                                       name="address"
                                       class="form-control"
                                       value="{{ old('address', $user->address) }}"
                                       maxlength="500"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    City / Town
                                </label>
                                <input type="text"
                                       name="city"
                                       class="form-control"
                                       value="{{ old('city', $user->city) }}"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Province
                                </label>
                                <select name="province"
                                        class="form-select"
                                        required>
                                    <option value="">Select Province</option>

                                    @foreach ($provinces as $province)
                                        <option value="{{ $province }}"
                                            {{ old('province', $user->province) === $province ? 'selected' : '' }}>
                                            {{ $province }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Postal Code
                                </label>
                                <input type="text"
                                       name="postal_code"
                                       class="form-control"
                                       value="{{ old('postal_code', $user->postal_code) }}"
                                       maxlength="20"
                                       required>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Change Password
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        <p class="text-muted small">
                            Leave these fields empty if you do not want to
                            change your password.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Current Password
                            </label>
                            <input type="password"
                                   name="current_password"
                                   class="form-control"
                                   autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                New Password
                            </label>
                            <input type="password"
                                   name="new_password"
                                   class="form-control"
                                   minlength="8"
                                   autocomplete="new-password">
                            <div class="form-text">
                                Minimum 8 characters.
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                Confirm New Password
                            </label>
                            <input type="password"
                                   name="confirm_password"
                                   class="form-control"
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>

                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex flex-wrap gap-2 justify-content-end">

                    <a href="{{ route('shop.account') }}"
                       class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

@endsection
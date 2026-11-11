@extends('layouts.shop')

@section('title', 'Create Account | Betachem')

@section('content')

@php
    $provinces = [
        'Eastern Cape',
        'Free State',
        'Gauteng',
        'KwaZulu-Natal',
        'Limpopo',
        'Mpumalanga',
        'Northern Cape',
        'North West',
        'Western Cape',
    ];
@endphp

<style>
.betachem-register {
    --bc-blue: #1d88e9;
    --bc-deep-blue: #1745ca;
    --bc-navy: #152b4a;
    --bc-text: #263548;
    --bc-muted: #758195;
    --bc-border: #e1e7ef;

    width: 100%;
    padding: 48px 24px 64px;
    background:
        radial-gradient(circle at 8% 5%,
            rgba(29, 136, 233, 0.07), transparent 32%),
        #f5f8fc;
    color: var(--bc-text);
    font-family: "Raleway", Arial, sans-serif;
}

.betachem-register *,
.betachem-register *::before,
.betachem-register *::after {
    box-sizing: border-box;
}

.betachem-register .register-container {
    width: 100%;
    max-width: 1040px;
    margin: 0 auto;
}

.betachem-register .register-card {
    overflow: hidden;
    width: 100%;
    background: #fff;
    border: 1px solid rgba(220, 228, 238, .9);
    border-radius: 20px;
    box-shadow: 0 18px 55px rgba(27, 49, 79, .09);
}

.betachem-register .register-heading {
    padding: 35px 40px 28px;
    text-align: center;
    border-bottom: 1px solid #edf1f6;
    background: linear-gradient(180deg, #fff, #fbfdff);
}

.betachem-register .register-logo {
    margin-bottom: 18px;
}

.betachem-register .register-logo img {
    display: block;
    width: 125px;
    max-width: 100%;
    height: auto;
    margin: 0 auto;
    object-fit: contain;
}

.betachem-register .register-eyebrow {
    margin: 0 0 8px;
    color: var(--bc-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.betachem-register .register-heading h1 {
    margin: 0;
    color: var(--bc-navy);
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 800;
    letter-spacing: -.8px;
}

.betachem-register .register-heading p {
    max-width: 520px;
    margin: 11px auto 0;
    color: var(--bc-muted);
    font-size: 14px;
    line-height: 1.7;
}

.betachem-register .register-form-body {
    padding: 34px 40px 38px;
}

.betachem-register .form-section + .form-section {
    margin-top: 30px;
    padding-top: 28px;
    border-top: 1px solid #edf1f6;
}

.betachem-register .section-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.betachem-register .section-icon {
    display: inline-flex;
    flex: 0 0 40px;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #edf6ff;
    color: var(--bc-deep-blue);
    font-size: 17px;
}

.betachem-register .section-heading h2 {
    margin: 0;
    color: var(--bc-navy);
    font-size: 17px;
    font-weight: 800;
}

.betachem-register .section-heading p {
    margin: 4px 0 0;
    color: var(--bc-muted);
    font-size: 12px;
}

.betachem-register .form-label {
    display: block;
    margin-bottom: 8px;
    color: #34445a;
    font-size: 13px;
    font-weight: 700;
}

.betachem-register .form-control,
.betachem-register .form-select {
    width: 100%;
    min-height: 48px;
    padding: 11px 14px;
    border: 1px solid var(--bc-border);
    border-radius: 10px;
    background-color: #fff;
    color: var(--bc-text);
    font-size: 14px;
    box-shadow: none;
    transition: border-color .2s, box-shadow .2s;
}

.betachem-register textarea.form-control {
    min-height: 95px;
    resize: vertical;
}

.betachem-register .form-control::placeholder {
    color: #a3adbb;
}

.betachem-register .form-control:focus,
.betachem-register .form-select:focus {
    border-color: var(--bc-blue);
    outline: none;
    box-shadow: 0 0 0 4px rgba(29, 136, 233, .11);
}

.betachem-register .field-hint {
    display: block;
    margin-top: 7px;
    color: var(--bc-muted);
    font-size: 11px;
    line-height: 1.5;
}

.betachem-register .password-wrap {
    position: relative;
}

.betachem-register .password-wrap .form-control {
    padding-right: 75px;
}

.betachem-register .password-toggle {
    position: absolute;
    top: 50%;
    right: 12px;
    padding: 5px 3px;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: var(--bc-deep-blue);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.betachem-register .password-toggle:focus-visible {
    outline: 2px solid var(--bc-blue);
    outline-offset: 3px;
    border-radius: 4px;
}

.betachem-register .register-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    min-height: 53px;
    margin-top: 32px;
    border: 0;
    border-radius: 11px;
    background: linear-gradient(110deg, var(--bc-blue), var(--bc-deep-blue));
    color: #fff;
    font-size: 15px;
    font-weight: 800;
    box-shadow: 0 8px 20px rgba(23, 69, 202, .18);
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}

.betachem-register .register-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 25px rgba(23, 69, 202, .25);
}

.betachem-register .register-footer {
    margin-top: 25px;
    padding-top: 22px;
    border-top: 1px solid #edf1f6;
    text-align: center;
    color: var(--bc-muted);
    font-size: 13px;
}

.betachem-register .register-footer a {
    margin-left: 5px;
    color: var(--bc-deep-blue);
    font-weight: 800;
    text-decoration: none;
}

.betachem-register .register-footer a:hover {
    text-decoration: underline;
}

.betachem-register .register-alert {
    margin-bottom: 22px;
    padding: 16px 19px;
    border: 1px solid #f3c5c8;
    border-radius: 12px;
    background: #fff5f5;
    color: #842029;
    font-size: 13px;
}

.betachem-register .register-alert strong {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
}

.betachem-register .secure-note {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 7px;
    margin-top: 20px;
    color: #7d8999;
    font-size: 11px;
    text-align: center;
}

@media (max-width: 767px) {
    .betachem-register {
        padding: 28px 14px 40px;
    }

    .betachem-register .register-card {
        border-radius: 15px;
    }

    .betachem-register .register-heading {
        padding: 28px 20px 23px;
    }

    .betachem-register .register-logo img {
        width: 115px;
    }

    .betachem-register .register-form-body {
        padding: 25px 20px 28px;
    }

    .betachem-register .section-heading {
        margin-bottom: 17px;
    }
}

@media (max-width: 420px) {
    .betachem-register .register-form-body {
        padding-right: 15px;
        padding-left: 15px;
    }

    .betachem-register .register-heading {
        padding-right: 15px;
        padding-left: 15px;
    }
}
</style>

<main class="betachem-register">
    <div class="register-container">
        <div class="register-card">

            <div class="register-heading">

                <div class="register-logo">
                    <a href="{{ route('shop.home') }}"
                       aria-label="Betachem Store Home">
                        <img src="{{ asset('assets/header_logo.png') }}"
                             alt="Betachem">
                    </a>
                </div>

                <p class="register-eyebrow">Welcome to Betachem</p>

                <h1>Create Your Account</h1>

                <p>
                    Join our online store to manage your details,
                    keep track of orders and enjoy a smoother shopping experience.
                </p>

            </div>

            <div class="register-form-body">

                @if($errors->any())
                    <div class="register-alert" role="alert">
                        <strong>Please check the following:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('shop.register.submit') }}"
                      autocomplete="on">

                    @csrf

                    <!-- PERSONAL INFORMATION -->

                    <section class="form-section">

                        <div class="section-heading">
                            <span class="section-icon">
                                <i class="fa fa-user-o"></i>
                            </span>

                            <div>
                                <h2>Personal Information</h2>
                                <p>Tell us a little about yourself.</p>
                            </div>
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="first_name" class="form-label">
                                    First Name
                                </label>

                                <input type="text"
                                       id="first_name"
                                       name="first_name"
                                       class="form-control"
                                       value="{{ old('first_name') }}"
                                       autocomplete="given-name"
                                       maxlength="100"
                                       placeholder="Enter your first name"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">
                                    Last Name
                                </label>

                                <input type="text"
                                       id="last_name"
                                       name="last_name"
                                       class="form-control"
                                       value="{{ old('last_name') }}"
                                       autocomplete="family-name"
                                       maxlength="100"
                                       placeholder="Enter your last name"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email Address
                                </label>

                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control"
                                       value="{{ old('email') }}"
                                       autocomplete="email"
                                       maxlength="254"
                                       placeholder="you@example.com"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Phone Number
                                </label>

                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       class="form-control"
                                       value="{{ old('phone') }}"
                                       autocomplete="tel"
                                       maxlength="30"
                                       placeholder="e.g. 071 234 5678"
                                       required>
                            </div>

                        </div>
                    </section>

                    <!-- ACCOUNT SECURITY -->

                    <section class="form-section">

                        <div class="section-heading">
                            <span class="section-icon">
                                <i class="fa fa-lock"></i>
                            </span>

                            <div>
                                <h2>Account Security</h2>
                                <p>Create a password to protect your account.</p>
                            </div>
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <div class="password-wrap">
                                    <input type="password"
                                           id="password"
                                           name="password"
                                           class="form-control"
                                           minlength="8"
                                           maxlength="4096"
                                           autocomplete="new-password"
                                           placeholder="Create a password"
                                           required>

                                    <button type="button"
                                            class="password-toggle"
                                            data-password-target="password"
                                            aria-controls="password"
                                            aria-pressed="false">
                                        Show
                                    </button>
                                </div>

                                <span class="field-hint">
                                    Use at least 8 characters.
                                </span>
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation"
                                       class="form-label">
                                    Confirm Password
                                </label>

                                <div class="password-wrap">
                                    <input type="password"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           class="form-control"
                                           minlength="8"
                                           maxlength="4096"
                                           autocomplete="new-password"
                                           placeholder="Enter your password again"
                                           required>

                                    <button type="button"
                                            class="password-toggle"
                                            data-password-target="password_confirmation"
                                            aria-controls="password_confirmation"
                                            aria-pressed="false">
                                        Show
                                    </button>
                                </div>

                                <span id="password-match-message"
                                      class="field-hint"
                                      aria-live="polite">
                                    Enter the same password in both fields.
                                </span>
                            </div>

                        </div>
                    </section>

                    <!-- DELIVERY ADDRESS -->

                    <section class="form-section">

                        <div class="section-heading">
                            <span class="section-icon">
                                <i class="fa fa-map-marker"></i>
                            </span>

                            <div>
                                <h2>Delivery Address</h2>
                                <p>
                                    Add the address you want associated
                                    with your account.
                                </p>
                            </div>
                        </div>

                        <div class="row g-3">

                            <div class="col-12">
                                <label for="address" class="form-label">
                                    Street Address
                                </label>

                                <textarea id="address"
                                          name="address"
                                          class="form-control"
                                          rows="3"
                                          maxlength="255"
                                          autocomplete="street-address"
                                          placeholder="Street name, house or building number"
                                          required>{{ old('address') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="city" class="form-label">
                                    City or Town
                                </label>

                                <input type="text"
                                       id="city"
                                       name="city"
                                       class="form-control"
                                       value="{{ old('city') }}"
                                       maxlength="100"
                                       autocomplete="address-level2"
                                       placeholder="Enter your city"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="province" class="form-label">
                                    Province
                                </label>

                                <select id="province"
                                        name="province"
                                        class="form-select"
                                        autocomplete="address-level1"
                                        required>

                                    <option value="">
                                        Select your province
                                    </option>

                                    @foreach($provinces as $province)
                                        <option value="{{ $province }}"
                                            {{ old('province') === $province ? 'selected' : '' }}>
                                            {{ $province }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">
                                    Postal Code
                                </label>

                                <input type="text"
                                       id="postal_code"
                                       name="postal_code"
                                       class="form-control"
                                       value="{{ old('postal_code') }}"
                                       maxlength="20"
                                       autocomplete="postal-code"
                                       placeholder="Enter postal code"
                                       required>
                            </div>

                        </div>
                    </section>

                    <button type="submit" class="register-submit">
                        <i class="fa fa-user-plus"></i>
                        Create My Account
                    </button>

                    <div class="secure-note">
                        <i class="fa fa-shield"></i>
                        Your password is securely hashed before storage.
                    </div>

                </form>

                <div class="register-footer">
                    Already have a Betachem account?

                    <a href="{{ route('shop.login') }}">
                        Sign in
                    </a>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll(
        '.betachem-register .password-toggle'
    ).forEach(function (button) {

        button.addEventListener('click', function () {

            const targetId = button.getAttribute('data-password-target');
            const passwordInput = document.getElementById(targetId);

            if (!passwordInput) {
                return;
            }

            const showingPassword = passwordInput.type === 'text';

            passwordInput.type = showingPassword ? 'password' : 'text';
            button.textContent = showingPassword ? 'Show' : 'Hide';

            button.setAttribute(
                'aria-pressed',
                showingPassword ? 'false' : 'true'
            );
        });
    });

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    const matchMessage = document.getElementById('password-match-message');

    function validatePasswordMatch() {

        if (!password || !confirmPassword || !matchMessage) {
            return;
        }

        if (confirmPassword.value === '') {
            confirmPassword.setCustomValidity('');
            matchMessage.textContent =
                'Enter the same password in both fields.';
            return;
        }

        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match.');
            matchMessage.textContent = 'The passwords do not match.';
        } else {
            confirmPassword.setCustomValidity('');
            matchMessage.textContent = 'Passwords match.';
        }
    }

    if (password && confirmPassword) {
        password.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);
    }
});
</script>

@endsection

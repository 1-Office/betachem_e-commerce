@extends('layouts.shop')

@section('title', 'Login | Betachem')

@section('content')

<style>
.betachem-login {
    --bc-blue: #1d88e9;
    --bc-deep-blue: #1745ca;
    --bc-navy: #102957;
    --bc-text: #263953;
    --bc-muted: #718096;
    --bc-border: #e1e8f1;

    width: 100%;
    min-height: 75vh;
    padding: 60px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at 10% 10%,
            rgba(29, 136, 233, 0.07), transparent 30%),
        #f5f8fc;
    color: var(--bc-text);
    font-family: "Raleway", Arial, sans-serif;
}

.betachem-login *,
.betachem-login *::before,
.betachem-login *::after {
    box-sizing: border-box;
}

.betachem-login .login-card {
    width: 100%;
    max-width: 470px;
    margin: 0 auto;
    padding: 42px;
    background: #fff;
    border: 1px solid rgba(225, 232, 241, 0.95);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(16, 41, 87, 0.09);
}

.betachem-login .login-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 28px;
}

.betachem-login .login-logo img {
    display: block;
    width: 100px;
    height: auto;
    object-fit: contain;
}

.betachem-login .login-heading {
    margin-bottom: 30px;
    text-align: center;
}

.betachem-login .login-heading h1 {
    margin: 0 0 10px;
    color: var(--bc-navy);
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.8px;
}

.betachem-login .login-heading p {
    margin: 0;
    color: var(--bc-muted);
    font-size: 13px;
    line-height: 1.8;
}

.betachem-login .login-error {
    margin-bottom: 22px;
    padding: 15px 17px;
    color: #a32626;
    background: #fff5f5;
    border: 1px solid #f2cece;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.7;
}

.betachem-login .login-error strong {
    display: block;
    margin-bottom: 5px;
}

.betachem-login .login-notice {
    margin-bottom: 22px;
    padding: 14px 16px;
    color: #236846;
    background: #f2fbf6;
    border: 1px solid #cce7da;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

.betachem-login .login-field {
    margin-bottom: 21px;
}

.betachem-login .login-field label {
    display: block;
    margin-bottom: 9px;
    color: var(--bc-text);
    font-size: 12px;
    font-weight: 800;
}

.betachem-login .input-wrap {
    display: flex;
    align-items: center;
    min-height: 54px;
    overflow: hidden;
    background: #fff;
    border: 1px solid var(--bc-border);
    border-radius: 10px;
    transition: border-color .2s, box-shadow .2s;
}

.betachem-login .input-wrap:focus-within {
    border-color: var(--bc-blue);
    box-shadow: 0 0 0 4px rgba(29, 136, 233, .09);
}

.betachem-login .input-icon {
    display: flex;
    flex: 0 0 47px;
    align-items: center;
    justify-content: center;
    color: #7b91ae;
    font-size: 15px;
}

.betachem-login .input-wrap input {
    display: block;
    width: 100%;
    min-width: 0;
    height: 52px;
    margin: 0;
    padding: 0 13px 0 0;
    color: var(--bc-text);
    background: transparent;
    border: 0;
    outline: 0;
    box-shadow: none;
    font-family: inherit;
    font-size: 13px;
}

.betachem-login .input-wrap input::placeholder {
    color: #a0adbd;
}

.betachem-login .password-toggle {
    display: flex;
    flex: 0 0 44px;
    align-items: center;
    justify-content: center;
    align-self: stretch;
    padding: 0;
    color: #8190a4;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.betachem-login .password-toggle:hover {
    color: var(--bc-blue);
}

.betachem-login .forgot-password {
    display: flex;
    justify-content: flex-end;
    margin-top: -5px;
    margin-bottom: 24px;
}

.betachem-login .forgot-password a {
    color: var(--bc-deep-blue);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.betachem-login .login-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    width: 100%;
    min-height: 53px;
    padding: 13px 20px;
    color: #fff;
    background: linear-gradient(105deg, #1745ca, #1d88e9);
    border: 0;
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(29, 91, 206, .18);
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}

.betachem-login .login-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 23px rgba(29, 91, 206, .25);
}

.betachem-login .login-divider {
    display: flex;
    align-items: center;
    gap: 13px;
    margin: 27px 0 22px;
    color: #96a3b4;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.betachem-login .login-divider::before,
.betachem-login .login-divider::after {
    flex: 1;
    height: 1px;
    background: #e8edf4;
    content: "";
}

.betachem-login .register-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    min-height: 49px;
    padding: 12px 18px;
    color: var(--bc-deep-blue);
    background: #fff;
    border: 1px solid #cbdcf5;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    transition: background .2s, border-color .2s;
}

.betachem-login .register-button:hover {
    color: var(--bc-deep-blue);
    background: #f3f8ff;
    border-color: var(--bc-blue);
}

@media (max-width: 575px) {
    .betachem-login {
        padding: 35px 15px;
    }

    .betachem-login .login-card {
        padding: 32px 23px 27px;
        border-radius: 16px;
    }

    .betachem-login .login-logo {
        margin-bottom: 24px;
    }

    .betachem-login .login-logo img {
        width: 115px;
    }

    .betachem-login .login-heading h1 {
        font-size: 29px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .betachem-login *,
    .betachem-login *::before,
    .betachem-login *::after {
        transition: none !important;
    }
}
</style>

<main class="betachem-login">
    <div class="login-card">

        <div class="login-logo">
            <a href="{{ route('shop.home') }}" aria-label="Betachem home">
                <img src="{{ asset('assets/header_logo.png') }}"
                     alt="Betachem">
            </a>
        </div>

        <div class="login-heading">
            <h1>Welcome Back</h1>
            <p>Sign in to your Betachem account to continue.</p>
        </div>

        @if(session('success'))
            <div class="login-notice">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="login-error" role="alert">
                <strong>
                    <i class="fa fa-exclamation-circle"></i>
                    Login unsuccessful
                </strong>

                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('shop.login.submit') }}"
              autocomplete="on">

            @csrf

            <div class="login-field">
                <label for="email">Email Address</label>

                <div class="input-wrap">
                    <span class="input-icon">
                        <i class="fa fa-envelope-o"></i>
                    </span>

                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter your email address"
                           autocomplete="email"
                           maxlength="254"
                           required>
                </div>
            </div>

            <div class="login-field">
                <label for="password">Password</label>

                <div class="input-wrap">
                    <span class="input-icon">
                        <i class="fa fa-lock"></i>
                    </span>

                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           autocomplete="current-password"
                           required>

                    <button type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Show password"
                            aria-pressed="false">
                        <i class="fa fa-eye"
                           id="passwordToggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-password">
    <a href="{{ route('shop.password.request') }}">
        Forgot your password?
    </a>
</div>

            <button type="submit" class="login-submit">
                <i class="fa fa-sign-in"></i>
                <span>Sign In</span>
            </button>

        </form>

        <div class="login-divider">
            New to Betachem?
        </div>

        <a href="{{ route('shop.register') }}"
           class="register-button">
            <i class="fa fa-user-plus"></i>
            Create Account
        </a>

    </div>
</main>

<script>
(function () {
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const icon = document.getElementById('passwordToggleIcon');

    if (!toggle || !password || !icon) {
        return;
    }

    toggle.addEventListener('click', function () {
        const show = password.type === 'password';

        password.type = show ? 'text' : 'password';

        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);

        toggle.setAttribute('aria-pressed', String(show));
        toggle.setAttribute(
            'aria-label',
            show ? 'Hide password' : 'Show password'
        );
    });
})();
</script>

@endsection

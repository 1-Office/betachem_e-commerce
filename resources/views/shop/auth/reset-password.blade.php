@extends('layouts.shop')

@section('title', 'Reset Password | Betachem')

@section('content')

<style>
.betachem-reset {
    width: 100%;
    min-height: 75vh;
    padding: 60px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at 10% 10%, rgba(29,136,233,.07), transparent 30%),
        #f5f8fc;
    color: #263953;
    font-family: "Raleway", Arial, sans-serif;
}

.betachem-reset .reset-card {
    width: 100%;
    max-width: 470px;
    padding: 42px;
    background: #fff;
    border: 1px solid #e1e8f1;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(16,41,87,.09);
}

.betachem-reset .reset-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eef4ff;
    color: #0d6efd;
    font-size: 30px;
}

.betachem-reset h1 {
    color: #102b49;
    font-size: 29px;
    font-weight: 800;
}

.betachem-reset .reset-description {
    color: #718096;
    font-size: 13px;
    line-height: 1.8;
}

.betachem-reset label {
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 9px;
}

.betachem-reset .form-control {
    min-height: 53px;
    border: 1px solid #e1e8f1;
    border-radius: 10px;
    font-size: 13px;
}

.betachem-reset .form-control:focus {
    border-color: #1d88e9;
    box-shadow: 0 0 0 4px rgba(29,136,233,.09);
}

.betachem-reset .reset-submit {
    width: 100%;
    min-height: 53px;
    border: 0;
    border-radius: 10px;
    color: #fff;
    background: linear-gradient(105deg, #1745ca, #1d88e9);
    font-size: 13px;
    font-weight: 800;
}

.betachem-reset .reset-link {
    color: #1745ca;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.betachem-reset .reset-alert {
    padding: 14px 16px;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

@media(max-width:575px) {
    .betachem-reset {
        padding: 35px 15px;
    }

    .betachem-reset .reset-card {
        padding: 32px 23px;
    }
}
</style>

<main class="betachem-reset">
    <div class="reset-card">

        <div class="text-center mb-4">

            <div class="reset-icon">
                <i class="fa fa-shield"></i>
            </div>

            <h1 class="mb-2">Reset Password</h1>

            <p class="reset-description mb-0">
                Create a new password for your Betachem account.
            </p>

        </div>

        @if(!empty($error))
            <div class="alert alert-danger reset-alert">
                <i class="fa fa-exclamation-circle me-2"></i>
                {{ $error }}
            </div>
        @endif

        @if(!empty($success))

            <div class="alert alert-success reset-alert">
                <i class="fa fa-check-circle me-2"></i>
                {{ $success }}
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('shop.login') }}"
                   class="btn btn-primary px-4">
                    <i class="fa fa-sign-in me-2"></i>
                    Go to Login
                </a>
            </div>

        @elseif($validReset)

            <form method="POST"
                  action="{{ route('shop.password.update', ['token' => $token]) }}">

                @csrf

                <div class="mb-3">
                    <label for="password" class="form-label">
                        New Password
                    </label>

                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control form-control-lg"
                           minlength="8"
                           autocomplete="new-password"
                           required>

                    <div class="form-text">
                        Password must be at least 8 characters.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">
                        Confirm New Password
                    </label>

                    <input type="password"
                           name="confirm_password"
                           id="confirm_password"
                           class="form-control form-control-lg"
                           minlength="8"
                           autocomplete="new-password"
                           required>
                </div>

                <button type="submit" class="reset-submit">
                    <i class="fa fa-check-circle me-2"></i>
                    Reset Password
                </button>

            </form>

            <div class="text-center mt-4">
                <a href="{{ route('shop.login') }}"
                   class="reset-link">
                    <i class="fa fa-arrow-left me-1"></i>
                    Back to Login
                </a>
            </div>

        @else

            <div class="text-center mt-4">
                <a href="{{ route('shop.password.request') }}"
                   class="btn btn-primary">
                    Request a New Reset Link
                </a>
            </div>

        @endif

    </div>
</main>

@endsection

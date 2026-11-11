@extends('layouts.shop')

@section('title', 'Forgot Password | Betachem')

@section('content')

<style>
.betachem-recovery {
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

.betachem-recovery .recovery-card {
    width: 100%;
    max-width: 470px;
    padding: 42px;
    background: #fff;
    border: 1px solid #e1e8f1;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(16,41,87,.09);
}

.betachem-recovery .recovery-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eaf4fb;
    color: #1769aa;
    font-size: 29px;
}

.betachem-recovery .recovery-label {
    color: #1769aa;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.betachem-recovery h1 {
    color: #102b49;
    font-size: 29px;
    font-weight: 800;
}

.betachem-recovery .recovery-description {
    color: #718096;
    font-size: 13px;
    line-height: 1.8;
}

.betachem-recovery label {
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 9px;
}

.betachem-recovery .form-control {
    min-height: 53px;
    border: 1px solid #e1e8f1;
    border-radius: 10px;
    font-size: 13px;
}

.betachem-recovery .form-control:focus {
    border-color: #1d88e9;
    box-shadow: 0 0 0 4px rgba(29,136,233,.09);
}

.betachem-recovery .recovery-submit {
    width: 100%;
    min-height: 53px;
    border: 0;
    border-radius: 10px;
    color: #fff;
    background: linear-gradient(105deg, #1745ca, #1d88e9);
    font-size: 13px;
    font-weight: 800;
}

.betachem-recovery .recovery-link {
    color: #1745ca;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.betachem-recovery .recovery-alert {
    padding: 14px 16px;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

@media(max-width:575px) {
    .betachem-recovery {
        padding: 35px 15px;
    }

    .betachem-recovery .recovery-card {
        padding: 32px 23px;
    }
}
</style>

<main class="betachem-recovery">
    <div class="recovery-card">

        <div class="text-center mb-4">

            <div class="recovery-icon">
                <i class="fa fa-lock"></i>
            </div>

            <div class="recovery-label mb-2">
                ACCOUNT RECOVERY
            </div>

            <h1 class="mb-2">Forgot Password?</h1>

            <p class="recovery-description mb-0">
                Enter your email address and we'll help you
                reset your password.
            </p>

        </div>

        @if(!empty($success))
            <div class="alert alert-success recovery-alert">
                <i class="fa fa-check-circle me-2"></i>
                {{ $success }}
            </div>

            @if(session('password_reset_link'))
                <div class="alert alert-warning recovery-alert">

                    <div class="fw-bold mb-2">
                        <i class="fa fa-wrench me-2"></i>
                        Local Testing Link
                    </div>

                    <p class="mb-3">
                        Email sending is not connected yet.
                        Use the button below to test the password
                        reset process.
                    </p>

                    <a href="{{ session('password_reset_link') }}"
                       class="btn btn-primary w-100">
                        <i class="fa fa-key me-2"></i>
                        Continue to Reset Password
                    </a>

                </div>
            @endif
        @else

            @if($errors->any())
                <div class="alert alert-danger recovery-alert">
                    <strong>
                        <i class="fa fa-exclamation-circle me-2"></i>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('shop.password.email') }}">

                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control form-control-lg"
                           placeholder="you@example.com"
                           autocomplete="email"
                           maxlength="254"
                           value="{{ old('email') }}"
                           required>
                </div>

                <button type="submit" class="recovery-submit">
                    <i class="fa fa-envelope me-2"></i>
                    Reset Password
                </button>

            </form>

        @endif

        <div class="text-center mt-4">
            <a href="{{ route('shop.login') }}"
               class="recovery-link">
                <i class="fa fa-arrow-left me-1"></i>
                Back to Login
            </a>
        </div>

    </div>
</main>

@endsection

<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Unified Login
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart_functions.php';

$pageTitle = 'Login';

$baseUrl = '/betachem/betachem_e-commerce';
$officialHomeUrl = '/betachem/betachem.betaoffice.co.za/public_html/';

$errors = [];
$email = '';

/*
|--------------------------------------------------------------------------
| Process Login
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validate Input
    |--------------------------------------------------------------------------
    */

    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    /*
    |--------------------------------------------------------------------------
    | Authenticate User
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id,
                first_name,
                last_name,
                email,
                password,
                role,
                status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {

            error_log('Betachem login: Failed to prepare user authentication query.');

            $errors[] = 'Unable to process your login right now. Please try again.';

        } else {

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | Check Account Status And Password
            |--------------------------------------------------------------------------
            */

            if (!$user) {

                $errors[] = 'Invalid email address or password.';

            } elseif ($user['status'] !== 'active') {

                if ($user['status'] === 'blocked') {
                    $errors[] = 'Your account has been blocked. Please contact Betachem.';
                } else {
                    $errors[] = 'Your account is not active. Please contact Betachem.';
                }

            } elseif (!password_verify($password, $user['password'])) {

                $errors[] = 'Invalid email address or password.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Preserve Guest Cart
                |--------------------------------------------------------------------------
                */

                $guestCart = [];

                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    $guestCart = $_SESSION['cart'];
                }

                /*
                |--------------------------------------------------------------------------
                | Capture Guest Session ID Before Regeneration
                |--------------------------------------------------------------------------
                */

                $guestSessionId = session_id();

                /*
                |--------------------------------------------------------------------------
                | Regenerate Session ID
                |--------------------------------------------------------------------------
                */

                if (!session_regenerate_id(true)) {

                    $errors[] = 'Unable to establish a secure session. Please try again.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Restore Guest Cart
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['cart'] = $guestCart;

                    /*
                    |--------------------------------------------------------------------------
                    | Set Authenticated User Session
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['user_id'] = (int) $user['id'];

                    $_SESSION['user_name'] = trim(
                        ($user['first_name'] ?? '') . ' ' .
                        ($user['last_name'] ?? '')
                    );

                    $_SESSION['user_first_name'] = $user['first_name'];
                    $_SESSION['user_last_name'] = $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    /*
                    |--------------------------------------------------------------------------
                    | Merge Database-Backed Guest Cart
                    |--------------------------------------------------------------------------
                    */

                    if ($guestSessionId !== '') {
                        try {
                            mergeGuestCartIntoCustomerCart(
                                (int) $user['id'],
                                $guestSessionId
                            );
                        } catch (Throwable $exception) {
                            error_log(
                                'Betachem cart merge error: ' .
                                $exception->getMessage()
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Redirect To Cart
                    |--------------------------------------------------------------------------
                    */

                    header('Location: ' . $baseUrl . '/cart.php');
                    exit;
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Display Login Page
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<style>
/* =========================================================
   BETACHEM LOGIN
========================================================= */

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
        radial-gradient(
            circle at 10% 10%,
            rgba(29, 136, 233, 0.07),
            transparent 30%
        ),
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
    background: #ffffff;
    border: 1px solid rgba(225, 232, 241, 0.95);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(16, 41, 87, 0.09);
}

.betachem-login .login-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 28px;
}

.betachem-login .login-logo a {
    display: inline-flex;
    justify-content: center;
    align-items: center;
}

.betachem-login .login-logo img {
    display: block;
    width: 100px;
    max-width: 100%;
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

.betachem-login .login-error ul {
    margin: 5px 0 0;
    padding-left: 19px;
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
    background: #ffffff;
    border: 1px solid var(--bc-border);
    border-radius: 10px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.betachem-login .input-wrap:focus-within {
    border-color: var(--bc-blue);
    box-shadow: 0 0 0 4px rgba(29, 136, 233, 0.09);
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

.betachem-login .input-wrap input:focus {
    border: 0;
    outline: 0;
    box-shadow: none;
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

.betachem-login .forgot-password a:hover {
    color: var(--bc-blue);
    text-decoration: underline;
}

.betachem-login .login-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    width: 100%;
    min-height: 53px;
    padding: 13px 20px;
    color: #ffffff;
    background: linear-gradient(105deg, #1745ca, #1d88e9);
    border: 0;
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(29, 91, 206, 0.18);
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.betachem-login .login-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 23px rgba(29, 91, 206, 0.25);
}

.betachem-login .login-submit:focus-visible,
.betachem-login .password-toggle:focus-visible {
    outline: 3px solid rgba(29, 136, 233, 0.4);
    outline-offset: 3px;
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
    background: #ffffff;
    border: 1px solid #cbdcf5;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.betachem-login .register-button:hover {
    color: var(--bc-deep-blue);
    background: #f3f8ff;
    border-color: var(--bc-blue);
}

.betachem-login .login-back {
    display: flex;
    justify-content: center;
    margin-top: 25px;
}

.betachem-login .login-back a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #718096;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.betachem-login .login-back a:hover {
    color: var(--bc-deep-blue);
}

@media (max-width: 575px) {
    .betachem-login .login-logo img {
        width: 115px;
    }
}

    .betachem-login .login-card {
        padding: 32px 23px 27px;
        border-radius: 16px;
    }

    .betachem-login .login-logo {
        margin-bottom: 24px;
    }

    .betachem-login .login-logo img {
        width: 160px;
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

        <!-- BETACHEM LOGO -->
        <div class="login-logo">
            <a
                href="<?= e($officialHomeUrl) ?>"
                aria-label="Betachem home"
            >
                <img
                    src="<?= e($baseUrl) ?>/assets/header_logo.png"
                    alt="Betachem"
                >
            </a>
        </div>

        <!-- HEADING -->
        <div class="login-heading">
            <h1>Welcome Back</h1>
            <p>Sign in to your Betachem account to continue.</p>
        </div>

        <!-- CURRENT SESSION NOTICE -->
        <?php if (
            isset($_SESSION['user_id']) &&
            isset($_SESSION['user_role'])
        ): ?>

            <div class="login-notice">
                You are currently signed in as
                <strong>
                    <?= e(
                        $_SESSION['user_name']
                        ?? $_SESSION['user_email']
                        ?? 'Customer'
                    ) ?>
                </strong>.
                You can sign in with another account below.
            </div>

        <?php endif; ?>

        <!-- LOGIN ERRORS -->
        <?php if (!empty($errors)): ?>

            <div class="login-error" role="alert">

                <strong>
                    <i
                        class="fa fa-exclamation-circle"
                        aria-hidden="true"
                    ></i>
                    Login unsuccessful
                </strong>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>

            </div>

        <?php endif; ?>

        <!-- LOGIN FORM -->
        <form
            method="POST"
            action="<?= e($baseUrl) ?>/login.php"
            autocomplete="on"
        >

            <!-- EMAIL -->
            <div class="login-field">

                <label for="email">Email Address</label>

                <div class="input-wrap">

                    <span class="input-icon">
                        <i
                            class="fa fa-envelope-o"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= e($email) ?>"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        maxlength="254"
                        required
                    >

                </div>

            </div>

            <!-- PASSWORD -->
            <div class="login-field">

                <label for="password">Password</label>

                <div class="input-wrap">

                    <span class="input-icon">
                        <i
                            class="fa fa-lock"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        id="togglePassword"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        <i
                            class="fa fa-eye"
                            id="passwordToggleIcon"
                            aria-hidden="true"
                        ></i>
                    </button>

                </div>

            </div>

            <!-- FORGOT PASSWORD -->
            <div class="forgot-password">
                <a href="<?= e($baseUrl) ?>/forgot_password.php">
                    Forgot Password?
                </a>
            </div>

            <!-- SIGN IN -->
            <button type="submit" class="login-submit">
                <i class="fa fa-sign-in" aria-hidden="true"></i>
                <span>Sign In</span>
            </button>

        </form>

        <!-- CREATE ACCOUNT -->
        <div class="login-divider">
            New to Betachem?
        </div>

        <a
            href="<?= e($baseUrl) ?>/register.php"
            class="register-button"
        >
            <i class="fa fa-user-plus" aria-hidden="true"></i>
            Create Account
        </a>



    </div>
</main>

<script>
(function () {
    'use strict';

    var toggle = document.getElementById('togglePassword');
    var password = document.getElementById('password');
    var icon = document.getElementById('passwordToggleIcon');

    if (!toggle || !password || !icon) {
        return;
    }

    toggle.addEventListener('click', function () {
        var show = password.type === 'password';

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
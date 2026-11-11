<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Customer Registration
|--------------------------------------------------------------------------
*/

// Start the session before loading files that may use it.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load required application files.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart_functions.php';

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';
$pageTitle = 'Create Account';

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

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['register_csrf_token'])) {
    $_SESSION['register_csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Form Values
|--------------------------------------------------------------------------
*/

$errors = [];

$firstName = '';
$lastName = '';
$email = '';
$phone = '';
$address = '';
$city = '';
$province = '';
$postalCode = '';

/*
|--------------------------------------------------------------------------
| Process Registration
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validate CSRF Token
    |--------------------------------------------------------------------------
    */

    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['register_csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !is_string($sessionToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        $errors[] = 'Your session has expired. Please refresh the page and try again.';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Personal Information
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {
        $errors[] = 'Please enter your first name.';
    } elseif (strlen($firstName) > 100) {
        $errors[] = 'Your first name is too long.';
    }

    if ($lastName === '') {
        $errors[] = 'Please enter your last name.';
    } elseif (strlen($lastName) > 100) {
        $errors[] = 'Your last name is too long.';
    }

    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 254) {
        $errors[] = 'Your email address is too long.';
    }

    if ($phone === '') {
        $errors[] = 'Please enter your phone number.';
    } elseif (strlen($phone) > 30) {
        $errors[] = 'Please enter a valid phone number.';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Password
    |--------------------------------------------------------------------------
    */

    if (!is_string($password) || strlen($password) < 8) {
        $errors[] = 'Your password must contain at least 8 characters.';
    } elseif (strlen($password) > 4096) {
        $errors[] = 'Please choose a shorter password.';
    }

    if (!is_string($confirmPassword) || $password !== $confirmPassword) {
        $errors[] = 'Your passwords do not match.';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Delivery Address
    |--------------------------------------------------------------------------
    */

    if ($address === '') {
        $errors[] = 'Please enter your street address.';
    } elseif (strlen($address) > 255) {
        $errors[] = 'Your street address is too long.';
    }

    if ($city === '') {
        $errors[] = 'Please enter your city.';
    } elseif (strlen($city) > 100) {
        $errors[] = 'Your city name is too long.';
    }

    if (!in_array($province, $provinces, true)) {
        $errors[] = 'Please select a valid province.';
    }

    if ($postalCode === '') {
        $errors[] = 'Please enter your postal code.';
    } elseif (strlen($postalCode) > 20) {
        $errors[] = 'Your postal code is too long.';
    }

    /*
    |--------------------------------------------------------------------------
    | Check Whether Email Already Exists
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare(
            'SELECT id FROM users WHERE email = ? LIMIT 1'
        );

        if (!$stmt) {
            error_log('Betachem registration: email lookup preparation failed.');
            $errors[] = 'We could not process your registration. Please try again.';
        } else {

            $stmt->bind_param('s', $email);

            if (!$stmt->execute()) {
                error_log('Betachem registration: email lookup failed.');
                $errors[] = 'We could not process your registration. Please try again.';
            } else {

                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $errors[] = 'An account with this email address already exists.';
                }
            }

            $stmt->close();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Customer Account
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        /*
         * Ordinary accounts use the existing "user" role.
         * Never accept a role submitted by the registration form.
         */
        $role = 'user';
        $status = 'active';

        if ($hashedPassword === false) {

            $errors[] = 'We could not secure your password. Please try again.';

        } else {

            $stmt = $conn->prepare("
                INSERT INTO users (
                    first_name,
                    last_name,
                    email,
                    phone,
                    password,
                    address,
                    city,
                    province,
                    postal_code,
                    role,
                    status,
                    created_at,
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                )
            ");

            if (!$stmt) {

                error_log('Betachem registration: account insert preparation failed.');
                $errors[] = 'We could not create your account. Please try again.';

            } else {

                $stmt->bind_param(
                    'sssssssssss',
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $hashedPassword,
                    $address,
                    $city,
                    $province,
                    $postalCode,
                    $role,
                    $status
                );

                if ($stmt->execute()) {

                    $newUserId = (int) $stmt->insert_id;
                    $stmt->close();

                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Guest Cart Before Regenerating Session
                    |--------------------------------------------------------------------------
                    */

                    $guestSessionId = session_id();

                    session_regenerate_id(true);

                    /*
                    |--------------------------------------------------------------------------
                    | Sign In Newly Registered Customer
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['user_id'] = $newUserId;
                    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                    $_SESSION['user_first_name'] = $firstName;
                    $_SESSION['user_last_name'] = $lastName;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_type'] = $role;

                    /*
                     * Rotate the registration token after successful submission.
                     */
                    $_SESSION['register_csrf_token'] = bin2hex(random_bytes(32));

                    /*
                    |--------------------------------------------------------------------------
                    | Merge Guest Cart Into Customer Cart
                    |--------------------------------------------------------------------------
                    */

                    try {

                        if (function_exists('mergeGuestCartIntoCustomerCart')) {
                            mergeGuestCartIntoCustomerCart(
                                $newUserId,
                                $guestSessionId
                            );
                        }

                    } catch (Throwable $exception) {

                        /*
                         * Keep the new account valid if cart merging fails.
                         * Record the technical detail in the server log.
                         */
                        error_log(
                            'Betachem registration cart merge failed: ' .
                            $exception->getMessage()
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Redirect After Registration
                    |--------------------------------------------------------------------------
                    */

                    redirect($baseUrl . '/account.php');
                    exit;

                } else {

                    $databaseError = $stmt->errno;

                    if ($databaseError === 1062) {
                        $errors[] = 'An account with this email address already exists.';
                    } else {
                        error_log(
                            'Betachem registration insert failed. MySQL error: ' .
                            $databaseError
                        );

                        $errors[] = 'We could not complete your registration. Please try again.';
                    }

                    $stmt->close();
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Load Page Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<style>
/* =========================================================
   BETACHEM REGISTRATION
========================================================= */

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
        radial-gradient(
            circle at 8% 5%,
            rgba(29, 136, 233, 0.07),
            transparent 32%
        ),
        #f5f8fc;
    color: var(--bc-text);
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
    background: #ffffff;
    border: 1px solid rgba(220, 228, 238, 0.9);
    border-radius: 20px;
    box-shadow: 0 18px 55px rgba(27, 49, 79, 0.09);
}

.betachem-register .register-heading {
    padding: 35px 40px 28px;
    text-align: center;
    border-bottom: 1px solid #edf1f6;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
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
    letter-spacing: -0.8px;
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
    background-color: #ffffff;
    color: var(--bc-text);
    font-size: 14px;
    box-shadow: none;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease;
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
    box-shadow: 0 0 0 4px rgba(29, 136, 233, 0.11);
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
    background: linear-gradient(
        110deg,
        var(--bc-blue),
        var(--bc-deep-blue)
    );
    color: #ffffff;
    font-size: 15px;
    font-weight: 800;
    box-shadow: 0 8px 20px rgba(23, 69, 202, 0.18);
    cursor: pointer;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.betachem-register .register-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 11px 25px rgba(23, 69, 202, 0.25);
}

.betachem-register .register-submit:focus-visible {
    outline: 3px solid rgba(29, 136, 233, 0.35);
    outline-offset: 3px;
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

.betachem-register .register-alert ul {
    margin: 0;
    padding-left: 20px;
}

.betachem-register .register-alert li + li {
    margin-top: 5px;
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
                    <a
                        href="<?= e($baseUrl) ?>/"
                        aria-label="Betachem Store Home"
                    >
                        <img
                            src="<?= e($baseUrl) ?>/assets/header_logo.png"
                            alt="Betachem"
                        >
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

                <?php if (!empty($errors)): ?>
                    <div
                        class="register-alert"
                        role="alert"
                        aria-live="polite"
                    >
                        <strong>Please check the following:</strong>

                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form
                    method="POST"
                    action="<?= e($baseUrl) ?>/register.php"
                    autocomplete="on"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['register_csrf_token']) ?>"
                    >

                    <!-- PERSONAL INFORMATION -->

                    <section class="form-section">

                        <div class="section-heading">
                            <span class="section-icon" aria-hidden="true">
                                <i class="fa fa-user-o"></i>
                            </span>

                            <div>
                                <h2>Personal Information</h2>
                                <p>Tell us a little about yourself.</p>
                            </div>
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label
                                    for="first_name"
                                    class="form-label"
                                >
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    class="form-control"
                                    value="<?= e($firstName) ?>"
                                    autocomplete="given-name"
                                    maxlength="100"
                                    placeholder="Enter your first name"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="last_name"
                                    class="form-label"
                                >
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    class="form-control"
                                    value="<?= e($lastName) ?>"
                                    autocomplete="family-name"
                                    maxlength="100"
                                    placeholder="Enter your last name"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= e($email) ?>"
                                    autocomplete="email"
                                    maxlength="254"
                                    placeholder="you@example.com"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="phone"
                                    class="form-label"
                                >
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    value="<?= e($phone) ?>"
                                    autocomplete="tel"
                                    maxlength="30"
                                    placeholder="e.g. 071 234 5678"
                                    required
                                >
                            </div>

                        </div>
                    </section>

                    <!-- PASSWORD -->

                    <section class="form-section">

                        <div class="section-heading">
                            <span class="section-icon" aria-hidden="true">
                                <i class="fa fa-lock"></i>
                            </span>

                            <div>
                                <h2>Account Security</h2>
                                <p>Create a password to protect your account.</p>
                            </div>
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label
                                    for="password"
                                    class="form-label"
                                >
                                    Password
                                </label>

                                <div class="password-wrap">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        minlength="8"
                                        maxlength="4096"
                                        autocomplete="new-password"
                                        placeholder="Create a password"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="password-toggle"
                                        data-password-target="password"
                                        aria-controls="password"
                                        aria-pressed="false"
                                    >
                                        Show
                                    </button>
                                </div>

                                <span class="field-hint">
                                    Use at least 8 characters.
                                </span>
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="confirm_password"
                                    class="form-label"
                                >
                                    Confirm Password
                                </label>

                                <div class="password-wrap">
                                    <input
                                        type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        class="form-control"
                                        minlength="8"
                                        maxlength="4096"
                                        autocomplete="new-password"
                                        placeholder="Enter your password again"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="password-toggle"
                                        data-password-target="confirm_password"
                                        aria-controls="confirm_password"
                                        aria-pressed="false"
                                    >
                                        Show
                                    </button>
                                </div>

                                <span
                                    id="password-match-message"
                                    class="field-hint"
                                    aria-live="polite"
                                >
                                    Enter the same password in both fields.
                                </span>
                            </div>

                        </div>
                    </section>

                    <!-- DELIVERY ADDRESS -->

                    <section class="form-section">

                        <div class="section-heading">
                            <span class="section-icon" aria-hidden="true">
                                <i class="fa fa-map-marker"></i>
                            </span>

                            <div>
                                <h2>Delivery Address</h2>
                                <p>Add the address you want associated with your account.</p>
                            </div>
                        </div>

                        <div class="row g-3">

                            <div class="col-12">
                                <label
                                    for="address"
                                    class="form-label"
                                >
                                    Street Address
                                </label>

                                <textarea
                                    id="address"
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    maxlength="255"
                                    autocomplete="street-address"
                                    placeholder="Street name, house or building number"
                                    required
                                ><?= e($address) ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="city"
                                    class="form-label"
                                >
                                    City or Town
                                </label>

                                <input
                                    type="text"
                                    id="city"
                                    name="city"
                                    class="form-control"
                                    value="<?= e($city) ?>"
                                    maxlength="100"
                                    autocomplete="address-level2"
                                    placeholder="Enter your city"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="province"
                                    class="form-label"
                                >
                                    Province
                                </label>

                                <select
                                    id="province"
                                    name="province"
                                    class="form-select"
                                    autocomplete="address-level1"
                                    required
                                >
                                    <option value="">Select your province</option>

                                    <?php foreach ($provinces as $provinceOption): ?>
                                        <option
                                            value="<?= e($provinceOption) ?>"
                                            <?= $province === $provinceOption ? 'selected' : '' ?>
                                        >
                                            <?= e($provinceOption) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="postal_code"
                                    class="form-label"
                                >
                                    Postal Code
                                </label>

                                <input
                                    type="text"
                                    id="postal_code"
                                    name="postal_code"
                                    class="form-control"
                                    value="<?= e($postalCode) ?>"
                                    maxlength="20"
                                    autocomplete="postal-code"
                                    placeholder="Enter postal code"
                                    required
                                >
                            </div>

                        </div>
                    </section>

                    <!-- CREATE ACCOUNT -->

                    <button type="submit" class="register-submit">
                        <i class="fa fa-user-plus" aria-hidden="true"></i>
                        Create My Account
                    </button>

                    <div class="secure-note">
                        <i class="fa fa-shield" aria-hidden="true"></i>
                        Your password is securely hashed before storage.
                    </div>

                </form>

                <div class="register-footer">
                    Already have a Betachem account?

                    <a href="<?= e($baseUrl) ?>/login.php">
                        Sign in
                    </a>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Show / Hide Password
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Confirm Password Feedback
    |--------------------------------------------------------------------------
    */

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>

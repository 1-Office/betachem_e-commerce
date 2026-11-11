<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Forgot Password
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Forgot Password';

$baseUrl = '/betachem/betachem_e-commerce';

$errors = [];
$success = '';

/*
|--------------------------------------------------------------------------
| Handle Form
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $errors[] = 'Please enter your email address.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Please enter a valid email address.';
    }

    /*
    |--------------------------------------------------------------------------
    | Find Customer
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id,
                first_name,
                email
            FROM users
            WHERE email = ?
            AND role = 'customer'
            AND status = 'active'
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $result = $stmt->get_result();

            $user = $result->fetch_assoc();

            $stmt->close();

        } else {

            $user = null;

            $errors[] =
                'We could not process your request. Please try again.';
        }

        /*
        |--------------------------------------------------------------------------
        | Create Reset Token
        |--------------------------------------------------------------------------
        */

        if (empty($errors) && $user) {

            /*
            |--------------------------------------------------------------------------
            | Remove Old Unused Tokens
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                DELETE FROM password_resets
                WHERE user_id = ?
                AND used_at IS NULL
            ");

            if ($stmt) {

                $stmt->bind_param(
                    "i",
                    $user['id']
                );

                $stmt->execute();

                $stmt->close();
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Secure Token
            |--------------------------------------------------------------------------
            */

            try {

                $token = bin2hex(
                    random_bytes(32)
                );

            } catch (Throwable $e) {

                $errors[] =
                    'We could not generate a secure reset link. Please try again.';

                $token = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Save Token
            |--------------------------------------------------------------------------
            */

            if (empty($errors) && $token !== null) {

                $expiresAt = date(
                    'Y-m-d H:i:s',
                    time() + 3600
                );

                $stmt = $conn->prepare("
                    INSERT INTO password_resets
                    (
                        user_id,
                        token,
                        expires_at
                    )
                    VALUES (?, ?, ?)
                ");

                if ($stmt) {

                    $stmt->bind_param(
                        "iss",
                        $user['id'],
                        $token,
                        $expiresAt
                    );

                    if (!$stmt->execute()) {

                        $errors[] =
                            'We could not create your password reset request. Please try again.';
                    }

                    $stmt->close();

                } else {

                    $errors[] =
                        'We could not create your password reset request. Please try again.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create Local Reset Link
            |--------------------------------------------------------------------------
            |
            | Email sending will be connected later.
            |
            */

            if (empty($errors) && $token !== null) {

                $resetLink =
                    $baseUrl .
                    '/reset_password.php?token=' .
                    urlencode($token);

                $_SESSION['password_reset_link'] = $resetLink;

                /*
                |--------------------------------------------------------------------------
                | Success Message
                |--------------------------------------------------------------------------
                */

                $success =
                    'A password reset link has been generated.';
            }

        } elseif (empty($errors)) {

            /*
            |--------------------------------------------------------------------------
            | Security
            |--------------------------------------------------------------------------
            |
            | Do not reveal whether an email exists.
            |
            */

            $success =
                'If an account exists with that email address, '
                . 'a password reset link has been generated.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7 col-lg-5">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-body p-4 p-lg-5">

                    <!-- ICON -->

                    <div class="text-center mb-4">

                        <div
                            class="d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="
                                width:70px;
                                height:70px;
                                background:#eaf4fb;
                                color:#1769aa;
                            "
                        >

                            <i class="bi bi-lock fs-2"></i>

                        </div>

                    </div>


                    <!-- HEADER -->

                    <div class="text-center mb-4">

                        <div
                            class="text-uppercase small fw-semibold mb-2"
                            style="
                                color:#1769aa;
                                letter-spacing:1.5px;
                            "
                        >
                            ACCOUNT RECOVERY
                        </div>

                        <h1
                            class="fw-bold mb-2"
                            style="color:#102b49;"
                        >
                            Forgot Password?
                        </h1>

                        <p class="text-muted mb-0">
                            Enter your email address and we'll help you
                            reset your password.
                        </p>

                    </div>


                    <!-- SUCCESS -->

                    <?php if ($success): ?>

                        <div class="alert alert-success rounded-4">

                            <i
                                class="bi bi-check-circle-fill me-2"
                            ></i>

                            <?= e($success) ?>

                        </div>


                        <?php if (
                            isset($_SESSION['password_reset_link']) &&
                            $_SESSION['password_reset_link'] !== ''
                        ): ?>

                            <div class="alert alert-warning rounded-4">

                                <div class="fw-bold mb-2">

                                    <i class="bi bi-tools me-2"></i>

                                    Local Testing Link

                                </div>

                                <div class="small mb-3">

                                    Email sending is not connected yet.
                                    Use the button below to test the
                                    password reset process.

                                </div>

                                <a
                                    href="<?= e(
                                        $_SESSION['password_reset_link']
                                    ) ?>"
                                    class="btn btn-primary w-100"
                                >

                                    <i class="bi bi-key me-2"></i>

                                    Continue to Reset Password

                                </a>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>


                    <!-- ERRORS -->

                    <?php if (!empty($errors)): ?>

                        <div class="alert alert-danger rounded-4">

                            <div class="fw-bold mb-2">

                                <i
                                    class="bi bi-exclamation-circle-fill me-2"
                                ></i>

                                Please correct the following:

                            </div>

                            <ul class="mb-0">

                                <?php foreach ($errors as $error): ?>

                                    <li>
                                        <?= e($error) ?>
                                    </li>

                                <?php endforeach; ?>

                            </ul>

                        </div>

                    <?php endif; ?>


                    <!-- FORM -->

                    <?php if (!$success): ?>

                        <form method="POST" action="">

                            <div class="mb-4">

                                <label
                                    for="email"
                                    class="form-label fw-semibold"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control form-control-lg"
                                    placeholder="you@example.com"
                                    autocomplete="email"
                                    value="<?= e(
                                        $_POST['email'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100"
                            >

                                <i class="bi bi-envelope me-2"></i>

                                Reset Password

                            </button>

                        </form>

                    <?php endif; ?>


                    <!-- LOGIN -->

                    <div class="text-center mt-4">

                        <a
                            href="<?= $baseUrl; ?>/login.php"
                            class="text-decoration-none"
                        >

                            <i class="bi bi-arrow-left me-1"></i>

                            Back to Login

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
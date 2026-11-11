<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Admin Reset Password
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

$errors = [];
$success = '';

$adminRoles = [
    'superuser',
    'administrator',
    'manager',
    'user'
];

$resetRecord = null;


/*
|--------------------------------------------------------------------------
| Validate Token
|--------------------------------------------------------------------------
*/

if ($token === '') {

    $errors[] = 'Invalid or missing password reset token.';

} else {

    $stmt = $conn->prepare("
        SELECT
            pr.id,
            pr.user_id,
            pr.token,
            pr.expires_at,
            pr.used_at,
            u.first_name,
            u.last_name,
            u.email,
            u.role,
            u.status
        FROM password_resets pr
        INNER JOIN users u ON u.id = pr.user_id
        WHERE pr.token = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();
    $resetRecord = $result->fetch_assoc();

    $stmt->close();


    if (!$resetRecord) {

        $errors[] = 'This password reset link is invalid.';

    } elseif (!empty($resetRecord['used_at'])) {

        $errors[] = 'This password reset link has already been used.';

    } elseif (strtotime($resetRecord['expires_at']) < time()) {

        $errors[] = 'This password reset link has expired.';

    } elseif (!in_array($resetRecord['role'], $adminRoles, true)) {

        $errors[] = 'This account does not have administrator access.';

    } elseif ($resetRecord['status'] !== 'active') {

        $errors[] = 'This administrator account is not active.';
    }
}


/*
|--------------------------------------------------------------------------
| Handle Password Reset
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($password === '') {

        $errors[] = 'Please enter a new password.';

    } elseif (strlen($password) < 8) {

        $errors[] = 'Your password must be at least 8 characters long.';
    }


    if ($confirmPassword === '') {

        $errors[] = 'Please confirm your new password.';

    } elseif ($password !== $confirmPassword) {

        $errors[] = 'The passwords do not match.';
    }


    /*
    |--------------------------------------------------------------------------
    | Save New Password
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $conn->begin_transaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Update Password
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "si",
                $hashedPassword,
                $resetRecord['user_id']
            );

            $stmt->execute();
            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Mark Token As Used
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE password_resets
                SET used_at = NOW()
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $resetRecord['id']
            );

            $stmt->execute();
            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Invalidate Other Reset Tokens
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE password_resets
                SET used_at = NOW()
                WHERE user_id = ?
                AND id != ?
                AND used_at IS NULL
            ");

            $stmt->bind_param(
                "ii",
                $resetRecord['user_id'],
                $resetRecord['id']
            );

            $stmt->execute();
            $stmt->close();


            $conn->commit();

            $success =
                'Your password has been reset successfully. You can now sign in to the admin panel.';

            $resetRecord = null;

        } catch (Throwable $e) {

            $conn->rollback();

            $errors[] =
                'Something went wrong while resetting your password. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Reset Password | Betachem Admin</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background: #f4f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .reset-wrapper {
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .reset-card {
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .reset-header {
            background: #0056b3;
            color: #ffffff;
            padding: 28px 30px;
        }

        .reset-header h1 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .reset-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .reset-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #243447;
        }

        .form-control {
            border-radius: 8px;
            padding: 11px 13px;
        }

        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 86, 179, 0.12);
        }

        .btn-primary {
            background: #0056b3;
            border-color: #0056b3;
            border-radius: 8px;
            padding: 11px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #004494;
            border-color: #004494;
        }

        .account-box {
            background: #f4f7fb;
            border-radius: 9px;
            padding: 14px 16px;
            margin-bottom: 22px;
        }

        .account-box small {
            display: block;
            color: #6c757d;
        }

        .account-box strong {
            color: #243447;
        }

        .back-link {
            color: #0056b3;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .success-icon {
            font-size: 42px;
            color: #198754;
        }

    </style>

</head>

<body>


<div class="reset-wrapper">

    <div class="reset-card">


        <div class="reset-header">

            <h1>
                Reset Your Password
            </h1>

            <p>
                Betachem Administration Portal
            </p>

        </div>


        <div class="reset-body">


            <?php if (!empty($success)): ?>

                <div class="text-center">

                    <div class="success-icon mb-3">
                        ✓
                    </div>

                    <h4 class="fw-bold mb-3">
                        Password Reset Successful
                    </h4>

                    <p class="text-muted mb-4">
                        <?= e($success) ?>
                    </p>

                    <a
                        href="<?= $baseUrl; ?>/admin/login.php"
                        class="btn btn-primary w-100"
                    >
                        Go to Admin Login
                    </a>

                </div>


            <?php elseif (!empty($errors)): ?>

                <div class="alert alert-danger">

                    <?php foreach ($errors as $error): ?>

                        <div>
                            <?= e($error) ?>
                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="text-center mt-4">

                    <a
                        href="<?= $baseUrl; ?>/admin/forgot_password.php"
                        class="back-link"
                    >
                        Request a New Reset Link
                    </a>

                    <span class="mx-2 text-muted">|</span>

                    <a
                        href="<?= $baseUrl; ?>/admin/login.php"
                        class="back-link"
                    >
                        Back to Login
                    </a>

                </div>


            <?php else: ?>


                <div class="account-box">

                    <small>
                        Administrator Account
                    </small>

                    <strong>
                        <?= e(
                            $resetRecord['first_name'] . ' ' .
                            $resetRecord['last_name']
                        ) ?>
                    </strong>

                    <small class="mt-1">
                        <?= e($resetRecord['email']) ?>
                    </small>

                </div>


                <form
                    method="POST"
                    action="<?= $baseUrl; ?>/admin/reset_password.php"
                >

                    <input
                        type="hidden"
                        name="token"
                        value="<?= e($token) ?>"
                    >


                    <div class="mb-3">

                        <label
                            for="password"
                            class="form-label"
                        >
                            New Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                            placeholder="Enter your new password"
                        >

                        <div class="form-text">
                            Minimum 8 characters.
                        </div>

                    </div>


                    <div class="mb-4">

                        <label
                            for="confirm_password"
                            class="form-label"
                        >
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="confirm_password"
                            name="confirm_password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm your new password"
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Reset Password
                    </button>

                </form>


                <div class="text-center mt-4">

                    <a
                        href="<?= $baseUrl; ?>/admin/login.php"
                        class="back-link"
                    >
                        Back to Admin Login
                    </a>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>


</body>

</html>
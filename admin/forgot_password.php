<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Admin Forgot Password
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


$errors = [];
$success = '';
$resetLink = '';

$adminRoles = [
    'superuser',
    'administrator',
    'manager',
    'user'
];


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
    | Find Administrator Account
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id,
                first_name,
                last_name,
                email,
                role,
                status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Only Admin Accounts Can Reset Admin Passwords
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            !in_array($user['role'], $adminRoles, true) ||
            $user['status'] !== 'active'
        ) {

            /*
            |--------------------------------------------------------------------------
            | Do Not Reveal Whether Account Exists
            |--------------------------------------------------------------------------
            */

            $success =
                'If this email address belongs to an active administrator account, a password reset link has been generated.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Invalidate Previous Unused Tokens
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE password_resets
                SET used_at = NOW()
                WHERE user_id = ?
                AND used_at IS NULL
            ");

            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Generate Secure Token
            |--------------------------------------------------------------------------
            */

            $token = bin2hex(random_bytes(32));


            /*
            |--------------------------------------------------------------------------
            | Token Expires In 1 Hour
            |--------------------------------------------------------------------------
            */

            $expiresAt = date(
                'Y-m-d H:i:s',
                time() + 3600
            );


            /*
            |--------------------------------------------------------------------------
            | Save Reset Token
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                INSERT INTO password_resets
                (
                    user_id,
                    token,
                    expires_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->bind_param(
                "iss",
                $user['id'],
                $token,
                $expiresAt
            );

            $stmt->execute();
            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Build Reset Link
            |--------------------------------------------------------------------------
            */

            $resetLink =
                'http://' .
                ($_SERVER['HTTP_HOST'] ?? 'localhost') .
                $baseUrl .
                '/admin/reset_password.php?token=' .
                urlencode($token);


            /*
            |--------------------------------------------------------------------------
            | Development Message
            |--------------------------------------------------------------------------
            */

            $success =
                'If this email address belongs to an active administrator account, a password reset link has been generated.';
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

    <title>Forgot Password | Betachem Admin</title>


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

        .forgot-wrapper {
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .forgot-card {
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .forgot-header {
            background: #0056b3;
            color: #ffffff;
            padding: 28px 30px;
        }

        .forgot-header h1 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .forgot-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .forgot-body {
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

        .back-link {
            color: #0056b3;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #f4f7fb;
            border-radius: 9px;
            padding: 14px 16px;
            font-size: 0.9rem;
            color: #5f6b76;
        }

        .dev-box {
            margin-top: 20px;
            padding: 16px;
            border: 1px dashed #198754;
            background: #f1fff7;
            border-radius: 9px;
        }

        .dev-box-title {
            color: #198754;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .reset-link {
            word-break: break-all;
            font-size: 0.85rem;
        }

    </style>

</head>

<body>


<div class="forgot-wrapper">

    <div class="forgot-card">


        <div class="forgot-header">

            <h1>
                Forgot Password?
            </h1>

            <p>
                Betachem Administration Portal
            </p>

        </div>


        <div class="forgot-body">


            <?php if (!empty($errors)): ?>

                <div class="alert alert-danger">

                    <?php foreach ($errors as $error): ?>

                        <div>
                            <?= e($error) ?>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <?php if (!empty($success)): ?>

                <div class="alert alert-success">

                    <?= e($success) ?>

                </div>


                <?php if (!empty($resetLink)): ?>

                    <div class="dev-box">

                        <div class="dev-box-title">
                            Development Reset Link
                        </div>

                        <p class="small text-muted mb-2">
                            Email sending is not configured yet.
                            Use the link below to test the password
                            reset process.
                        </p>

                        <a
                            href="<?= e($resetLink) ?>"
                            class="btn btn-success btn-sm mb-3"
                        >
                            Open Reset Password Page
                        </a>

                        <div class="reset-link text-muted">
                            <?= e($resetLink) ?>
                        </div>

                    </div>

                <?php endif; ?>


                <div class="text-center mt-4">

                    <a
                        href="<?= $baseUrl; ?>/admin/login.php"
                        class="back-link"
                    >
                        Back to Admin Login
                    </a>

                </div>


            <?php else: ?>


                <div class="info-box mb-4">

                    Enter the email address associated with your
                    administrator account. If the account is active,
                    a password reset link will be generated.

                </div>


                <form
                    method="POST"
                    action="<?= $baseUrl; ?>/admin/forgot_password.php"
                >

                    <div class="mb-4">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Email Address
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= e($_POST['email'] ?? '') ?>"
                            placeholder="admin@betachem.co.za"
                            autocomplete="email"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Generate Reset Link
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
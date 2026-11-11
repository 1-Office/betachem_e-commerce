<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Admin Roles
|--------------------------------------------------------------------------
*/

$adminRoles = [
    'superuser',
    'administrator',
    'manager',
    'user'
];


/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
|
| If an administrator is already logged in, send them directly
| to the admin dashboard.
|
*/

if (
    isset($_SESSION['user_id']) &&
    isset($_SESSION['user_role']) &&
    in_array($_SESSION['user_role'], $adminRoles, true)
) {
    header('Location: ' . $baseUrl . '/admin/index.php');
    exit;
}


$error = '';


/*
|--------------------------------------------------------------------------
| Login
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

    if ($email === '' || $password === '') {

        $error = 'Please enter your email and password.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

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

        $stmt->bind_param('s', $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | User Not Found
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            $error = 'Invalid email or password.';

        }


        /*
        |--------------------------------------------------------------------------
        | Check Administrator Role
        |--------------------------------------------------------------------------
        */

        elseif (
            !in_array(
                $user['role'],
                $adminRoles,
                true
            )
        ) {

            $error = 'You do not have administrator access.';

        }


        /*
        |--------------------------------------------------------------------------
        | Check Account Status
        |--------------------------------------------------------------------------
        */

        elseif ($user['status'] !== 'active') {

            if ($user['status'] === 'blocked') {

                $error =
                    'Your account has been blocked. Please contact the administrator.';

            } else {

                $error =
                    'Your account is not active.';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        elseif (!password_verify($password, $user['password'])) {

            $error = 'Invalid email or password.';

        }


        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */

        else {

            /*
            |--------------------------------------------------------------------------
            | Regenerate Session
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);


            /*
            |--------------------------------------------------------------------------
            | Clear Any Customer Session Data
            |--------------------------------------------------------------------------
            */

            unset(
                $_SESSION['cart'],
                $_SESSION['customer_id'],
                $_SESSION['customer_name'],
                $_SESSION['customer_email']
            );


            /*
            |--------------------------------------------------------------------------
            | Create Admin Session
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_id'] =
                (int) $user['id'];

            $_SESSION['user_name'] =
                trim(
                    $user['first_name'] .
                    ' ' .
                    $user['last_name']
                );

            $_SESSION['user_email'] =
                $user['email'];

            $_SESSION['user_role'] =
                $user['role'];


            /*
            |--------------------------------------------------------------------------
            | Explicit Role-Based Redirect
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $user['role'],
                    $adminRoles,
                    true
                )
            ) {

                header(
                    'Location: ' .
                    $baseUrl .
                    '/admin/index.php'
                );

                exit;
            }


            /*
            |--------------------------------------------------------------------------
            | Safety Fallback
            |--------------------------------------------------------------------------
            */

            header(
                'Location: ' .
                $baseUrl .
                '/index.php'
            );

            exit;
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
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login | Betachem</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <link
        rel="stylesheet"
        href="<?= $baseUrl; ?>/assets/css/style.css"
    >

</head>

<body>


<div class="min-vh-100 d-flex align-items-center bg-light">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-lg">

                    <div class="card-body p-4 p-lg-5">


                        <!-- =====================================================
                             HEADER
                        ====================================================== -->

                        <div class="text-center mb-4">

                            <div
                                class="mb-3"
                                style="font-size: 2.5rem;"
                            >

                                <i
                                    class="bi bi-shield-lock-fill text-primary"
                                ></i>

                            </div>


                            <h3 class="fw-bold text-primary">
                                BETACHEM
                            </h3>


                            <p class="text-muted mb-0">
                                Administration Login
                            </p>

                        </div>


                        <!-- =====================================================
                             ERROR
                        ====================================================== -->

                        <?php if ($error): ?>

                            <div
                                class="alert alert-danger"
                                role="alert"
                            >

                                <i
                                    class="bi bi-exclamation-circle me-2"
                                ></i>

                                <?= e($error) ?>

                            </div>

                        <?php endif; ?>


                        <!-- =====================================================
                             LOGIN FORM
                        ====================================================== -->

                        <form
                            method="POST"
                            action="<?= $baseUrl; ?>/admin/login.php"
                        >


                            <!-- Email -->

                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label fw-semibold"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="admin@betachem.co.za"
                                    value="<?= e($_POST['email'] ?? '') ?>"
                                    autocomplete="email"
                                    required
                                    autofocus
                                >

                            </div>


                            <!-- Password -->

                            <div class="mb-2">

                                <label
                                    for="password"
                                    class="form-label fw-semibold"
                                >
                                    Password
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Enter password"
                                    autocomplete="current-password"
                                    required
                                >

                            </div>


                            <!-- Forgot Password -->

                            <div class="text-end mb-4">

                                <a
                                    href="<?= $baseUrl; ?>/admin/forgot_password.php"
                                    class="small text-decoration-none"
                                >

                                    Forgot your password?

                                </a>

                            </div>


                            <!-- Login Button -->

                            <button
                                type="submit"
                                class="btn btn-primary w-100 py-2"
                            >

                                <i
                                    class="bi bi-box-arrow-in-right me-2"
                                ></i>

                                Login to Admin

                            </button>


                        </form>


                        <!-- =====================================================
                             BACK TO STORE
                        ====================================================== -->

                        <div class="text-center mt-4">

                            <a
                                href="<?= $baseUrl; ?>/"
                                class="text-muted small text-decoration-none"
                            >

                                <i
                                    class="bi bi-arrow-left me-1"
                                ></i>

                                Back to Store

                            </a>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


</body>

</html>
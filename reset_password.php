<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Reset Password
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Page Title
|--------------------------------------------------------------------------
*/

$pageTitle = 'Reset Password';


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$token = trim($_GET['token'] ?? '');

$error = '';
$success = '';

$validReset = false;
$userId = null;


/*
|--------------------------------------------------------------------------
| Validate Token
|--------------------------------------------------------------------------
*/

if ($token !== '') {

    $stmt = $conn->prepare("
        SELECT
            pr.id,
            pr.user_id,
            pr.expires_at,
            u.email,
            u.first_name,
            u.last_name
        FROM password_resets pr
        INNER JOIN users u
            ON u.id = pr.user_id
        WHERE pr.token = ?
          AND pr.used_at IS NULL
          AND pr.expires_at > NOW()
          AND u.role = 'customer'
          AND u.status = 'active'
        LIMIT 1
    ");

    $stmt->bind_param(
        "s",
        $token
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $reset = $result->fetch_assoc();

    $stmt->close();


    if ($reset) {

        $validReset = true;

        $userId = (int) $reset['user_id'];

    } else {

        $error =
            'This password reset link is invalid or has expired.';
    }

} else {

    $error =
        'No password reset token was provided.';
}


/*
|--------------------------------------------------------------------------
| Handle Password Reset
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $validReset
) {

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validate Password
    |--------------------------------------------------------------------------
    */

    if (strlen($password) < 8) {

        $error =
            'Your password must be at least 8 characters long.';

    } elseif ($password !== $confirmPassword) {

        $error =
            'The passwords do not match.';

    } else {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        try {

            $conn->begin_transaction();


            /*
            |--------------------------------------------------------------------------
            | Update Password
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE users
                SET password = ?,
                    updated_at = NOW()
                WHERE id = ?
                  AND role = 'customer'
                  AND status = 'active'
            ");

            $stmt->bind_param(
                "si",
                $hashedPassword,
                $userId
            );

            $stmt->execute();


            if ($stmt->affected_rows < 1) {

                $stmt->close();

                throw new RuntimeException(
                    'Unable to update the password.'
                );
            }

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Mark Current Token As Used
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE password_resets
                SET used_at = NOW()
                WHERE token = ?
                  AND user_id = ?
                  AND used_at IS NULL
            ");

            $stmt->bind_param(
                "si",
                $token,
                $userId
            );

            $stmt->execute();

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Delete Other Reset Tokens
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                DELETE FROM password_resets
                WHERE user_id = ?
                  AND token != ?
            ");

            $stmt->bind_param(
                "is",
                $userId,
                $token
            );

            $stmt->execute();

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $conn->commit();

            $success =
                'Your password has been reset successfully.';

            $validReset = false;

        } catch (Throwable $e) {

            if ($conn->errno === 0) {
                // Transaction may already have been closed.
            }

            try {
                $conn->rollback();
            } catch (Throwable $rollbackError) {
                // Ignore rollback errors.
            }

            $error =
                'Something went wrong while resetting your password. Please try again.';
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

        <div class="col-lg-5 col-md-7">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4 p-md-5">


                    <!-- =================================================
                         HEADER
                    ================================================== -->

                    <div class="text-center mb-4">

                        <div
                            class="rounded-circle
                                   d-inline-flex
                                   align-items-center
                                   justify-content-center
                                   mb-3"
                            style="
                                width:70px;
                                height:70px;
                                background:#eef4ff;
                                color:#0d6efd;
                                font-size:30px;
                            "
                        >

                            <i class="bi bi-shield-lock"></i>

                        </div>


                        <h2 class="fw-bold mb-2">
                            Reset Password
                        </h2>


                        <p class="text-muted mb-0">

                            Create a new password for your
                            Betachem account.

                        </p>

                    </div>


                    <!-- =================================================
                         ERROR
                    ================================================== -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <i
                                class="bi
                                       bi-exclamation-circle
                                       me-2"
                            ></i>

                            <?= e($error) ?>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         SUCCESS
                    ================================================== -->

                    <?php if ($success): ?>

                        <div class="alert alert-success">

                            <i
                                class="bi
                                       bi-check-circle
                                       me-2"
                            ></i>

                            <?= e($success) ?>

                        </div>


                        <div class="text-center mt-4">

                            <a
                                href="<?= $baseUrl; ?>/login.php"
                                class="btn btn-primary px-4"
                            >

                                <i
                                    class="bi
                                           bi-box-arrow-in-right
                                           me-2"
                                ></i>

                                Go to Login

                            </a>

                        </div>


                    <?php elseif ($validReset): ?>


                        <!-- =================================================
                             RESET FORM
                        ================================================== -->

                        <form
                            method="POST"
                            action="<?= $baseUrl; ?>/reset_password.php?token=<?= urlencode($token) ?>"
                        >


                            <!-- NEW PASSWORD -->

                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label fw-semibold"
                                >
                                    New Password
                                </label>


                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control form-control-lg"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                >


                                <div class="form-text">

                                    Password must be at least
                                    8 characters.

                                </div>

                            </div>


                            <!-- CONFIRM PASSWORD -->

                            <div class="mb-4">

                                <label
                                    for="confirm_password"
                                    class="form-label fw-semibold"
                                >
                                    Confirm New Password
                                </label>


                                <input
                                    type="password"
                                    name="confirm_password"
                                    id="confirm_password"
                                    class="form-control form-control-lg"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                >

                            </div>


                            <!-- SUBMIT -->

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100"
                            >

                                <i
                                    class="bi
                                           bi-check2-circle
                                           me-2"
                                ></i>

                                Reset Password

                            </button>

                        </form>


                        <!-- LOGIN -->

                        <div class="text-center mt-4">

                            <a
                                href="<?= $baseUrl; ?>/login.php"
                                class="text-decoration-none"
                            >

                                <i
                                    class="bi
                                           bi-arrow-left
                                           me-1"
                                ></i>

                                Back to Login

                            </a>

                        </div>


                    <?php else: ?>


                        <!-- =================================================
                             INVALID TOKEN
                        ================================================== -->

                        <div class="text-center mt-4">

                            <a
                                href="<?= $baseUrl; ?>/forgot_password.php"
                                class="btn btn-primary"
                            >

                                Request a New Reset Link

                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>
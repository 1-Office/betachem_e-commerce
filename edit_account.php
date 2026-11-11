<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Edit Customer Account
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = (int) currentUserId();

$baseUrl = '/betachem/betachem_e-commerce';

$errors = [];
$success = '';


/*
|--------------------------------------------------------------------------
| Load Current User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        first_name,
        last_name,
        email,
        phone,
        address,
        city,
        province,
        postal_code,
        role,
        status
    FROM users
    WHERE id = ?
    AND role = 'customer'
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    session_unset();
    session_destroy();

    header('Location: ' . $baseUrl . '/login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Form Values
|--------------------------------------------------------------------------
*/

$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';
$city = $user['city'] ?? '';
$province = $user['province'] ?? '';
$postalCode = $user['postal_code'] ?? '';

$currentPassword = '';
$newPassword = '';
$confirmPassword = '';


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {

        $errors[] = 'First name is required.';

    } elseif (mb_strlen($firstName) > 100) {

        $errors[] = 'First name cannot exceed 100 characters.';
    }


    if ($lastName === '') {

        $errors[] = 'Last name is required.';

    } elseif (mb_strlen($lastName) > 100) {

        $errors[] = 'Last name cannot exceed 100 characters.';
    }


    if ($email === '') {

        $errors[] = 'Email address is required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Please enter a valid email address.';

    } elseif (mb_strlen($email) > 190) {

        $errors[] = 'Email address cannot exceed 190 characters.';
    }


    if ($phone === '') {

        $errors[] = 'Phone number is required.';

    } elseif (mb_strlen($phone) > 50) {

        $errors[] = 'Phone number cannot exceed 50 characters.';
    }


    if ($address === '') {

        $errors[] = 'Delivery address is required.';

    } elseif (mb_strlen($address) > 500) {

        $errors[] = 'Delivery address is too long.';
    }


    if ($city === '') {

        $errors[] = 'City is required.';

    } elseif (mb_strlen($city) > 100) {

        $errors[] = 'City cannot exceed 100 characters.';
    }


    $allowedProvinces = [
        'Eastern Cape',
        'Free State',
        'Gauteng',
        'KwaZulu-Natal',
        'Limpopo',
        'Mpumalanga',
        'Northern Cape',
        'North West',
        'Western Cape'
    ];


    if ($province === '') {

        $errors[] = 'Province is required.';

    } elseif (!in_array($province, $allowedProvinces, true)) {

        $errors[] = 'Please select a valid province.';
    }


    if ($postalCode === '') {

        $errors[] = 'Postal code is required.';

    } elseif (mb_strlen($postalCode) > 20) {

        $errors[] = 'Postal code cannot exceed 20 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Check Email Availability
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "si",
            $email,
            $userId
        );

        $stmt->execute();

        $emailResult = $stmt->get_result();

        if ($emailResult->num_rows > 0) {

            $errors[] =
                'That email address is already being used by another account.';
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Password Change Detection
    |--------------------------------------------------------------------------
    */

    $changePassword = (
        $currentPassword !== '' ||
        $newPassword !== '' ||
        $confirmPassword !== ''
    );


    /*
    |--------------------------------------------------------------------------
    | Password Validation
    |--------------------------------------------------------------------------
    */

    if ($changePassword) {

        if ($currentPassword === '') {

            $errors[] =
                'Enter your current password to change your password.';
        }


        if ($newPassword === '') {

            $errors[] =
                'Enter a new password.';

        } elseif (strlen($newPassword) < 6) {

            $errors[] =
                'Your new password must be at least 6 characters.';

        } elseif (strlen($newPassword) > 255) {

            $errors[] =
                'Your new password is too long.';
        }


        if ($confirmPassword === '') {

            $errors[] =
                'Please confirm your new password.';
        }


        if (
            $newPassword !== '' &&
            $confirmPassword !== '' &&
            $newPassword !== $confirmPassword
        ) {

            $errors[] =
                'The new passwords do not match.';
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        */

        if (
            $currentPassword !== '' &&
            empty($errors)
        ) {

            $stmt = $conn->prepare("
                SELECT password
                FROM users
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $userId
            );

            $stmt->execute();

            $passwordResult = $stmt->get_result();
            $passwordRow = $passwordResult->fetch_assoc();

            $stmt->close();


            if (
                !$passwordRow ||
                !password_verify(
                    $currentPassword,
                    $passwordRow['password']
                )
            ) {

                $errors[] =
                    'Your current password is incorrect.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Account
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        if ($changePassword) {

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                UPDATE users
                SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    province = ?,
                    postal_code = ?,
                    password = ?,
                    updated_at = NOW()
                WHERE id = ?
                AND role = 'customer'
            ");

            $stmt->bind_param(
                "sssssssssi",
                $firstName,
                $lastName,
                $email,
                $phone,
                $address,
                $city,
                $province,
                $postalCode,
                $hashedPassword,
                $userId
            );

        } else {

            $stmt = $conn->prepare("
                UPDATE users
                SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    province = ?,
                    postal_code = ?,
                    updated_at = NOW()
                WHERE id = ?
                AND role = 'customer'
            ");

            $stmt->bind_param(
                "ssssssssi",
                $firstName,
                $lastName,
                $email,
                $phone,
                $address,
                $city,
                $province,
                $postalCode,
                $userId
            );
        }


        if ($stmt->execute()) {

            /*
            |--------------------------------------------------------------------------
            | Update Session
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_name'] =
                $firstName . ' ' . $lastName;

            $_SESSION['user_email'] =
                $email;


            /*
            |--------------------------------------------------------------------------
            | Update Local User Data
            |--------------------------------------------------------------------------
            */

            $user['first_name'] = $firstName;
            $user['last_name'] = $lastName;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['address'] = $address;
            $user['city'] = $city;
            $user['province'] = $province;
            $user['postal_code'] = $postalCode;


            $success =
                'Your account details have been updated successfully.';


            /*
            |--------------------------------------------------------------------------
            | Clear Password Fields
            |--------------------------------------------------------------------------
            */

            $currentPassword = '';
            $newPassword = '';
            $confirmPassword = '';

        } else {

            $errors[] =
                'Something went wrong while updating your account.';
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

$pageTitle = 'Edit Account';

require_once __DIR__ . '/includes/header.php';

?>


<style>

.account-page {
    max-width: 1050px;
    margin: 0 auto;
}

.account-section-title {
    color: #102b49;
}

.account-section-icon {
    color: #1769aa;
}

.account-form-card {
    border-radius: 16px;
}

.account-form-card .form-control,
.account-form-card .form-select {
    border-radius: 10px;
    border-color: #dce3ea;
}

.account-form-card .form-control:focus,
.account-form-card .form-select:focus {
    border-color: #1769aa;
    box-shadow: 0 0 0 .2rem rgba(23, 105, 170, .12);
}

.security-note {
    border-radius: 14px;
}

</style>


<div class="container py-5">

    <div class="account-page">


        <!-- =====================================================
             BACK LINK
             ===================================================== -->

        <div class="mb-4">

            <a
                href="<?= $baseUrl ?>/account.php"
                class="text-decoration-none"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Back to My Account

            </a>

        </div>


        <!-- =====================================================
             PAGE HEADER
             ===================================================== -->

        <div class="mb-4">

            <div
                class="text-uppercase small fw-semibold mb-2"
                style="
                    color:#1769aa;
                    letter-spacing:1.5px;
                "
            >
                MY ACCOUNT
            </div>

            <h1
                class="fw-bold mb-2"
                style="color:#102b49;"
            >
                Edit Account
            </h1>

            <p class="text-muted mb-0">
                Update your personal information, delivery details
                and account password.
            </p>

        </div>


        <!-- =====================================================
             SUCCESS MESSAGE
             ===================================================== -->

        <?php if ($success !== ''): ?>

            <div
                class="alert alert-success rounded-4 shadow-sm"
            >

                <i
                    class="bi bi-check-circle-fill me-2"
                ></i>

                <?= e($success) ?>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             ERROR MESSAGES
             ===================================================== -->

        <?php if (!empty($errors)): ?>

            <div
                class="alert alert-danger rounded-4 shadow-sm"
            >

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


        <!-- =====================================================
             ACCOUNT FORM
             ===================================================== -->

        <div
            class="card border-0 shadow-sm account-form-card mb-4"
        >

            <div class="card-body p-4 p-lg-5">

                <form
                    method="POST"
                    action=""
                >


                    <!-- =================================================
                         PERSONAL INFORMATION
                         ================================================= -->

                    <div class="mb-4">

                        <h5
                            class="fw-bold mb-1 account-section-title"
                        >

                            <i
                                class="bi bi-person me-2 account-section-icon"
                            ></i>

                            Personal Information

                        </h5>

                        <p class="text-muted small mb-0">
                            Keep your contact details up to date.
                        </p>

                    </div>


                    <div class="row g-3">


                        <!-- First Name -->

                        <div class="col-md-6">

                            <label
                                for="first_name"
                                class="form-label fw-semibold"
                            >
                                First Name
                            </label>

                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                class="form-control form-control-lg"
                                value="<?= e($user['first_name']) ?>"
                                maxlength="100"
                                autocomplete="given-name"
                                required
                            >

                        </div>


                        <!-- Last Name -->

                        <div class="col-md-6">

                            <label
                                for="last_name"
                                class="form-label fw-semibold"
                            >
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                class="form-control form-control-lg"
                                value="<?= e($user['last_name']) ?>"
                                maxlength="100"
                                autocomplete="family-name"
                                required
                            >

                        </div>


                        <!-- Email -->

                        <div class="col-md-6">

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
                                value="<?= e($user['email']) ?>"
                                maxlength="190"
                                autocomplete="email"
                                required
                            >

                        </div>


                        <!-- Phone -->

                        <div class="col-md-6">

                            <label
                                for="phone"
                                class="form-label fw-semibold"
                            >
                                Phone Number
                            </label>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control form-control-lg"
                                value="<?= e($user['phone']) ?>"
                                maxlength="50"
                                autocomplete="tel"
                                required
                            >

                        </div>

                    </div>


                    <hr class="my-5">


                    <!-- =================================================
                         DELIVERY INFORMATION
                         ================================================= -->

                    <div class="mb-4">

                        <h5
                            class="fw-bold mb-1 account-section-title"
                        >

                            <i
                                class="bi bi-geo-alt me-2 account-section-icon"
                            ></i>

                            Delivery Information

                        </h5>

                        <p class="text-muted small mb-0">
                            This information can be used during checkout.
                        </p>

                    </div>


                    <div class="row g-3">


                        <!-- Address -->

                        <div class="col-12">

                            <label
                                for="address"
                                class="form-label fw-semibold"
                            >
                                Delivery Address
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                class="form-control"
                                rows="3"
                                maxlength="500"
                                autocomplete="street-address"
                                required
                            ><?= e($user['address']) ?></textarea>

                        </div>


                        <!-- City -->

                        <div class="col-md-6">

                            <label
                                for="city"
                                class="form-label fw-semibold"
                            >
                                City
                            </label>

                            <input
                                type="text"
                                id="city"
                                name="city"
                                class="form-control form-control-lg"
                                value="<?= e($user['city']) ?>"
                                maxlength="100"
                                autocomplete="address-level2"
                                required
                            >

                        </div>


                        <!-- Province -->

                        <div class="col-md-6">

                            <label
                                for="province"
                                class="form-label fw-semibold"
                            >
                                Province
                            </label>

                            <select
                                id="province"
                                name="province"
                                class="form-select form-select-lg"
                                required
                            >

                                <option value="">
                                    Select Province
                                </option>

                                <?php foreach ($allowedProvinces as $provinceOption): ?>

                                    <option
                                        value="<?= e($provinceOption) ?>"
                                        <?= $user['province'] === $provinceOption
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= e($provinceOption) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Postal Code -->

                        <div class="col-md-6">

                            <label
                                for="postal_code"
                                class="form-label fw-semibold"
                            >
                                Postal Code
                            </label>

                            <input
                                type="text"
                                id="postal_code"
                                name="postal_code"
                                class="form-control form-control-lg"
                                value="<?= e($user['postal_code']) ?>"
                                maxlength="20"
                                autocomplete="postal-code"
                                required
                            >

                        </div>

                    </div>


                    <hr class="my-5">


                    <!-- =================================================
                         PASSWORD
                         ================================================= -->

                    <div class="mb-4">

                        <h5
                            class="fw-bold mb-1 account-section-title"
                        >

                            <i
                                class="bi bi-lock me-2 account-section-icon"
                            ></i>

                            Change Password

                        </h5>

                        <p class="text-muted small mb-0">
                            Leave these fields empty if you do not want
                            to change your password.
                        </p>

                    </div>


                    <div class="row g-3">


                        <!-- Current Password -->

                        <div class="col-12">

                            <label
                                for="current_password"
                                class="form-label fw-semibold"
                            >
                                Current Password
                            </label>

                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control form-control-lg"
                                autocomplete="current-password"
                            >

                        </div>


                        <!-- New Password -->

                        <div class="col-md-6">

                            <label
                                for="new_password"
                                class="form-label fw-semibold"
                            >
                                New Password
                            </label>

                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control form-control-lg"
                                minlength="6"
                                maxlength="255"
                                autocomplete="new-password"
                            >

                            <div class="form-text">
                                Minimum 6 characters.
                            </div>

                        </div>


                        <!-- Confirm Password -->

                        <div class="col-md-6">

                            <label
                                for="confirm_password"
                                class="form-label fw-semibold"
                            >
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control form-control-lg"
                                minlength="6"
                                maxlength="255"
                                autocomplete="new-password"
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         ACTIONS
                         ================================================= -->

                    <div
                        class="d-flex flex-column flex-sm-row gap-2 mt-5"
                    >

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg px-4"
                        >

                            <i
                                class="bi bi-check2-circle me-2"
                            ></i>

                            Save Changes

                        </button>


                        <a
                            href="<?= $baseUrl ?>/account.php"
                            class="btn btn-outline-secondary btn-lg px-4"
                        >

                            Cancel

                        </a>

                    </div>


                </form>

            </div>

        </div>


        <!-- =====================================================
             SECURITY NOTE
             ===================================================== -->

        <div
            class="alert alert-light border rounded-4 security-note"
        >

            <div class="d-flex align-items-start">

                <i
                    class="bi bi-shield-check fs-4 me-3"
                    style="color:#1769aa;"
                ></i>

                <div>

                    <strong>
                        Your account security matters.
                    </strong>

                    <div class="small text-muted mt-1">

                        Your password is stored securely using
                        one-way password hashing and is never displayed
                        in your account.

                    </div>

                </div>

            </div>

        </div>


    </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
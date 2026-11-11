<?php

require_once __DIR__ . '/../includes/auth.php';

requireSuperuser();

$pageTitle = 'Add Admin User';

$baseUrl = '/betachem/betachem_e-commerce';

$errors = [];
$success = '';

$firstName = '';
$lastName = '';
$email = '';
$phone = '';
$role = 'user';
$status = 'active';


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role      = $_POST['role'] ?? 'user';
    $status    = $_POST['status'] ?? 'active';


    /*
    |--------------------------------------------------------------------------
    | Validate Name
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $errors[] = 'Email address is required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Please enter a valid email address.';

    } else {

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'An account with this email address already exists.';
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Role
    |--------------------------------------------------------------------------
    */

    $allowedRoles = [
        'administrator',
        'manager',
        'user'
    ];

    if (!in_array($role, $allowedRoles, true)) {
        $errors[] = 'Please select a valid administration role.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    $allowedStatuses = [
        'active',
        'inactive',
        'blocked'
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        $errors[] = 'Please select a valid account status.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Password
    |--------------------------------------------------------------------------
    */

    if ($password === '') {

        $errors[] = 'Password is required.';

    } elseif (strlen($password) < 8) {

        $errors[] = 'Password must be at least 8 characters long.';

    }

    if ($confirmPassword === '') {

        $errors[] = 'Please confirm the password.';

    } elseif ($password !== $confirmPassword) {

        $errors[] = 'The passwords do not match.';
    }


    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare("
            INSERT INTO users (
                first_name,
                last_name,
                email,
                phone,
                password,
                role,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssss",
            $firstName,
            $lastName,
            $email,
            $phone,
            $hashedPassword,
            $role,
            $status
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                'Location: ' .
                $baseUrl .
                '/admin/users/index.php?created=1'
            );

            exit;

        } else {

            $errors[] = 'Unable to create the administration user.';

            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container-fluid px-0">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="text-primary fw-semibold small mb-1">
                USER MANAGEMENT
            </div>

            <h1 class="fw-bold mb-1">
                Add Admin User
            </h1>

            <p class="text-muted mb-0">
                Create a new user with access to the Betachem administration system.
            </p>

        </div>

        <div>

            <a
                href="<?= $baseUrl; ?>/admin/users/index.php"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back to Admin Users
            </a>

        </div>

    </div>


    <!-- Errors -->
    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger border-0 shadow-sm">

            <div class="fw-semibold mb-2">

                <i class="bi bi-exclamation-triangle me-1"></i>

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


    <div class="row justify-content-center">

        <div class="col-xl-9 col-xxl-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4 p-lg-5">

                    <form
                        method="POST"
                        action="<?= $baseUrl; ?>/admin/users/add.php"
                    >

                        <!-- Personal Information -->
                        <div class="mb-4">

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-person text-primary me-1"></i>

                                Personal Information

                            </h5>

                            <p class="text-muted small mb-0">
                                Enter the staff member's basic information.
                            </p>

                        </div>


                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

                                <label
                                    for="first_name"
                                    class="form-label fw-semibold"
                                >
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    value="<?= e($firstName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="last_name"
                                    class="form-label fw-semibold"
                                >
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    value="<?= e($lastName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

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
                                    value="<?= e($email) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="phone"
                                    class="form-label fw-semibold"
                                >
                                    Phone Number
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?= e($phone) ?>"
                                >

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- Access -->
                        <div class="mb-4">

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-shield-lock text-primary me-1"></i>

                                Administration Access

                            </h5>

                            <p class="text-muted small mb-0">
                                Choose what level of access this staff member should have.
                            </p>

                        </div>


                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

                                <label
                                    for="role"
                                    class="form-label fw-semibold"
                                >
                                    Role
                                </label>

                                <select
                                    class="form-select"
                                    id="role"
                                    name="role"
                                    required
                                >

                                    <option
                                        value="user"
                                        <?= $role === 'user' ? 'selected' : '' ?>
                                    >
                                        User
                                    </option>

                                    <option
                                        value="manager"
                                        <?= $role === 'manager' ? 'selected' : '' ?>
                                    >
                                        Manager
                                    </option>

                                    <option
                                        value="administrator"
                                        <?= $role === 'administrator' ? 'selected' : '' ?>
                                    >
                                        Administrator
                                    </option>

                                </select>

                                <div class="form-text">
                                    Superusers cannot be created from this form.
                                </div>

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="status"
                                    class="form-label fw-semibold"
                                >
                                    Account Status
                                </label>

                                <select
                                    class="form-select"
                                    id="status"
                                    name="status"
                                    required
                                >

                                    <option
                                        value="active"
                                        <?= $status === 'active' ? 'selected' : '' ?>
                                    >
                                        Active
                                    </option>

                                    <option
                                        value="inactive"
                                        <?= $status === 'inactive' ? 'selected' : '' ?>
                                    >
                                        Inactive
                                    </option>

                                    <option
                                        value="blocked"
                                        <?= $status === 'blocked' ? 'selected' : '' ?>
                                    >
                                        Blocked
                                    </option>

                                </select>

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- Password -->
                        <div class="mb-4">

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-key text-primary me-1"></i>

                                Login Password

                            </h5>

                            <p class="text-muted small mb-0">
                                Create a secure password for the new administration account.
                            </p>

                        </div>


                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

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
                                    minlength="8"
                                    required
                                >

                                <div class="form-text">
                                    Minimum 8 characters.
                                </div>

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="confirm_password"
                                    class="form-label fw-semibold"
                                >
                                    Confirm Password
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="confirm_password"
                                    name="confirm_password"
                                    minlength="8"
                                    required
                                >

                            </div>

                        </div>


                        <!-- Role Information -->
                        <div class="alert alert-light border mb-4">

                            <div class="fw-semibold mb-2">

                                <i class="bi bi-info-circle text-primary me-1"></i>

                                Role Permissions

                            </div>

                            <div class="row g-3 small">

                                <div class="col-md-4">

                                    <strong>User</strong>

                                    <div class="text-muted mt-1">
                                        Basic day-to-day administration access.
                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <strong>Manager</strong>

                                    <div class="text-muted mt-1">
                                        Higher-level operational management access.
                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <strong>Administrator</strong>

                                    <div class="text-muted mt-1">
                                        Broad administration access.
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2">

                            <a
                                href="<?= $baseUrl; ?>/admin/users/index.php"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                            >

                                <i class="bi bi-person-plus me-1"></i>

                                Create Admin User

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
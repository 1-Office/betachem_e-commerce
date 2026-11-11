<?php

require_once __DIR__ . '/../includes/auth.php';

requireSuperuser();

$pageTitle = 'Edit Admin User';

$baseUrl = '/betachem/betachem_e-commerce';

$errors = [];

$userId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($userId <= 0) {
    header('Location: ' . $baseUrl . '/admin/users/index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Load User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        first_name,
        last_name,
        email,
        phone,
        role,
        status,
        created_at,
        updated_at
    FROM users
    WHERE id = ?
      AND role IN (
          'superuser',
          'administrator',
          'manager',
          'user'
      )
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    header('Location: ' . $baseUrl . '/admin/users/index.php');
    exit;
}


$isCurrentUser = (
    (int) $user['id'] === (int) $_SESSION['user_id']
);


/*
|--------------------------------------------------------------------------
| Form Values
|--------------------------------------------------------------------------
*/

$firstName = $user['first_name'];
$lastName  = $user['last_name'];
$email     = $user['email'];
$phone     = $user['phone'];
$role      = $user['role'];
$status    = $user['status'];


/*
|--------------------------------------------------------------------------
| Handle Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    $newRole = $_POST['role'] ?? $role;
    $newStatus = $_POST['status'] ?? $status;

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }

    if ($email === '') {

        $errors[] = 'Email address is required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Please enter a valid email address.';

    } else {

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
              AND id != ?
            LIMIT 1
        ");

        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();

        $emailResult = $stmt->get_result();

        if ($emailResult->num_rows > 0) {
            $errors[] = 'Another account already uses this email address.';
        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Role Validation
    |--------------------------------------------------------------------------
    */

    $allowedRoles = [
        'superuser',
        'administrator',
        'manager',
        'user'
    ];

    if (!in_array($newRole, $allowedRoles, true)) {
        $errors[] = 'Invalid administration role.';
    }


    /*
    |--------------------------------------------------------------------------
    | Status Validation
    |--------------------------------------------------------------------------
    */

    $allowedStatuses = [
        'active',
        'inactive',
        'blocked'
    ];

    if (!in_array($newStatus, $allowedStatuses, true)) {
        $errors[] = 'Invalid account status.';
    }


    /*
    |--------------------------------------------------------------------------
    | Protect Current Superuser
    |--------------------------------------------------------------------------
    */

    if ($isCurrentUser && $user['role'] === 'superuser') {

        $newRole = 'superuser';
        $newStatus = 'active';
    }


    /*
    |--------------------------------------------------------------------------
    | Make Sure At Least One Superuser Remains
    |--------------------------------------------------------------------------
    */

    if (
        $user['role'] === 'superuser' &&
        (
            $newRole !== 'superuser' ||
            $newStatus !== 'active'
        )
    ) {

        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM users
            WHERE role = 'superuser'
              AND status = 'active'
              AND id != ?
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $countResult = $stmt->get_result();
        $countData = $countResult->fetch_assoc();

        $stmt->close();

        if ((int) $countData['total'] < 1) {
            $errors[] = 'The system must always have at least one active Superuser.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Password Change
    |--------------------------------------------------------------------------
    */

    if ($password !== '') {

        if (strlen($password) < 8) {
            $errors[] = 'New password must contain at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'The new passwords do not match.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Database
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        if ($password !== '') {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                UPDATE users
                SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    password = ?,
                    role = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sssssssi",
                $firstName,
                $lastName,
                $email,
                $phone,
                $hashedPassword,
                $newRole,
                $newStatus,
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
                    role = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssssssi",
                $firstName,
                $lastName,
                $email,
                $phone,
                $newRole,
                $newStatus,
                $userId
            );
        }


        if ($stmt->execute()) {

            $stmt->close();

            if ($isCurrentUser) {

                $_SESSION['user_name'] =
                    trim($firstName . ' ' . $lastName);

                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $newRole;
            }

            header(
                'Location: ' .
                $baseUrl .
                '/admin/users/index.php?updated=1'
            );

            exit;

        } else {

            $errors[] = 'Unable to update the user.';

            $stmt->close();
        }
    }
}


require_once __DIR__ . '/../includes/header.php';

?>


<div class="container-fluid px-0">

    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="text-primary fw-semibold small mb-1">
                USER MANAGEMENT
            </div>

            <h1 class="fw-bold mb-1">
                Edit User
            </h1>

            <p class="text-muted mb-0">
                Manage the existing administration account.
            </p>

        </div>

        <a
            href="<?= $baseUrl; ?>/admin/users/index.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back to Users
        </a>

    </div>


    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger shadow-sm border-0">

            <div class="fw-semibold mb-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Update could not be completed
            </div>

            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li><?= e($error) ?></li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <div class="row g-4">

        <!-- LEFT -->

        <div class="col-xl-8">

            <!-- Account Details -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>
                        Account Details
                    </div>

                    <div class="small text-muted">
                        Update the user's personal and contact information.
                    </div>

                </div>

                <div class="card-body p-4">

                    <form
                        method="POST"
                        action="<?= $baseUrl; ?>/admin/users/edit.php?id=<?= (int) $userId ?>"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $userId ?>"
                        >


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    value="<?= e($firstName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    value="<?= e($lastName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= e($email) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Phone Number
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="<?= e($phone) ?>"
                                >

                            </div>

                        </div>

                </div>

            </div>


            <!-- Access Control -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">
                        <i class="bi bi-shield-lock text-primary me-2"></i>
                        Access Control
                    </div>

                    <div class="small text-muted">
                        Control what this account can access.
                    </div>

                </div>

                <div class="card-body p-4">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Administration Role
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                <?= $isCurrentUser ? 'disabled' : '' ?>
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

                                <option
                                    value="superuser"
                                    <?= $role === 'superuser' ? 'selected' : '' ?>
                                >
                                    Superuser
                                </option>

                            </select>

                            <?php if ($isCurrentUser): ?>

                                <input
                                    type="hidden"
                                    name="role"
                                    value="superuser"
                                >

                                <div class="small text-warning mt-2">
                                    <i class="bi bi-lock me-1"></i>
                                    Your own Superuser role is protected.
                                </div>

                            <?php endif; ?>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Account Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                                <?= $isCurrentUser ? 'disabled' : '' ?>
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

                            <?php if ($isCurrentUser): ?>

                                <input
                                    type="hidden"
                                    name="status"
                                    value="active"
                                >

                                <div class="small text-warning mt-2">
                                    <i class="bi bi-lock me-1"></i>
                                    Your own account must remain active.
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Password -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">
                        <i class="bi bi-key text-primary me-2"></i>
                        Password
                    </div>

                    <div class="small text-muted">
                        Only complete this section if you want to change the password.
                    </div>

                </div>

                <div class="card-body p-4">

                    <div class="alert alert-light border small">

                        <i class="bi bi-info-circle text-primary me-1"></i>

                        Leave both password fields empty to keep the current password.

                    </div>


                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="8"
                            >

                        </div>

                    </div>

                </div>

            </div>


            <!-- Save -->

            <div class="d-flex justify-content-end gap-2 mb-4">

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
                    <i class="bi bi-check-lg me-1"></i>
                    Save Changes
                </button>

            </div>


                    </form>

        </div>


        <!-- RIGHT -->

        <div class="col-xl-4">


            <!-- Current Account -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <div class="text-center">

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width:80px;height:80px;"
                        >
                            <i class="bi bi-person-fill fs-1"></i>
                        </div>


                        <h4 class="fw-bold mb-1">

                            <?= e(trim(
                                $user['first_name'] . ' ' . $user['last_name']
                            )) ?>

                        </h4>


                        <div class="text-muted small mb-3">

                            <?= e($user['email']) ?>

                        </div>


                        <?php

                        $roleLabels = [
                            'superuser' => 'Superuser',
                            'administrator' => 'Administrator',
                            'manager' => 'Manager',
                            'user' => 'User'
                        ];

                        $roleColors = [
                            'superuser' => 'danger',
                            'administrator' => 'primary',
                            'manager' => 'success',
                            'user' => 'secondary'
                        ];

                        ?>

                        <span
                            class="badge bg-<?= e($roleColors[$role] ?? 'secondary') ?> px-3 py-2"
                        >
                            <?= e($roleLabels[$role] ?? ucfirst($role)) ?>
                        </span>

                    </div>

                </div>

            </div>


            <!-- Account Information -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Account Information
                    </div>

                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between py-2 border-bottom">

                        <span class="text-muted">
                            User ID
                        </span>

                        <strong>
                            #<?= (int) $user['id'] ?>
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between py-2 border-bottom">

                        <span class="text-muted">
                            Created
                        </span>

                        <strong class="small">

                            <?= !empty($user['created_at'])
                                ? date('d M Y', strtotime($user['created_at']))
                                : '—' ?>

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between py-2">

                        <span class="text-muted">
                            Last Updated
                        </span>

                        <strong class="small">

                            <?= !empty($user['updated_at'])
                                ? date('d M Y', strtotime($user['updated_at']))
                                : '—' ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- Security Notice -->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-shield-check text-success me-2"></i>

                        Security

                    </h6>

                    <p class="small text-muted mb-0">

                        Passwords are securely hashed before being stored.
                        Existing passwords cannot be viewed by administrators.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
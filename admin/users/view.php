<?php

require_once __DIR__ . '/../includes/auth.php';

requireSuperuser();

$pageTitle = 'View Admin User';

$baseUrl = '/betachem/betachem_e-commerce';

$userId = (int) ($_GET['id'] ?? 0);

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


/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/

$fullName = trim(
    $user['first_name'] . ' ' . $user['last_name']
);

$isCurrentUser = (
    (int) $user['id'] === (int) $_SESSION['user_id']
);


$roleNames = [
    'superuser'     => 'Superuser',
    'administrator' => 'Administrator',
    'manager'       => 'Manager',
    'user'          => 'User'
];

$roleBadges = [
    'superuser'     => 'danger',
    'administrator' => 'primary',
    'manager'       => 'success',
    'user'          => 'secondary'
];

$statusBadges = [
    'active'   => 'success',
    'inactive' => 'warning',
    'blocked'  => 'danger'
];

$roleName = $roleNames[$user['role']] ?? ucfirst($user['role']);
$roleBadge = $roleBadges[$user['role']] ?? 'secondary';

$statusName = ucfirst($user['status']);
$statusBadge = $statusBadges[$user['status']] ?? 'secondary';


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
                Admin User Profile
            </h1>

            <p class="text-muted mb-0">
                View administration account information and access details.
            </p>

        </div>


        <div class="d-flex gap-2">

            <a
                href="<?= $baseUrl; ?>/admin/users/index.php"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            <a
                href="<?= $baseUrl; ?>/admin/users/edit.php?id=<?= (int) $user['id'] ?>"
                class="btn btn-primary"
            >
                <i class="bi bi-pencil me-1"></i>
                Edit User
            </a>

        </div>

    </div>


    <div class="row g-4">


        <!-- Main Profile -->

        <div class="col-xl-8">


            <!-- Profile Card -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4 p-lg-5">

                    <div class="d-flex align-items-center">


                        <div
                            class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-4"
                            style="width:90px;height:90px;"
                        >

                            <i class="bi bi-person-fill fs-1"></i>

                        </div>


                        <div>

                            <h2 class="fw-bold mb-1">

                                <?= e($fullName) ?>

                                <?php if ($isCurrentUser): ?>

                                    <span class="badge bg-info text-dark ms-2 align-middle">
                                        You
                                    </span>

                                <?php endif; ?>

                            </h2>


                            <div class="text-muted mb-3">

                                <?= e($user['email']) ?>

                            </div>


                            <div class="d-flex flex-wrap gap-2">

                                <span
                                    class="badge bg-<?= e($roleBadge) ?> px-3 py-2"
                                >
                                    <?= e($roleName) ?>
                                </span>


                                <span
                                    class="badge bg-<?= e($statusBadge) ?> px-3 py-2"
                                >
                                    <?= e($statusName) ?>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Personal Information -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">

                        <i class="bi bi-person-lines-fill text-primary me-2"></i>

                        Personal Information

                    </div>

                </div>


                <div class="card-body p-4">

                    <div class="row g-4">


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                First Name
                            </div>

                            <div class="fw-semibold">
                                <?= e($user['first_name']) ?>
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Last Name
                            </div>

                            <div class="fw-semibold">
                                <?= e($user['last_name']) ?>
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Email Address
                            </div>

                            <div class="fw-semibold">
                                <?= e($user['email']) ?>
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Phone Number
                            </div>

                            <div class="fw-semibold">

                                <?php if (!empty($user['phone'])): ?>

                                    <?= e($user['phone']) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        Not provided
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


            <!-- Access Information -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">

                        <i class="bi bi-shield-lock text-primary me-2"></i>

                        Administration Access

                    </div>

                </div>


                <div class="card-body p-4">


                    <div class="row g-4">


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Administration Role
                            </div>

                            <div>

                                <span
                                    class="badge bg-<?= e($roleBadge) ?> px-3 py-2"
                                >
                                    <?= e($roleName) ?>
                                </span>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Account Status
                            </div>

                            <div>

                                <span
                                    class="badge bg-<?= e($statusBadge) ?> px-3 py-2"
                                >
                                    <?= e($statusName) ?>
                                </span>

                            </div>

                        </div>


                    </div>


                    <hr class="my-4">


                    <div class="row g-4">


                        <div class="col-md-6">

                            <div class="d-flex">

                                <div class="text-primary me-3">
                                    <i class="bi bi-check-circle fs-4"></i>
                                </div>

                                <div>

                                    <div class="fw-semibold">
                                        Administration Access
                                    </div>

                                    <div class="small text-muted">

                                        This account is permitted to access
                                        the Betachem administration system.

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="d-flex">

                                <div class="text-primary me-3">
                                    <i class="bi bi-key fs-4"></i>
                                </div>

                                <div>

                                    <div class="fw-semibold">
                                        Password Protected
                                    </div>

                                    <div class="small text-muted">

                                        The account password is securely
                                        hashed and cannot be viewed.

                                    </div>

                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


        </div>


        <!-- Sidebar -->

        <div class="col-xl-4">


            <!-- Account Summary -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <div class="fw-bold">

                        <i class="bi bi-info-circle text-primary me-2"></i>

                        Account Summary

                    </div>

                </div>


                <div class="card-body">


                    <div class="d-flex justify-content-between py-3 border-bottom">

                        <span class="text-muted">
                            User ID
                        </span>

                        <strong>
                            #<?= (int) $user['id'] ?>
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between py-3 border-bottom">

                        <span class="text-muted">
                            Created
                        </span>

                        <strong class="small">

                            <?= !empty($user['created_at'])
                                ? date('d M Y, H:i', strtotime($user['created_at']))
                                : '—' ?>

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between py-3">

                        <span class="text-muted">
                            Last Updated
                        </span>

                        <strong class="small">

                            <?= !empty($user['updated_at'])
                                ? date('d M Y, H:i', strtotime($user['updated_at']))
                                : '—' ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- Role Description -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <div class="text-primary fs-3 mb-3">

                        <i class="bi bi-shield-check"></i>

                    </div>


                    <h5 class="fw-bold mb-2">
                        <?= e($roleName) ?>
                    </h5>


                    <?php if ($user['role'] === 'superuser'): ?>

                        <p class="text-muted small mb-0">

                            Full access to the administration system,
                            including administration user management.

                        </p>

                    <?php elseif ($user['role'] === 'administrator'): ?>

                        <p class="text-muted small mb-0">

                            Broad administration access for managing
                            the operational areas of the store.

                        </p>

                    <?php elseif ($user['role'] === 'manager'): ?>

                        <p class="text-muted small mb-0">

                            Management-level access for day-to-day
                            operational administration.

                        </p>

                    <?php else: ?>

                        <p class="text-muted small mb-0">

                            Basic administration access for
                            day-to-day staff operations.

                        </p>

                    <?php endif; ?>

                </div>

            </div>


            <!-- Security -->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-lock text-success me-2"></i>

                        Security

                    </h6>


                    <div class="small text-muted">

                        <div class="d-flex mb-2">

                            <i class="bi bi-check-circle-fill text-success me-2"></i>

                            Password securely hashed

                        </div>


                        <div class="d-flex mb-2">

                            <i class="bi bi-check-circle-fill text-success me-2"></i>

                            Role-based access control

                        </div>


                        <div class="d-flex">

                            <i class="bi bi-check-circle-fill text-success me-2"></i>

                            Account status protection

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
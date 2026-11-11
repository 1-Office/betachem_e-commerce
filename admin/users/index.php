<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Admin Users
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';

requireSuperuser();

require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = 'Admin Users';
$baseUrl = '/betachem/betachem_e-commerce';

/*
|--------------------------------------------------------------------------
| Fetch Admin Users
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
    WHERE role IN (
        'superuser',
        'administrator',
        'manager',
        'user'
    )
    ORDER BY
        CASE role
            WHEN 'superuser' THEN 1
            WHEN 'administrator' THEN 2
            WHEN 'manager' THEN 3
            WHEN 'user' THEN 4
            ELSE 5
        END,
        first_name ASC,
        last_name ASC
");

$stmt->execute();
$result = $stmt->get_result();
$adminUsers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/*
|--------------------------------------------------------------------------
| Role Configuration
|--------------------------------------------------------------------------
*/

$roleNames = [
    'superuser'     => 'Superuser',
    'administrator' => 'Administrator',
    'manager'       => 'Manager',
    'user'          => 'User',
];

$roleBadges = [
    'superuser'     => 'danger',
    'administrator' => 'primary',
    'manager'       => 'success',
    'user'          => 'secondary',
];

$statusBadges = [
    'active'   => 'success',
    'inactive' => 'warning',
    'blocked'  => 'danger',
];

$roleCounts = [
    'superuser'     => 0,
    'administrator' => 0,
    'manager'       => 0,
    'user'          => 0,
];

$activeCount = 0;
$inactiveCount = 0;
$blockedCount = 0;

foreach ($adminUsers as $adminUser) {
    $role = $adminUser['role'];
    $status = strtolower((string) ($adminUser['status'] ?? ''));

    if (isset($roleCounts[$role])) {
        $roleCounts[$role]++;
    }

    if ($status === 'active') {
        $activeCount++;
    } elseif ($status === 'inactive') {
        $inactiveCount++;
    } elseif ($status === 'blocked') {
        $blockedCount++;
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<style>
/* =========================================================
   BETACHEM ADMIN USERS — PAGE STYLING
========================================================= */

.admin-users-page {
    color: #26364b;
    font-family: 'Raleway', sans-serif;
}

.admin-users-page .page-heading {
    font-size: clamp(1.65rem, 2.5vw, 2.1rem);
    font-weight: 800;
    letter-spacing: -0.6px;
    color: #172b4d;
}

.admin-users-page .eyebrow {
    color: #1769aa;
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.admin-users-page .page-description {
    color: #718096;
    font-size: .93rem;
    line-height: 1.7;
}

.admin-users-page .users-hero {
    position: relative;
    overflow: hidden;
    padding: 28px;
    margin-bottom: 24px;
    border-radius: 18px;
    color: #fff;
    background: linear-gradient(120deg, #1745ca 0%, #1d88e9 100%);
    box-shadow: 0 10px 28px rgba(23, 69, 202, .13);
}

.admin-users-page .users-hero::after {
    content: "";
    position: absolute;
    width: 210px;
    height: 210px;
    right: -65px;
    top: -100px;
    border: 30px solid rgba(255, 255, 255, .08);
    border-radius: 50%;
    pointer-events: none;
}

.admin-users-page .users-hero h1 {
    position: relative;
    z-index: 1;
    font-weight: 800;
    margin-bottom: 8px;
}

.admin-users-page .users-hero p {
    position: relative;
    z-index: 1;
    margin: 0;
    color: rgba(255, 255, 255, .85);
    line-height: 1.7;
}

.admin-users-page .hero-label {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: rgba(255, 255, 255, .8);
    text-transform: uppercase;
}

.admin-users-page .hero-action {
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

.admin-users-page .hero-action .btn {
    padding: 11px 17px;
    border: 0;
    border-radius: 10px;
    background: #fff;
    color: #1745ca;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
}

.admin-users-page .hero-action .btn:hover {
    background: #eef5ff;
    color: #102b80;
}

.admin-users-page .summary-card {
    height: 100%;
    border: 1px solid #e9eef5;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(22, 45, 78, .035);
    transition: transform .2s ease, box-shadow .2s ease;
}

.admin-users-page .summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(22, 45, 78, .08);
}

.admin-users-page .summary-card .card-body {
    padding: 20px;
}

.admin-users-page .summary-label {
    color: #7a8798;
    font-size: .8rem;
    font-weight: 600;
}

.admin-users-page .summary-number {
    color: #172b4d;
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1.2;
    margin-top: 8px;
}

.admin-users-page .summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border-radius: 13px;
    font-size: 1.35rem;
}

.admin-users-page .icon-blue {
    color: #1769aa;
    background: #eaf4ff;
}

.admin-users-page .icon-purple {
    color: #5b46bd;
    background: #f0edff;
}

.admin-users-page .icon-green {
    color: #198754;
    background: #e8f7ef;
}

.admin-users-page .icon-orange {
    color: #b96a08;
    background: #fff3df;
}

.admin-users-page .section-card {
    overflow: hidden;
    border: 1px solid #e9eef5;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 18px rgba(22, 45, 78, .04);
}

.admin-users-page .section-heading {
    padding: 20px 23px;
    border-bottom: 1px solid #edf1f6;
}

.admin-users-page .section-heading h2 {
    color: #172b4d;
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0;
}

.admin-users-page .section-heading p {
    margin: 5px 0 0;
    color: #8190a3;
    font-size: .82rem;
}

.admin-users-page .table {
    min-width: 900px;
}

.admin-users-page .table thead th {
    padding: 15px 14px;
    border-bottom: 1px solid #e9eef5;
    background: #f7f9fc;
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .55px;
    text-transform: uppercase;
    white-space: nowrap;
}

.admin-users-page .table tbody td {
    padding: 16px 14px;
    border-color: #eff2f7;
    color: #40516a;
    font-size: .85rem;
    vertical-align: middle;
}

.admin-users-page .table tbody tr:last-child td {
    border-bottom: 0;
}

.admin-users-page .table tbody tr:hover td {
    background: #f9fbff;
}

.admin-users-page .user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border-radius: 13px;
    background: linear-gradient(135deg, #eaf4ff, #dceaff);
    color: #1769aa;
    font-size: 1.15rem;
}

.admin-users-page .user-name {
    color: #253852;
    font-weight: 750;
    line-height: 1.5;
}

.admin-users-page .user-id {
    color: #8a97a8;
    font-size: .75rem;
    margin-top: 2px;
}

.admin-users-page .email-text {
    overflow-wrap: anywhere;
}

.admin-users-page .status-pill,
.admin-users-page .role-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 7px;
    font-size: .73rem;
    font-weight: 750;
    white-space: nowrap;
}

.admin-users-page .role-superuser {
    color: #b42332;
    background: #fdebed;
}

.admin-users-page .role-administrator {
    color: #1757b8;
    background: #e8f1ff;
}

.admin-users-page .role-manager {
    color: #137547;
    background: #e6f6ed;
}

.admin-users-page .role-user {
    color: #596579;
    background: #eef1f5;
}

.admin-users-page .status-active {
    color: #137547;
    background: #e6f6ed;
}

.admin-users-page .status-inactive {
    color: #98620a;
    background: #fff3d9;
}

.admin-users-page .status-blocked {
    color: #b42332;
    background: #fdebed;
}

.admin-users-page .status-unknown {
    color: #596579;
    background: #eef1f5;
}

.admin-users-page .current-user {
    display: inline-block;
    margin-left: 5px;
    padding: 3px 6px;
    border-radius: 5px;
    background: #e5f3ff;
    color: #1769aa;
    font-size: .65rem;
    font-weight: 800;
    vertical-align: middle;
}

.admin-users-page .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    margin-left: 3px;
    border: 1px solid #e1e8f0;
    border-radius: 8px;
    background: #fff;
    color: #5f7088;
    text-decoration: none;
    transition: .2s ease;
}

.admin-users-page .action-view:hover {
    color: #1769aa;
    border-color: #1769aa;
    background: #edf6ff;
}

.admin-users-page .action-edit:hover {
    color: #1745ca;
    border-color: #1745ca;
    background: #eef1ff;
}

.admin-users-page .empty-state {
    padding: 55px 20px;
    text-align: center;
}

.admin-users-page .empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 75px;
    height: 75px;
    margin: 0 auto 18px;
    border-radius: 22px;
    background: #edf5ff;
    color: #1769aa;
    font-size: 2rem;
}

.admin-users-page .role-info {
    height: 100%;
    padding: 18px;
    border: 1px solid #e8edf4;
    border-radius: 12px;
    background: #fff;
}

.admin-users-page .role-info p {
    color: #718096;
    font-size: .82rem;
    line-height: 1.75;
    margin: 10px 0 0;
}

.admin-users-page .role-info h3 {
    color: #26364b;
    font-size: .9rem;
    font-weight: 800;
    margin: 0;
}

@media (max-width: 767.98px) {
    .admin-users-page .users-hero {
        padding: 22px;
    }

    .admin-users-page .hero-layout {
        align-items: flex-start !important;
        flex-direction: column;
        gap: 18px;
    }

    .admin-users-page .hero-action {
        width: 100%;
    }

    .admin-users-page .hero-action .btn {
        width: 100%;
    }

    .admin-users-page .summary-card .card-body {
        padding: 16px;
    }

    .admin-users-page .summary-number {
        font-size: 1.65rem;
    }

    .admin-users-page .section-heading {
        padding: 17px;
    }
}
</style>

<div class="container-fluid px-0 admin-users-page">

    <!-- PAGE HERO -->

    <div class="users-hero">
        <div class="d-flex justify-content-between align-items-center hero-layout gap-4">

            <div>
                <div class="hero-label mb-2">
                    <i class="bi bi-shield-check me-1"></i>
                    User Management
                </div>

                <h1 class="h2">Admin Users</h1>

                <p>
                    Manage administration accounts, review assigned roles,
                    and monitor account status.
                </p>
            </div>

            <div class="hero-action">
                <a
                    href="<?= $baseUrl ?>/admin/users/add.php"
                    class="btn"
                >
                    <i class="bi bi-person-plus me-2"></i>
                    Add Admin User
                </a>
            </div>

        </div>
    </div>

    <!-- SUMMARY CARDS -->

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="summary-label">Superusers</div>
                            <div class="summary-number">
                                <?= $roleCounts['superuser'] ?>
                            </div>
                        </div>
                        <div class="summary-icon icon-purple">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="summary-label">Administrators</div>
                            <div class="summary-number">
                                <?= $roleCounts['administrator'] ?>
                            </div>
                        </div>
                        <div class="summary-icon icon-blue">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="summary-label">Managers</div>
                            <div class="summary-number">
                                <?= $roleCounts['manager'] ?>
                            </div>
                        </div>
                        <div class="summary-icon icon-green">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="summary-label">Active Accounts</div>
                            <div class="summary-number">
                                <?= $activeCount ?>
                            </div>
                        </div>
                        <div class="summary-icon icon-orange">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- USERS TABLE -->

    <div class="section-card mb-4">

        <div class="section-heading d-flex justify-content-between align-items-center gap-3">
            <div>
                <h2>
                    <i class="bi bi-people me-2" style="color:#1769aa"></i>
                    Administration Accounts
                </h2>
                <p>
                    <?= count($adminUsers) ?>
                    account<?= count($adminUsers) === 1 ? '' : 's' ?>
                    found
                    <?php if ($inactiveCount > 0 || $blockedCount > 0): ?>
                        · <?= $inactiveCount ?> inactive
                        · <?= $blockedCount ?> blocked
                    <?php endif; ?>
                </p>
            </div>

            <span class="badge rounded-pill text-bg-light border px-3 py-2">
                <i class="bi bi-shield-check me-1"></i>
                Restricted Access
            </span>
        </div>

        <?php if (empty($adminUsers)): ?>

            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-people"></i>
                </div>

                <h3 class="h5 fw-bold">No admin users found</h3>

                <p class="text-muted mb-4">
                    Add your first administration user to get started.
                </p>

                <a
                    href="<?= $baseUrl ?>/admin/users/add.php"
                    class="btn btn-primary"
                >
                    <i class="bi bi-person-plus me-1"></i>
                    Add Admin User
                </a>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($adminUsers as $adminUser): ?>

                            <?php
                            $role = (string) $adminUser['role'];
                            $status = strtolower(
                                (string) ($adminUser['status'] ?? '')
                            );

                            $fullName = trim(
                                ($adminUser['first_name'] ?? '') . ' ' .
                                ($adminUser['last_name'] ?? '')
                            );

                            if ($fullName === '') {
                                $fullName = 'Unnamed User';
                            }

                            $roleClass = [
                                'superuser'     => 'role-superuser',
                                'administrator' => 'role-administrator',
                                'manager'       => 'role-manager',
                                'user'          => 'role-user',
                            ][$role] ?? 'role-user';

                            $statusClass = [
                                'active'   => 'status-active',
                                'inactive' => 'status-inactive',
                                'blocked'  => 'status-blocked',
                            ][$status] ?? 'status-unknown';

                            $isCurrentUser =
                                (int) $adminUser['id'] ===
                                (int) ($_SESSION['user_id'] ?? 0);

                            $createdAt = !empty($adminUser['created_at'])
                                ? strtotime($adminUser['created_at'])
                                : false;
                            ?>

                            <tr>
                                <!-- USER -->

                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <div>
                                            <div class="user-name">
                                                <?= e($fullName) ?>

                                                <?php if ($isCurrentUser): ?>
                                                    <span class="current-user">
                                                        YOU
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="user-id">
                                                ID #<?= (int) $adminUser['id'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- EMAIL -->

                                <td>
                                    <span class="email-text">
                                        <?= e($adminUser['email'] ?? '') ?>
                                    </span>
                                </td>

                                <!-- PHONE -->

                                <td>
                                    <?php if (!empty($adminUser['phone'])): ?>
                                        <?= e($adminUser['phone']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ROLE -->

                                <td>
                                    <span class="role-pill <?= e($roleClass) ?>">
                                        <i class="bi bi-shield-check"></i>
                                        <?= e($roleNames[$role] ?? ucfirst($role)) ?>
                                    </span>
                                </td>

                                <!-- STATUS -->

                                <td>
                                    <span class="status-pill <?= e($statusClass) ?>">
                                        <i class="bi bi-circle-fill" style="font-size:6px"></i>
                                        <?= e(ucfirst($status ?: 'unknown')) ?>
                                    </span>
                                </td>

                                <!-- CREATED -->

                                <td>
                                    <?php if ($createdAt !== false): ?>
                                        <span class="text-nowrap">
                                            <?= date('d M Y', $createdAt) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTIONS -->

                                <td class="text-end pe-4">
                                    <a
                                        href="<?= $baseUrl ?>/admin/users/view.php?id=<?= (int) $adminUser['id'] ?>"
                                        class="action-btn action-view"
                                        title="View user"
                                        aria-label="View user"
                                        <i class="bi bi-eye"></i>
                                        View
                                        
                                    </a>

                                    <a
                                        href="<?= $baseUrl ?>/admin/users/edit.php?id=<?= (int) $adminUser['id'] ?>"
                                        class="action-btn action-edit"
                                        title="Edit user"
                                        aria-label="Edit user"
                                    >
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>

    <!-- ROLE INFORMATION -->

    <div class="section-card">

        <div class="section-heading">
            <h2>
                <i class="bi bi-info-circle me-2" style="color:#1769aa"></i>
                Administration Roles
            </h2>
            <p>
                Access levels depend on the permissions assigned to each role.
            </p>
        </div>

        <div class="p-3 p-md-4">
            <div class="row g-3">

                <div class="col-md-6 col-xl-3">
                    <div class="role-info">
                        <span class="role-pill role-superuser mb-3">
                            <i class="bi bi-shield-lock"></i>
                            Superuser
                        </span>

                        <h3>System-level access</h3>

                        <p>
                            Full administration access, including management
                            of administration accounts and roles.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="role-info">
                        <span class="role-pill role-administrator mb-3">
                            <i class="bi bi-person-badge"></i>
                            Administrator
                        </span>

                        <h3>Store administration</h3>

                        <p>
                            Operational administration access, subject to
                            the permissions enforced by the application.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="role-info">
                        <span class="role-pill role-manager mb-3">
                            <i class="bi bi-person-workspace"></i>
                            Manager
                        </span>

                        <h3>Management access</h3>

                        <p>
                            Access to permitted operational areas for
                            managing store activities.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="role-info">
                        <span class="role-pill role-user mb-3">
                            <i class="bi bi-person"></i>
                            User
                        </span>

                        <h3>Staff access</h3>

                        <p>
                            Basic staff access, limited to the features
                            allowed by the application's authorization rules.
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
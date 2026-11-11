<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Customers
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

require_once __DIR__ . '/../../includes/functions.php';

$baseUrl = '/betachem/betachem_e-commerce';

$pageTitle = 'Customers';

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$allowedStatuses = ['active', 'inactive', 'blocked'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

/*
|--------------------------------------------------------------------------
| Fetch Customers
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.city,
        u.province,
        u.role,
        u.status,
        u.created_at,
        COUNT(DISTINCT o.id) AS order_count,
        COALESCE(
            SUM(
                CASE
                    WHEN o.payment_status != 'refunded'
                    THEN o.total
                    ELSE 0
                END
            ),
            0
        ) AS total_spent
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    WHERE u.role = 'customer'
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= "
        AND (
            u.first_name LIKE ?
            OR u.last_name LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params = [
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    ];

    $types .= 'ssss';
}

if ($status !== '') {
    $sql .= " AND u.status = ? ";
    $params[] = $status;
    $types .= 's';
}

$sql .= "
    GROUP BY
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.city,
        u.province,
        u.role,
        u.status,
        u.created_at
    ORDER BY u.created_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Unable to prepare the customer query.');
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();

/*
|--------------------------------------------------------------------------
| Customer Statistics
|--------------------------------------------------------------------------
*/

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_customers,
        COALESCE(SUM(status = 'active'), 0) AS active_customers,
        COALESCE(SUM(status = 'inactive'), 0) AS inactive_customers,
        COALESCE(SUM(status = 'blocked'), 0) AS blocked_customers
    FROM users
    WHERE role = 'customer'
");

$stats = $statsResult
    ? $statsResult->fetch_assoc()
    : [
        'total_customers' => 0,
        'active_customers' => 0,
        'inactive_customers' => 0,
        'blocked_customers' => 0
    ];

/*
|--------------------------------------------------------------------------
| Status Badge
|--------------------------------------------------------------------------
*/

function customerStatusBadge($status)
{
    $labels = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'blocked' => 'Blocked'
    ];

    $classes = [
        'active' => 'status-active',
        'inactive' => 'status-inactive',
        'blocked' => 'status-blocked'
    ];

    $label = $labels[$status] ?? ucfirst($status);
    $class = $classes[$status] ?? 'status-inactive';

    return '<span class="customer-status ' . $class . '">'
        . e($label)
        . '</span>';
}

/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>

<style>
/* Betachem Customers — page-specific styling */

.betachem-customers {
    --bc-blue: #1D88E9;
    --bc-dark-blue: #1745CA;
    --bc-purple: #2D208A;
    --bc-border: #e7edf5;
    color: #243247;
}

.betachem-customers .customer-hero {
    position: relative;
    overflow: hidden;
    padding: 28px 30px;
    border-radius: 18px;
    color: #fff;
    background: linear-gradient(
        115deg,
        var(--bc-purple),
        var(--bc-dark-blue),
        var(--bc-blue)
    );
    box-shadow: 0 8px 24px rgba(23, 69, 202, .15);
}

.betachem-customers .customer-hero h1 {
    font-size: 1.8rem;
    font-weight: 750;
    letter-spacing: -.5px;
}

.betachem-customers .customer-hero p {
    color: rgba(255,255,255,.82);
}

.betachem-customers .hero-icon {
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    background: rgba(255,255,255,.15);
    font-size: 30px;
}

.betachem-customers .bc-card {
    height: 100%;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(25, 45, 75, .045);
    transition: transform .2s ease, box-shadow .2s ease;
}

.betachem-customers .bc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(25, 45, 75, .075);
}

.betachem-customers .stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 22px;
}

.betachem-customers .stat-label {
    color: #718096;
    font-size: .87rem;
    font-weight: 600;
}

.betachem-customers .stat-number {
    color: #1f2d40;
    font-size: 1.8rem;
    font-weight: 750;
}

.betachem-customers .filter-card,
.betachem-customers .table-card {
    overflow: hidden;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(25, 45, 75, .045);
}

.betachem-customers .section-heading {
    color: #1f2d40;
    font-weight: 750;
}

.betachem-customers .form-label {
    color: #34445b;
    font-size: .88rem;
    font-weight: 650;
}

.betachem-customers .form-control,
.betachem-customers .form-select {
    min-height: 44px;
    border: 1px solid #dfe6ef;
    border-radius: 9px;
    box-shadow: none;
}

.betachem-customers .form-control:focus,
.betachem-customers .form-select:focus {
    border-color: var(--bc-blue);
    box-shadow: 0 0 0 3px rgba(29, 136, 233, .12);
}

.betachem-customers .btn-primary {
    border-color: var(--bc-dark-blue);
    background: var(--bc-dark-blue);
}

.betachem-customers .btn-primary:hover {
    border-color: var(--bc-purple);
    background: var(--bc-purple);
}

.betachem-customers .table {
    --bs-table-hover-bg: #f7faff;
}

.betachem-customers .table thead th {
    padding-top: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5ebf3;
    background: #f6f8fc;
    color: #68768a;
    font-size: .76rem;
    font-weight: 750;
    letter-spacing: .45px;
    text-transform: uppercase;
    white-space: nowrap;
}

.betachem-customers .table tbody td {
    padding-top: 17px;
    padding-bottom: 17px;
    border-color: #edf1f6;
    font-size: .88rem;
    vertical-align: middle;
}

.betachem-customers .customer-avatar {
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    background: linear-gradient(135deg, #e7f2ff, #e9e7ff);
    color: var(--bc-dark-blue);
    font-size: 19px;
}

.betachem-customers .customer-name {
    color: #243247;
    font-weight: 700;
}

.betachem-customers .customer-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: .76rem;
    font-weight: 700;
    white-space: nowrap;
}

.betachem-customers .customer-status::before {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    content: "";
}

.betachem-customers .status-active {
    background: #e6f7ee;
    color: #16804a;
}

.betachem-customers .status-inactive {
    background: #edf0f5;
    color: #667085;
}

.betachem-customers .status-blocked {
    background: #fdebec;
    color: #c93643;
}

.betachem-customers .customer-count {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 8px;
    background: #edf4ff;
    color: var(--bc-dark-blue);
    font-weight: 700;
}

.betachem-customers .view-button {
    border-radius: 8px;
    font-weight: 650;
    white-space: nowrap;
}

.betachem-customers .empty-state {
    padding: 55px 20px;
}

.betachem-customers .empty-state-icon {
    color: #a8b9d0;
    font-size: 46px;
}

@media (max-width: 767.98px) {
    .betachem-customers .customer-hero {
        padding: 23px 20px;
    }

    .betachem-customers .customer-hero h1 {
        font-size: 1.5rem;
    }

    .betachem-customers .hero-icon {
        display: none;
    }

    .betachem-customers .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<div class="container-fluid py-4 betachem-customers">

    <!-- PAGE HEADER -->

    <div class="customer-hero mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <div class="small fw-semibold mb-2">
                    BETACHEM E-COMMERCE / CUSTOMER MANAGEMENT
                </div>

                <h1 class="mb-2">
                    <i class="bi bi-people me-2"></i>Customers
                </h1>

                <p class="mb-0">
                    Manage registered customers, account statuses and order activity.
                </p>
            </div>

            <div class="hero-icon">
                <i class="bi bi-person-lines-fill"></i>
            </div>
        </div>
    </div>

    <!-- STATISTICS -->

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Total Customers</div>
                        <div class="stat-number">
                            <?= (int) ($stats['total_customers'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="stat-icon" style="background:#e7f1ff;color:#1745CA;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Active</div>
                        <div class="stat-number">
                            <?= (int) ($stats['active_customers'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="stat-icon" style="background:#e5f7ed;color:#16804a;">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Inactive</div>
                        <div class="stat-number">
                            <?= (int) ($stats['inactive_customers'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="stat-icon" style="background:#edf0f5;color:#667085;">
                        <i class="bi bi-person-dash"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Blocked</div>
                        <div class="stat-number">
                            <?= (int) ($stats['blocked_customers'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="stat-icon" style="background:#fdebec;color:#c93643;">
                        <i class="bi bi-person-x"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SEARCH AND FILTER -->

    <div class="filter-card mb-4">
        <div class="p-3 p-lg-4">

            <div class="section-heading mb-3">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Search and Filter Customers
            </div>

            <form method="GET" action="<?= $baseUrl ?>/admin/customers/index.php">
                <div class="row g-3 align-items-end">

                    <div class="col-lg-7">
                        <label for="customer-search" class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            id="customer-search"
                            name="search"
                            class="form-control"
                            placeholder="Name, email or phone number"
                            value="<?= e($search) ?>"
                        >
                    </div>

                    <div class="col-lg-3">
                        <label for="customer-status" class="form-label">
                            Account Status
                        </label>

                        <select
                            id="customer-status"
                            name="status"
                            class="form-select"
                        >
                            <option value="">All statuses</option>

                            <?php foreach ($allowedStatuses as $customerStatus): ?>
                                <option
                                    value="<?= e($customerStatus) ?>"
                                    <?= $status === $customerStatus ? 'selected' : '' ?>
                                >
                                    <?= e(ucfirst($customerStatus)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>

                            <a
                                href="<?= $baseUrl ?>/admin/customers/index.php"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Clear
                            </a>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <!-- CUSTOMER TABLE -->

    <div class="table-card">

        <div class="p-3 p-lg-4 border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="section-heading mb-1">Customer Accounts</h5>
                    <div class="text-muted small">
                        Customer contact information and purchasing activity
                    </div>
                </div>

                <span class="customer-count">
                    <?= count($customers) ?>
                    <?= count($customers) === 1 ? 'customer' : 'customers' ?>
                </span>
            </div>
        </div>

        <?php if (empty($customers)): ?>

            <div class="empty-state text-center">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-person-x"></i>
                </div>

                <h5 class="fw-bold">No Customers Found</h5>

                <p class="text-muted mb-3">
                    No customer accounts match your current filters.
                </p>

                <?php if ($search !== '' || $status !== ''): ?>
                    <a
                        href="<?= $baseUrl ?>/admin/customers/index.php"
                        class="btn btn-outline-primary"
                    >
                        Clear Filters
                    </a>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th class="ps-4">Customer</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($customers as $customer): ?>

                            <?php
                            $fullName = trim(
                                ($customer['first_name'] ?? '') . ' ' .
                                ($customer['last_name'] ?? '')
                            );

                            if ($fullName === '') {
                                $fullName = 'Unnamed Customer';
                            }

                            $joinedTimestamp = !empty($customer['created_at'])
                                ? strtotime($customer['created_at'])
                                : false;
                            ?>

                            <tr>

                                <!-- CUSTOMER -->

                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="customer-avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <div>
                                            <div class="customer-name">
                                                <?= e($fullName) ?>
                                            </div>

                                            <small class="text-muted">
                                                Customer #<?= (int) $customer['id'] ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- CONTACT -->

                                <td>
                                    <div class="fw-semibold">
                                        <?= e($customer['email'] ?? '') ?>
                                    </div>

                                    <?php if (!empty($customer['phone'])): ?>
                                        <small class="text-muted">
                                            <?= e($customer['phone']) ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">No phone provided</small>
                                    <?php endif; ?>
                                </td>

                                <!-- LOCATION -->

                                <td>
                                    <?php if (!empty($customer['city'])): ?>
                                        <div><?= e($customer['city']) ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($customer['province'])): ?>
                                        <small class="text-muted">
                                            <?= e($customer['province']) ?>
                                        </small>
                                    <?php endif; ?>

                                    <?php if (
                                        empty($customer['city']) &&
                                        empty($customer['province'])
                                    ): ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ORDERS -->

                                <td>
                                    <span class="fw-bold">
                                        <?= (int) $customer['order_count'] ?>
                                    </span>
                                </td>

                                <!-- TOTAL SPENT -->

                                <td>
                                    <span class="fw-bold" style="color:#1745CA;">
                                        <?= money((float) $customer['total_spent']) ?>
                                    </span>
                                </td>

                                <!-- STATUS -->

                                <td>
                                    <?= customerStatusBadge($customer['status'] ?? '') ?>
                                </td>

                                <!-- JOINED -->

                                <td>
                                    <?php if ($joinedTimestamp !== false): ?>
                                        <div class="fw-semibold">
                                            <?= e(date('d M Y', $joinedTimestamp)) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= e(date('H:i', $joinedTimestamp)) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTION -->

                                <td class="text-end pe-4">
                                    <a
                                        href="<?= $baseUrl ?>/admin/customers/view.php?id=<?= (int) $customer['id'] ?>"
                                        class="btn btn-sm btn-outline-primary view-button"
                                    >
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
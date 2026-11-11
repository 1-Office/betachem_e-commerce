<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Orders
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

require_once __DIR__ . '/../../includes/functions.php';

$baseUrl = '/betachem/betachem_e-commerce';
$pageTitle = 'Orders';

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';

$allowedStatuses = [
    'pending',
    'processing',
    'ready',
    'shipped',
    'completed',
    'cancelled'
];

$allowedPaymentStatuses = [
    'pending',
    'paid',
    'failed',
    'refunded',
    'partial'
];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

if (!in_array($paymentStatus, $allowedPaymentStatuses, true)) {
    $paymentStatus = '';
}

/*
|--------------------------------------------------------------------------
| Fetch Orders
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        o.id,
        o.order_number,
        o.status,
        o.subtotal,
        o.delivery_fee,
        o.discount,
        o.total,
        o.payment_status,
        o.payment_method,
        o.shipping_first_name,
        o.shipping_last_name,
        o.shipping_email,
        o.created_at,
        u.first_name AS customer_first_name,
        u.last_name AS customer_last_name,
        u.email AS customer_email
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= "
        AND (
            o.order_number LIKE ?
            OR o.shipping_first_name LIKE ?
            OR o.shipping_last_name LIKE ?
            OR o.shipping_email LIKE ?
            OR u.first_name LIKE ?
            OR u.last_name LIKE ?
            OR u.email LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    for ($i = 0; $i < 7; $i++) {
        $params[] = $searchValue;
        $types .= 's';
    }
}

if ($status !== '') {
    $sql .= " AND o.status = ? ";
    $params[] = $status;
    $types .= 's';
}

if ($paymentStatus !== '') {
    $sql .= " AND o.payment_status = ? ";
    $params[] = $paymentStatus;
    $types .= 's';
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Unable to prepare the orders query.');
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();

/*
|--------------------------------------------------------------------------
| Order Statistics
|--------------------------------------------------------------------------
*/

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(status = 'pending'), 0) AS pending_orders,
        COALESCE(SUM(status = 'processing'), 0) AS processing_orders,
        COALESCE(SUM(status = 'completed'), 0) AS completed_orders
    FROM orders
");

$stats = $statsResult
    ? $statsResult->fetch_assoc()
    : [
        'total_orders' => 0,
        'pending_orders' => 0,
        'processing_orders' => 0,
        'completed_orders' => 0
    ];

/*
|--------------------------------------------------------------------------
| Order Status Badge
|--------------------------------------------------------------------------
*/

function orderStatusBadge($status)
{
    $labels = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'ready' => 'Ready',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    $classes = [
        'pending' => 'order-pending',
        'processing' => 'order-processing',
        'ready' => 'order-ready',
        'shipped' => 'order-shipped',
        'completed' => 'order-completed',
        'cancelled' => 'order-cancelled'
    ];

    $label = $labels[$status] ?? ucfirst($status);
    $class = $classes[$status] ?? 'order-pending';

    return '<span class="order-status ' . $class . '">'
        . e($label)
        . '</span>';
}

/*
|--------------------------------------------------------------------------
| Payment Status Badge
|--------------------------------------------------------------------------
*/

function paymentStatusBadge($status)
{
    $labels = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'partial' => 'Partial'
    ];

    $classes = [
        'pending' => 'payment-pending',
        'paid' => 'payment-paid',
        'failed' => 'payment-failed',
        'refunded' => 'payment-refunded',
        'partial' => 'payment-partial'
    ];

    $label = $labels[$status] ?? ucfirst($status);
    $class = $classes[$status] ?? 'payment-refunded';

    return '<span class="payment-status ' . $class . '">'
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
/* Betachem Orders — page-specific styling */

.betachem-orders {
    --bc-blue: #1D88E9;
    --bc-dark-blue: #1745CA;
    --bc-purple: #2D208A;
    --bc-border: #e7edf5;
    color: #243247;
}

.betachem-orders .orders-hero {
    padding: 28px 30px;
    overflow: hidden;
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

.betachem-orders .orders-hero h1 {
    font-size: 1.8rem;
    font-weight: 750;
    letter-spacing: -.5px;
}

.betachem-orders .orders-hero p {
    color: rgba(255,255,255,.82);
}

.betachem-orders .hero-icon {
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    background: rgba(255,255,255,.15);
    font-size: 30px;
}

.betachem-orders .bc-card {
    height: 100%;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(25, 45, 75, .045);
    transition: transform .2s ease, box-shadow .2s ease;
}

.betachem-orders .bc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(25, 45, 75, .075);
}

.betachem-orders .stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 22px;
}

.betachem-orders .stat-label {
    color: #718096;
    font-size: .87rem;
    font-weight: 600;
}

.betachem-orders .stat-number {
    color: #1f2d40;
    font-size: 1.8rem;
    font-weight: 750;
}

.betachem-orders .filter-card,
.betachem-orders .table-card {
    overflow: hidden;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(25, 45, 75, .045);
}

.betachem-orders .section-heading {
    color: #1f2d40;
    font-weight: 750;
}

.betachem-orders .form-label {
    color: #34445b;
    font-size: .88rem;
    font-weight: 650;
}

.betachem-orders .form-control,
.betachem-orders .form-select {
    min-height: 44px;
    border: 1px solid #dfe6ef;
    border-radius: 9px;
    box-shadow: none;
}

.betachem-orders .form-control:focus,
.betachem-orders .form-select:focus {
    border-color: var(--bc-blue);
    box-shadow: 0 0 0 3px rgba(29, 136, 233, .12);
}

.betachem-orders .btn-primary {
    border-color: var(--bc-dark-blue);
    background: var(--bc-dark-blue);
}

.betachem-orders .btn-primary:hover {
    border-color: var(--bc-purple);
    background: var(--bc-purple);
}

.betachem-orders .table {
    --bs-table-hover-bg: #f7faff;
}

.betachem-orders .table thead th {
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

.betachem-orders .table tbody td {
    padding-top: 17px;
    padding-bottom: 17px;
    border-color: #edf1f6;
    font-size: .88rem;
    vertical-align: middle;
}

.betachem-orders .order-number {
    color: var(--bc-dark-blue);
    font-weight: 750;
    white-space: nowrap;
}

.betachem-orders .order-status,
.betachem-orders .payment-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
}

.betachem-orders .order-status::before,
.betachem-orders .payment-status::before {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    content: "";
}

.betachem-orders .order-pending,
.betachem-orders .payment-pending {
    background: #fff3d6;
    color: #956000;
}

.betachem-orders .order-processing {
    background: #e3f3ff;
    color: #0877b9;
}

.betachem-orders .order-ready {
    background: #e9e7ff;
    color: #5141b7;
}

.betachem-orders .order-shipped,
.betachem-orders .payment-partial {
    background: #e5f4ff;
    color: #1769aa;
}

.betachem-orders .order-completed,
.betachem-orders .payment-paid {
    background: #e5f7ed;
    color: #16804a;
}

.betachem-orders .order-cancelled,
.betachem-orders .payment-failed {
    background: #fdebec;
    color: #c93643;
}

.betachem-orders .payment-refunded {
    background: #edf0f5;
    color: #667085;
}

.betachem-orders .order-count {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 8px;
    background: #edf4ff;
    color: var(--bc-dark-blue);
    font-weight: 700;
}

.betachem-orders .view-button {
    border-radius: 8px;
    font-weight: 650;
    white-space: nowrap;
}

.betachem-orders .empty-state {
    padding: 55px 20px;
}

.betachem-orders .empty-state-icon {
    color: #a8b9d0;
    font-size: 46px;
}

@media (max-width: 767.98px) {
    .betachem-orders .orders-hero {
        padding: 23px 20px;
    }

    .betachem-orders .orders-hero h1 {
        font-size: 1.5rem;
    }

    .betachem-orders .hero-icon {
        display: none;
    }

    .betachem-orders .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<div class="container-fluid py-4 betachem-orders">

    <!-- PAGE HEADER -->

    <div class="orders-hero mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <div class="small fw-semibold mb-2">
                    BETACHEM E-COMMERCE / ORDER MANAGEMENT
                </div>

                <h1 class="mb-2">
                    <i class="bi bi-bag-check me-2"></i>Orders
                </h1>

                <p class="mb-0">
                    Manage customer orders, payment progress and fulfilment.
                </p>
            </div>

            <div class="hero-icon">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <!-- STATISTICS -->

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Total Orders</div>
                        <div class="stat-number">
                            <?= (int) ($stats['total_orders'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#e7f1ff;color:#1745CA;">
                        <i class="bi bi-bag"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Pending</div>
                        <div class="stat-number">
                            <?= (int) ($stats['pending_orders'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#fff3d6;color:#956000;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Processing</div>
                        <div class="stat-number">
                            <?= (int) ($stats['processing_orders'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#e3f3ff;color:#0877b9;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Completed</div>
                        <div class="stat-number">
                            <?= (int) ($stats['completed_orders'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#e5f7ed;color:#16804a;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SEARCH AND FILTERS -->

    <div class="filter-card mb-4">
        <div class="p-3 p-lg-4">

            <div class="section-heading mb-3">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Search and Filter Orders
            </div>

            <form method="GET" action="<?= $baseUrl ?>/admin/orders/index.php">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-5">
                        <label for="order-search" class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            id="order-search"
                            name="search"
                            class="form-control"
                            placeholder="Order number, customer name or email"
                            value="<?= e($search) ?>"
                        >
                    </div>

                    <div class="col-lg-3">
                        <label for="order-status" class="form-label">
                            Order Status
                        </label>

                        <select id="order-status" name="status" class="form-select">
                            <option value="">All statuses</option>

                            <?php foreach ($allowedStatuses as $itemStatus): ?>
                                <option
                                    value="<?= e($itemStatus) ?>"
                                    <?= $status === $itemStatus ? 'selected' : '' ?>
                                >
                                    <?= e(ucfirst($itemStatus)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <label for="payment-status" class="form-label">
                            Payment
                        </label>

                        <select
                            id="payment-status"
                            name="payment_status"
                            class="form-select"
                        >
                            <option value="">All payments</option>

                            <?php foreach ($allowedPaymentStatuses as $itemPaymentStatus): ?>
                                <option
                                    value="<?= e($itemPaymentStatus) ?>"
                                    <?= $paymentStatus === $itemPaymentStatus ? 'selected' : '' ?>
                                >
                                    <?= e(ucfirst($itemPaymentStatus)) ?>
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
                                href="<?= $baseUrl ?>/admin/orders/index.php"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Clear Filters
                            </a>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

    <!-- ORDERS TABLE -->

    <div class="table-card">

        <div class="p-3 p-lg-4 border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="section-heading mb-1">All Orders</h5>
                    <div class="text-muted small">
                        Review orders and open an order to manage its details.
                    </div>
                </div>

                <span class="order-count">
                    <?= count($orders) ?>
                    <?= count($orders) === 1 ? 'order' : 'orders' ?>
                </span>
            </div>
        </div>

        <?php if (empty($orders)): ?>

            <div class="empty-state text-center">
                <div class="empty-state-icon mb-3">
                    <i class="bi bi-bag-x"></i>
                </div>

                <h5 class="fw-bold">No Orders Found</h5>

                <p class="text-muted mb-3">
                    There are no orders matching your current filters.
                </p>

                <?php if ($search !== '' || $status !== '' || $paymentStatus !== ''): ?>
                    <a
                        href="<?= $baseUrl ?>/admin/orders/index.php"
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
                            <th class="ps-4">Order</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($orders as $order): ?>

                            <?php
                            $customerName = trim(
                                ($order['customer_first_name'] ?? '') . ' ' .
                                ($order['customer_last_name'] ?? '')
                            );

                            if ($customerName === '') {
                                $customerName = trim(
                                    ($order['shipping_first_name'] ?? '') . ' ' .
                                    ($order['shipping_last_name'] ?? '')
                                );
                            }

                            $customerEmail = $order['customer_email']
                                ?: ($order['shipping_email'] ?? '');

                            $createdTimestamp = !empty($order['created_at'])
                                ? strtotime($order['created_at'])
                                : false;
                            ?>

                            <tr>

                                <!-- ORDER -->

                                <td class="ps-4">
                                    <div class="order-number">
                                        <?= e($order['order_number']) ?>
                                    </div>

                                    <small class="text-muted">
                                        Order #<?= (int) $order['id'] ?>
                                    </small>
                                </td>

                                <!-- CUSTOMER -->

                                <td>
                                    <div class="fw-semibold">
                                        <?= e($customerName ?: 'Guest Customer') ?>
                                    </div>

                                    <?php if ($customerEmail !== ''): ?>
                                        <small class="text-muted">
                                            <?= e($customerEmail) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <!-- DATE -->

                                <td>
                                    <?php if ($createdTimestamp !== false): ?>
                                        <div class="fw-semibold">
                                            <?= e(date('d M Y', $createdTimestamp)) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= e(date('H:i', $createdTimestamp)) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </td>

                                <!-- TOTAL -->

                                <td>
                                    <span class="fw-bold">
                                        <?= money((float) $order['total']) ?>
                                    </span>
                                </td>

                                <!-- PAYMENT -->

                                <td>
                                    <?= paymentStatusBadge($order['payment_status'] ?? '') ?>

                                    <?php if (!empty($order['payment_method'])): ?>
                                        <div class="text-muted small mt-1">
                                            <?= e(strtoupper($order['payment_method'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- ORDER STATUS -->

                                <td>
                                    <?= orderStatusBadge($order['status'] ?? '') ?>
                                </td>

                                <!-- ACTION -->

                                <td class="text-end pe-4">
                                    <a
                                        href="<?= $baseUrl ?>/admin/orders/view.php?id=<?= (int) $order['id'] ?>"
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
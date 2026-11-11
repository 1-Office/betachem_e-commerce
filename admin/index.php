<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Admin Dashboard
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';

requireAdmin();

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Admin Dashboard';
$baseUrl = '/betachem/betachem_e-commerce';

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

function dashboardCount($conn, $sql)
{
    $result = $conn->query($sql);

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_row();

    return (int) ($row[0] ?? 0);
}

function dashboardMoney($conn, $sql)
{
    $result = $conn->query($sql);

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_row();

    return (float) ($row[0] ?? 0);
}

function dashboardRows($conn, $sql)
{
    $result = $conn->query($sql);

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Main Statistics
|--------------------------------------------------------------------------
*/

$totalProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM products"
);

$activeProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM products WHERE is_active = 1"
);

$totalCategories = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM categories"
);

$totalCustomers = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM users WHERE role = 'customer'"
);

$totalOrders = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM orders"
);

$pendingOrders = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM orders WHERE status = 'pending'"
);

$processingOrders = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM orders WHERE status = 'processing'"
);

$paidOrders = dashboardCount(
    $conn,
    "SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'"
);

$totalSales = dashboardMoney(
    $conn,
    "SELECT COALESCE(SUM(total), 0)
     FROM orders
     WHERE status != 'cancelled'
       AND payment_status != 'refunded'"
);

$lowStockProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*)
     FROM products
     WHERE is_active = 1
       AND stock_quantity > 0
       AND stock_quantity <= minimum_stock"
);

$outOfStockProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*)
     FROM products
     WHERE is_active = 1
       AND stock_quantity <= 0"
);

/*
|--------------------------------------------------------------------------
| Recent Orders
|--------------------------------------------------------------------------
*/

$recentOrders = dashboardRows(
    $conn,
    "SELECT
        o.id,
        o.order_number,
        o.status,
        o.payment_status,
        o.total,
        o.created_at,
        o.shipping_first_name,
        o.shipping_last_name,
        u.first_name AS customer_first_name,
        u.last_name AS customer_last_name,
        u.email AS customer_email
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 8"
);

/*
|--------------------------------------------------------------------------
| Best-Selling Products
|--------------------------------------------------------------------------
*/

$bestSellingProducts = dashboardRows(
    $conn,
    "SELECT
        oi.product_id,
        oi.product_name,
        SUM(oi.quantity) AS units_sold,
        SUM(oi.subtotal) AS revenue
     FROM order_items oi
     INNER JOIN orders o ON o.id = oi.order_id
     WHERE o.status != 'cancelled'
     GROUP BY oi.product_id, oi.product_name
     ORDER BY units_sold DESC
     LIMIT 5"
);

/*
|--------------------------------------------------------------------------
| Stock Alerts
|--------------------------------------------------------------------------
*/

$stockAlerts = dashboardRows(
    $conn,
    "SELECT id, name, sku, stock_quantity, minimum_stock
     FROM products
     WHERE is_active = 1
       AND stock_quantity <= minimum_stock
     ORDER BY stock_quantity ASC
     LIMIT 8"
);

/*
|--------------------------------------------------------------------------
| Contact Messages
|--------------------------------------------------------------------------
| If the contact_messages table is not installed yet, show zero.
|--------------------------------------------------------------------------
*/

$contactMessages = 0;

$tableCheck = $conn->query("SHOW TABLES LIKE 'contact_messages'");

if ($tableCheck && $tableCheck->num_rows > 0) {
    $columnsResult = $conn->query("SHOW COLUMNS FROM contact_messages");

    $messageColumns = [];

    if ($columnsResult) {
        while ($column = $columnsResult->fetch_assoc()) {
            $messageColumns[] = $column['Field'];
        }
    }

    if (in_array('status', $messageColumns, true)) {
        $contactMessages = dashboardCount(
            $conn,
            "SELECT COUNT(*) FROM contact_messages
             WHERE status = 'unread'"
        );
    } else {
        $contactMessages = dashboardCount(
            $conn,
            "SELECT COUNT(*) FROM contact_messages"
        );
    }
}

/*
|--------------------------------------------------------------------------
| Page Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<style>
/* =========================================================
   BETACHEM ADMIN DASHBOARD
   Shared styling inspired by Categories index.php
   ========================================================= */

.betachem-dashboard {
    --bc-blue: #1D88E9;
    --bc-dark-blue: #1745CA;
    --bc-navy: #2D208A;
    --bc-text: #183153;
    --bc-muted: #68778D;
    --bc-border: #E3EAF3;
    --bc-bg: #F5F8FC;

    color: var(--bc-text);
    padding: 8px 0 30px;
    font-family: Raleway, Arial, sans-serif;
}

.betachem-dashboard * {
    box-sizing: border-box;
}

.betachem-dashboard .dashboard-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        120deg,
        #2D208A 0%,
        #1745CA 52%,
        #1D88E9 100%
    );
    color: #fff;
    border-radius: 18px;
    padding: 30px;
    margin-bottom: 26px;
    box-shadow: 0 8px 24px rgba(23, 69, 202, .15);
}

.betachem-dashboard .dashboard-hero::after {
    content: "";
    position: absolute;
    width: 210px;
    height: 210px;
    right: -55px;
    top: -85px;
    border: 28px solid rgba(255, 255, 255, .09);
    transform: rotate(30deg);
    border-radius: 32px;
    pointer-events: none;
}

.betachem-dashboard .dashboard-hero-content {
    position: relative;
    z-index: 1;
}

.betachem-dashboard .dashboard-eyebrow {
    text-transform: uppercase;
    letter-spacing: 1.8px;
    font-size: 11px;
    font-weight: 800;
    opacity: .8;
    margin-bottom: 8px;
}

.betachem-dashboard .dashboard-hero h1 {
    color: #fff;
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 800;
    margin: 0 0 9px;
}

.betachem-dashboard .dashboard-hero p {
    color: rgba(255, 255, 255, .88);
    margin: 0;
    font-size: 14px;
    line-height: 1.7;
    max-width: 680px;
}

.betachem-dashboard .hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

.betachem-dashboard .hero-actions a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 10px 15px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: .2s ease;
}

.betachem-dashboard .hero-primary {
    background: #fff;
    color: var(--bc-dark-blue);
}

.betachem-dashboard .hero-primary:hover {
    background: #EDF5FF;
    color: var(--bc-navy);
    transform: translateY(-1px);
}

.betachem-dashboard .hero-secondary {
    color: #fff;
    border: 1px solid rgba(255, 255, 255, .48);
    background: rgba(255, 255, 255, .08);
}

.betachem-dashboard .hero-secondary:hover {
    background: rgba(255, 255, 255, .17);
    color: #fff;
}

.betachem-dashboard .section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin: 26px 0 15px;
}

.betachem-dashboard .section-heading h2 {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    color: var(--bc-text);
}

.betachem-dashboard .section-heading p {
    color: var(--bc-muted);
    font-size: 12px;
    margin: 5px 0 0;
}

.betachem-dashboard .section-heading > a {
    color: var(--bc-dark-blue);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.betachem-dashboard .section-heading > a:hover {
    color: var(--bc-blue);
    text-decoration: underline;
}

/* Statistics */

.betachem-dashboard .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.betachem-dashboard .stat-card {
    background: #fff;
    border: 1px solid var(--bc-border);
    border-radius: 14px;
    padding: 20px;
    min-width: 0;
    box-shadow: 0 3px 12px rgba(24, 49, 83, .035);
    transition: transform .2s ease, box-shadow .2s ease;
}

.betachem-dashboard .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(24, 49, 83, .08);
}

.betachem-dashboard .stat-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.betachem-dashboard .stat-label {
    color: var(--bc-muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.5;
}

.betachem-dashboard .stat-value {
    color: var(--bc-text);
    font-size: clamp(22px, 2.2vw, 29px);
    font-weight: 800;
    line-height: 1.25;
    margin-top: 13px;
    overflow-wrap: anywhere;
}

.betachem-dashboard .stat-description {
    color: var(--bc-muted);
    font-size: 11px;
    line-height: 1.5;
    margin-top: 8px;
}

.betachem-dashboard .stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border-radius: 12px;
    font-size: 19px;
}

.betachem-dashboard .icon-blue {
    background: #E8F3FF;
    color: #1769AA;
}

.betachem-dashboard .icon-purple {
    background: #F0ECFF;
    color: #5D42C3;
}

.betachem-dashboard .icon-green {
    background: #E7F8EF;
    color: #16834A;
}

.betachem-dashboard .icon-orange {
    background: #FFF1E3;
    color: #BB6412;
}

.betachem-dashboard .icon-red {
    background: #FFE9E9;
    color: #C03939;
}

.betachem-dashboard .icon-teal {
    background: #E1F8F6;
    color: #087F8C;
}

/* Quick actions */

.betachem-dashboard .quick-actions {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.betachem-dashboard .quick-action {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 17px;
    border: 1px solid var(--bc-border);
    background: #fff;
    border-radius: 13px;
    text-decoration: none;
    color: var(--bc-text);
    min-width: 0;
    transition: .2s ease;
}

.betachem-dashboard .quick-action:hover {
    border-color: #9BC8F5;
    background: #F8FBFF;
    transform: translateY(-2px);
    box-shadow: 0 5px 16px rgba(23, 69, 202, .06);
    color: var(--bc-dark-blue);
}

.betachem-dashboard .quick-action-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 11px;
    background: #EAF3FF;
    color: var(--bc-dark-blue);
    font-size: 19px;
}

.betachem-dashboard .quick-action-title {
    font-size: 13px;
    font-weight: 800;
    line-height: 1.4;
}

.betachem-dashboard .quick-action-description {
    color: var(--bc-muted);
    font-size: 11px;
    margin-top: 4px;
    line-height: 1.4;
}

.betachem-dashboard .quick-action-arrow {
    margin-left: auto;
    color: #8A9AB0;
    flex-shrink: 0;
}

/* Panels and tables */

.betachem-dashboard .dashboard-panel {
    background: #fff;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    overflow: hidden;
    min-width: 0;
    box-shadow: 0 3px 12px rgba(24, 49, 83, .035);
}

.betachem-dashboard .panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 19px 21px;
    border-bottom: 1px solid var(--bc-border);
}

.betachem-dashboard .panel-header h3 {
    font-size: 15px;
    font-weight: 800;
    color: var(--bc-text);
    margin: 0;
}

.betachem-dashboard .panel-header p {
    font-size: 11px;
    color: var(--bc-muted);
    margin: 5px 0 0;
}

.betachem-dashboard .panel-header a {
    font-size: 12px;
    font-weight: 700;
    color: var(--bc-dark-blue);
    text-decoration: none;
    white-space: nowrap;
}

.betachem-dashboard .table-responsive {
    width: 100%;
    overflow-x: auto;
}

.betachem-dashboard .dashboard-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 12px;
}

.betachem-dashboard .dashboard-table th {
    background: #F6F9FD;
    color: #63748B;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .65px;
    font-weight: 800;
    padding: 13px 16px;
    white-space: nowrap;
    border-bottom: 1px solid var(--bc-border);
    text-align: left;
}

.betachem-dashboard .dashboard-table td {
    padding: 14px 16px;
    color: var(--bc-text);
    vertical-align: middle;
    border-bottom: 1px solid #EDF1F6;
}

.betachem-dashboard .dashboard-table tbody tr:last-child td {
    border-bottom: 0;
}

.betachem-dashboard .dashboard-table tbody tr:hover {
    background: #FAFCFF;
}

.betachem-dashboard .table-main {
    font-weight: 800;
    font-size: 12px;
    color: var(--bc-text);
}

.betachem-dashboard .table-sub {
    color: var(--bc-muted);
    font-size: 11px;
    margin-top: 4px;
    line-height: 1.5;
}

.betachem-dashboard .status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 800;
    text-transform: capitalize;
    white-space: nowrap;
    background: #EEF2F7;
    color: #526176;
}

.betachem-dashboard .status-pending {
    background: #FFF2D8;
    color: #956000;
}

.betachem-dashboard .status-processing {
    background: #E6F2FF;
    color: #175EA8;
}

.betachem-dashboard .status-ready,
.betachem-dashboard .status-shipped {
    background: #EDE8FF;
    color: #6042B5;
}

.betachem-dashboard .status-completed,
.betachem-dashboard .status-paid,
.betachem-dashboard .status-active {
    background: #E4F7EC;
    color: #187744;
}

.betachem-dashboard .status-cancelled,
.betachem-dashboard .status-failed,
.betachem-dashboard .status-refunded {
    background: #FFE8E8;
    color: #B52F37;
}

.betachem-dashboard .status-partial {
    background: #FFF0E3;
    color: #A85D16;
}

.betachem-dashboard .btn-view {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 32px;
    padding: 7px 10px;
    border: 1px solid #C8DDF5;
    border-radius: 8px;
    background: #F4F9FF;
    color: #175EA8;
    text-decoration: none;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
    transition: .2s ease;
}

.betachem-dashboard .btn-view:hover {
    background: #175EA8;
    border-color: #175EA8;
    color: #fff;
}

.betachem-dashboard .panel-body {
    padding: 19px 21px;
}

.betachem-dashboard .dashboard-columns {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, 1fr);
    gap: 18px;
    align-items: start;
}

.betachem-dashboard .side-panels {
    display: grid;
    gap: 18px;
}

.betachem-dashboard .alert-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 0;
    border-bottom: 1px solid #EDF1F6;
}

.betachem-dashboard .alert-row:first-child {
    padding-top: 0;
}

.betachem-dashboard .alert-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.betachem-dashboard .alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 10px;
    background: #FFF0E7;
    color: #B85D16;
    font-size: 16px;
}

.betachem-dashboard .alert-icon.out-of-stock {
    background: #FFE8E8;
    color: #C03939;
}

.betachem-dashboard .alert-content {
    min-width: 0;
    flex: 1;
}

.betachem-dashboard .alert-name {
    font-size: 12px;
    font-weight: 800;
    color: var(--bc-text);
    overflow-wrap: anywhere;
}

.betachem-dashboard .alert-meta {
    color: var(--bc-muted);
    font-size: 11px;
    margin-top: 4px;
}

.betachem-dashboard .stock-number {
    flex-shrink: 0;
    text-align: right;
    font-size: 12px;
    font-weight: 800;
    color: #B85D16;
}

.betachem-dashboard .stock-number.out {
    color: #C03939;
}

.betachem-dashboard .empty-state {
    padding: 32px 20px;
    text-align: center;
    color: var(--bc-muted);
}

.betachem-dashboard .empty-state i {
    display: block;
    font-size: 28px;
    color: #A9B9CC;
    margin-bottom: 10px;
}

.betachem-dashboard .empty-state strong {
    display: block;
    color: var(--bc-text);
    font-size: 13px;
    margin-bottom: 5px;
}

.betachem-dashboard .empty-state span {
    font-size: 12px;
}

.betachem-dashboard .panel-footer-link {
    display: block;
    padding: 13px 20px;
    background: #FAFCFF;
    border-top: 1px solid var(--bc-border);
    color: var(--bc-dark-blue);
    font-size: 12px;
    font-weight: 800;
    text-align: center;
    text-decoration: none;
}

.betachem-dashboard .panel-footer-link:hover {
    background: #EDF5FF;
}

.betachem-dashboard .dashboard-bottom {
    margin-top: 18px;
}

/* Responsive */

@media (max-width: 1200px) {
    .betachem-dashboard .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .betachem-dashboard .quick-actions {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 900px) {
    .betachem-dashboard .dashboard-columns {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .betachem-dashboard .dashboard-hero {
        padding: 23px 19px;
        border-radius: 14px;
    }

    .betachem-dashboard .stats-grid,
    .betachem-dashboard .quick-actions {
        grid-template-columns: 1fr;
        gap: 11px;
    }

    .betachem-dashboard .stat-card {
        padding: 17px;
    }

    .betachem-dashboard .section-heading {
        align-items: flex-start;
    }

    .betachem-dashboard .panel-header,
    .betachem-dashboard .panel-body {
        padding: 16px;
    }

    .betachem-dashboard .dashboard-table th,
    .betachem-dashboard .dashboard-table td {
        padding: 12px;
    }

    .betachem-dashboard .hero-actions a {
        flex: 1 1 100%;
    }
}
</style>

<div class="container-fluid betachem-dashboard">

    <!-- Dashboard Hero -->
    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <div class="dashboard-eyebrow">
                Betachem E-Commerce Administration
            </div>

            <h1>
                <i class="bi bi-speedometer2 me-2"></i>
                Admin Dashboard
            </h1>

            <p>
                Monitor sales, manage products and categories, track customer
                orders, and keep your online store running smoothly.
            </p>

            <div class="hero-actions">
                <a class="hero-primary"
                   href="<?= $baseUrl ?>/admin/products/add.php">
                    <i class="bi bi-plus-circle"></i>
                    Add Product
                </a>

                <a class="hero-secondary"
                   href="<?= $baseUrl ?>/admin/orders/index.php">
                    <i class="bi bi-box-seam"></i>
                    Manage Orders
                </a>

                <a class="hero-secondary"
                   href="<?= $baseUrl ?>/shop.php"
                   target="_blank"
                   rel="noopener">
                    <i class="bi bi-shop"></i>
                    View Store
                </a>
            </div>
        </div>
    </section>

    <!-- Main Statistics -->
    <div class="section-heading">
        <div>
            <h2>Store Overview</h2>
            <p>Your store's key figures at a glance.</p>
        </div>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total Sales</div>
                <div class="stat-icon icon-green">
                    <i class="bi bi-currency-exchange"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= e(money($totalSales)) ?>
            </div>

            <div class="stat-description">
                Excludes cancelled and refunded orders
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Total Orders</div>
                <div class="stat-icon icon-blue">
                    <i class="bi bi-bag-check"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($totalOrders) ?>
            </div>

            <div class="stat-description">
                <?= number_format($pendingOrders) ?> pending
                · <?= number_format($processingOrders) ?> processing
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Products</div>
                <div class="stat-icon icon-purple">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($totalProducts) ?>
            </div>

            <div class="stat-description">
                <?= number_format($activeProducts) ?> active products
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Customers</div>
                <div class="stat-icon icon-teal">
                    <i class="bi bi-people"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($totalCustomers) ?>
            </div>

            <div class="stat-description">
                Registered customer accounts
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Categories</div>
                <div class="stat-icon icon-purple">
                    <i class="bi bi-grid"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($totalCategories) ?>
            </div>

            <div class="stat-description">
                Product categories in your store
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Paid Orders</div>
                <div class="stat-icon icon-green">
                    <i class="bi bi-credit-card"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($paidOrders) ?>
            </div>

            <div class="stat-description">
                Orders marked as paid
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Low Stock</div>
                <div class="stat-icon icon-orange">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($lowStockProducts) ?>
            </div>

            <div class="stat-description">
                Products at or below minimum stock
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-label">Out of Stock</div>
                <div class="stat-icon icon-red">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>

            <div class="stat-value">
                <?= number_format($outOfStockProducts) ?>
            </div>

            <div class="stat-description">
                Active products with no stock
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="section-heading">
        <div>
            <h2>Quick Actions</h2>
            <p>Go directly to the tasks you use most.</p>
        </div>
    </div>

    <div class="quick-actions">

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/products/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div>
                <div class="quick-action-title">Manage Products</div>
                <div class="quick-action-description">
                    Add, edit and update stock
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/categories/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-grid"></i>
            </div>
            <div>
                <div class="quick-action-title">Manage Categories</div>
                <div class="quick-action-description">
                    Organise store categories
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/orders/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="quick-action-title">Manage Orders</div>
                <div class="quick-action-description">
                    View orders and update statuses
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/customers/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <div class="quick-action-title">Manage Customers</div>
                <div class="quick-action-description">
                    View registered customers
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/contact-messages/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-envelope"></i>
            </div>
            <div>
                <div class="quick-action-title">Contact Messages</div>
                <div class="quick-action-description">
                    <?= number_format($contactMessages) ?> message(s) to review
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/orders/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="quick-action-title">Pending Orders</div>
                <div class="quick-action-description">
                    <?= number_format($pendingOrders) ?> awaiting processing
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/admin/products/index.php">
            <div class="quick-action-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <div>
                <div class="quick-action-title">Check Stock</div>
                <div class="quick-action-description">
                    Review low and out-of-stock items
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

        <a class="quick-action"
           href="<?= $baseUrl ?>/shop.php"
           target="_blank"
           rel="noopener">
            <div class="quick-action-icon">
                <i class="bi bi-box-arrow-up-right"></i>
            </div>
            <div>
                <div class="quick-action-title">Visit Online Store</div>
                <div class="quick-action-description">
                    Preview the customer experience
                </div>
            </div>
            <i class="bi bi-chevron-right quick-action-arrow"></i>
        </a>

    </div>

    <!-- Recent Orders and Stock Alerts -->
    <div class="section-heading">
        <div>
            <h2>Store Activity</h2>
            <p>Review recent customer orders and inventory alerts.</p>
        </div>
    </div>

    <div class="dashboard-columns">

        <!-- Recent Orders -->
        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3>
                        <i class="bi bi-receipt me-2"></i>
                        Recent Orders
                    </h3>
                    <p>Latest orders placed in your store.</p>
                </div>

                <a href="<?= $baseUrl ?>/admin/orders/index.php">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <?php if (!empty($recentOrders)): ?>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <?php
                            $orderId = (int) $order['id'];
                            $orderStatus = strtolower(
                                trim((string) ($order['status'] ?? 'pending'))
                            );

                            $customerName = trim(
    (string) ($order['customer_first_name'] ?? '') . ' ' .
    (string) ($order['customer_last_name'] ?? '')
);

if ($customerName === '') {
    $customerName = trim(
        (string) ($order['shipping_first_name'] ?? '') . ' ' .
        (string) ($order['shipping_last_name'] ?? '')
    );
}

if ($customerName === '') {
    $customerName = 'Customer';
}
                            ?>

                            <tr>
                                <td>
                                    <div class="table-main">
                                        <?= e($order['order_number'] ?? ('#' . $orderId)) ?>
                                    </div>
                                    <div class="table-sub">
                                        <?= !empty($order['created_at'])
                                            ? e(date('d M Y', strtotime($order['created_at'])))
                                            : '—' ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="table-main">
                                        <?= e($customerName) ?>
                                    </div>
                                    <div class="table-sub">
                                        <?= e($order['customer_email'] ?? '') ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="table-main">
                                        <?= e(money((float) ($order['total'] ?? 0))) ?>
                                    </div>
                                    <div class="table-sub">
                                        Payment:
                                        <?= e(ucfirst(
                                            (string) ($order['payment_status'] ?? 'pending')
                                        )) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-<?= e($orderStatus) ?>">
                                        <?= e(ucfirst($orderStatus)) ?>
                                    </span>
                                </td>

                                <td>
                                    <a class="btn-view"
                                       href="<?= $baseUrl ?>/admin/orders/view.php?id=<?= $orderId ?>"
                                       title="View Order"
                                       aria-label="View Order">
                                        <i class="bi bi-eye"></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <strong>No orders yet</strong>
                    <span>New customer orders will appear here.</span>
                </div>
            <?php endif; ?>

            <a class="panel-footer-link"
               href="<?= $baseUrl ?>/admin/orders/index.php">
                <i class="bi bi-list-ul me-1"></i>
                View All Orders
            </a>
        </section>

        <!-- Right Side Panels -->
        <div class="side-panels">

            <!-- Stock Alerts -->
            <section class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h3>
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Stock Alerts
                        </h3>
                        <p>Products that may need restocking.</p>
                    </div>

                    <a href="<?= $baseUrl ?>/admin/products/index.php">
                        Products <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="panel-body">
                    <?php if (!empty($stockAlerts)): ?>

                        <?php foreach ($stockAlerts as $product): ?>
                            <?php
                            $stockQuantity = (int) $product['stock_quantity'];
                            $isOutOfStock = $stockQuantity <= 0;
                            ?>

                            <div class="alert-row">
                                <div class="alert-icon <?= $isOutOfStock ? 'out-of-stock' : '' ?>">
                                    <i class="bi <?= $isOutOfStock
                                        ? 'bi-x-circle'
                                        : 'bi-exclamation-triangle' ?>"></i>
                                </div>

                                <div class="alert-content">
                                    <div class="alert-name">
                                        <?= e($product['name']) ?>
                                    </div>

                                    <div class="alert-meta">
                                        SKU: <?= e($product['sku'] ?? 'N/A') ?>
                                        · Minimum:
                                        <?= (int) ($product['minimum_stock'] ?? 0) ?>
                                    </div>
                                </div>

                                <div class="stock-number <?= $isOutOfStock ? 'out' : '' ?>">
                                    <?= $stockQuantity ?>
                                    <div class="table-sub">
                                        <?= $isOutOfStock ? 'Out of stock' : 'Remaining' ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <strong>Stock looks good</strong>
                            <span>No products are currently below their minimum stock level.</span>
                        </div>
                    <?php endif; ?>
                </div>

                <a class="panel-footer-link"
                   href="<?= $baseUrl ?>/admin/products/index.php">
                    <i class="bi bi-box-seam me-1"></i>
                    Manage Inventory
                </a>
            </section>

            <!-- Best Sellers -->
            <section class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h3>
                            <i class="bi bi-trophy me-2"></i>
                            Best Sellers
                        </h3>
                        <p>Products ranked by units sold.</p>
                    </div>
                </div>

                <?php if (!empty($bestSellingProducts)): ?>
                    <div class="panel-body">
                        <?php foreach ($bestSellingProducts as $index => $product): ?>
                            <div class="alert-row">
                                <div class="quick-action-icon">
                                    <?php if ($index === 0): ?>
                                        <i class="bi bi-trophy-fill"></i>
                                    <?php else: ?>
                                        <i class="bi bi-box"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="alert-content">
                                    <div class="alert-name">
                                        <?= e($product['product_name']) ?>
                                    </div>

                                    <div class="alert-meta">
                                        <?= number_format((int) $product['units_sold']) ?>
                                        unit(s) sold
                                    </div>
                                </div>

                                <div class="stock-number" style="color: #16834A;">
                                    <?= e(money((float) ($product['revenue'] ?? 0))) ?>
                                    <div class="table-sub">Revenue</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-bar-chart"></i>
                        <strong>No sales data yet</strong>
                        <span>Best sellers will appear after orders contain products.</span>
                    </div>
                <?php endif; ?>

                <a class="panel-footer-link"
                   href="<?= $baseUrl ?>/admin/products/index.php">
                    <i class="bi bi-bar-chart-line me-1"></i>
                    View Products
                </a>
            </section>

        </div>
    </div>

    <!-- Additional Status Summary -->
    <div class="dashboard-bottom">
        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3>
                        <i class="bi bi-activity me-2"></i>
                        Order Status Summary
                    </h3>
                    <p>A quick view of orders that need attention.</p>
                </div>

                <a href="<?= $baseUrl ?>/admin/orders/index.php">
                    Manage Orders <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="panel-body">
                <div class="stats-grid">

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Pending</div>
                            <div class="stat-icon icon-orange">
                                <i class="bi bi-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= number_format($pendingOrders) ?>
                        </div>
                        <div class="stat-description">
                            Orders awaiting review
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Processing</div>
                            <div class="stat-icon icon-blue">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= number_format($processingOrders) ?>
                        </div>
                        <div class="stat-description">
                            Orders being processed
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Low Stock Items</div>
                            <div class="stat-icon icon-orange">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= number_format($lowStockProducts) ?>
                        </div>
                        <div class="stat-description">
                            Products to consider restocking
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Contact Messages</div>
                            <div class="stat-icon icon-purple">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                        <div class="stat-value">
                            <?= number_format($contactMessages) ?>
                        </div>
                        <div class="stat-description">
                            <?= $contactMessages === 1
                                ? 'Message to review'
                                : 'Messages to review' ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
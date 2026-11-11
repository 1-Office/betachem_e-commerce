<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Admin Header
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| URLs
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';

$officialBaseUrl = 'https://bchem.co.za';

$officialHeaderLogo =
    '/assets/header_logo.png';

$officialLinkedInIcon =
    '/assets/linkedin_icon.png';

/*
|--------------------------------------------------------------------------
| Page Information
|--------------------------------------------------------------------------
*/

$pageTitle = $pageTitle ?? 'Admin Dashboard';

/*
|--------------------------------------------------------------------------
| Current Administrator
|--------------------------------------------------------------------------
*/

$currentRole = currentAdminRole();
$currentRoleName = adminRoleName($currentRole);
$currentRoleBadge = adminRoleBadge($currentRole);

$currentUserName = trim(
    ($_SESSION['user_first_name'] ?? '') . ' ' .
    ($_SESSION['user_last_name'] ?? '')
);

if ($currentUserName === '') {
    $currentUserName =
        $_SESSION['user_name']
        ?? $_SESSION['name']
        ?? 'Account';
}

$currentUserEmail = $_SESSION['user_email'] ?? '';

/*
|--------------------------------------------------------------------------
| Admin Navigation
|--------------------------------------------------------------------------
*/

$adminNavLinks = [];

if (hasPermission('dashboard')) {
    $adminNavLinks[] = [
        'text' => 'Dashboard',
        'href' => $baseUrl . '/admin/index.php',
        'icon' => 'fa-dashboard'
    ];
}

if (hasPermission('categories')) {
    $adminNavLinks[] = [
        'text' => 'Categories',
        'href' => $baseUrl . '/admin/categories/index.php',
        'icon' => 'fa-th-large'
    ];
}

if (hasPermission('products')) {
    $adminNavLinks[] = [
        'text' => 'Products',
        'href' => $baseUrl . '/admin/products/index.php',
        'icon' => 'fa-cubes'
    ];
}

if (hasPermission('orders')) {
    $adminNavLinks[] = [
        'text' => 'Orders',
        'href' => $baseUrl . '/admin/orders/index.php',
        'icon' => 'fa-shopping-cart'
    ];
}

if (hasPermission('customers')) {
    $adminNavLinks[] = [
        'text' => 'Customers',
        'href' => $baseUrl . '/admin/customers/index.php',
        'icon' => 'fa-users'
    ];
}

/*
|--------------------------------------------------------------------------
| Contact Messages
|--------------------------------------------------------------------------
*/

$newContactMessages = 0;

if (hasPermission('contact_messages')) {
    $contactCountResult = $conn->query("
        SELECT COUNT(*) AS total
        FROM contact_messages
        WHERE status = 'new'
    ");

    if ($contactCountResult) {
        $contactCountRow = $contactCountResult->fetch_assoc();
        $newContactMessages = (int) ($contactCountRow['total'] ?? 0);
    }

    $adminNavLinks[] = [
        'text' => 'Messages',
        'href' => $baseUrl . '/admin/contact_messages/index.php',
        'icon' => 'fa-envelope'
    ];
}

if (hasPermission('admin_users')) {
    $adminNavLinks[] = [
        'text' => 'Users',
        'href' => $baseUrl . '/admin/users/index.php',
        'icon' => 'fa-shield'
    ];
}

/*
|--------------------------------------------------------------------------
| Active Page Detection
|--------------------------------------------------------------------------
*/

$currentRequestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '',
    PHP_URL_PATH
) ?: '';

/*
|--------------------------------------------------------------------------
| Cart Count
|--------------------------------------------------------------------------
*/

$cartCount = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        if (is_numeric($quantity)) {
            $cartCount += (int) $quantity;
        }
    }
}

?>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
>

<link
    href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet"
>

<style>
.hex-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 95px;
    height: 65px;
    background-color: #1D88E9;
    color: #fff !important;
    text-align: center;
    font-family: 'Raleway', sans-serif;
    font-weight: bold;
    clip-path: polygon(
        25% 0%, 75% 0%, 100% 50%,
        75% 100%, 25% 100%, 0% 50%
    );
    transition: all .3s ease;
}

.hex-btn:hover {
    background-color: #2D208A;
}

.hex-nav {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 20px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.hex-nav .nav-item {
    margin: 0 -20px;
}

.hex-nav .nav-item:nth-child(even) {
    transform: translateY(47px);
}

.hex-nav .nav-link {
    padding: 0 !important;
}

.hex-nav .hex-btn {
    background: none;
    clip-path: none;
    padding: 0;
    width: auto;
    height: auto;
}

.hex-nav .hex-btn svg {
    width: 95px;
    height: 65px;
    transition: transform .3s ease, filter .3s ease;
}

.hex-nav .hex-btn:hover svg {
    transform: scale(1.1);
    filter: drop-shadow(0 0 8px rgba(29,136,233,.5));
}

.hex-nav .hex-btn svg path {
    stroke-width: 6;
}

.hex-nav .nav-item.active .hex-btn svg path {
    fill: #1745CA;
    stroke: #1745CA;
}

.hex-nav .nav-item.active .hex-btn svg text {
    fill: #fff;
}

.betachem-admin-header {
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    position: sticky;
    top: 0;
    z-index: 1030;
}

.admin-header-container {
    width: 100%;
    max-width: none;
    padding-left: 25px;
    padding-right: 25px;
}

.admin-header-logo {
    max-height: 140px;
    width: auto;
    vertical-align: middle;
}

.admin-label {
    color: #2D208A;
    font-family: 'Raleway', sans-serif;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-left: 8px;
}

.betachem-admin-actions {
    white-space: nowrap;
}

.admin-account-button {
    border: 1px solid #1745CA !important;
    color: #1745CA !important;
    background: #fff !important;
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    border-radius: 8px;
    padding: 8px 12px;
}

.admin-account-button:hover,
.admin-account-button[aria-expanded="true"] {
    background: #1745CA !important;
    color: #fff !important;
}

.admin-dropdown {
    min-width: 260px;
    padding: 8px;
    border: 1px solid #e3e7ed;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,.12);
}

.admin-dropdown .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 7px;
    padding: 10px 12px;
    font-family: 'Raleway', sans-serif;
    font-size: 13px;
    font-weight: 700;
    white-space: normal;
}

.admin-dropdown .dropdown-item i {
    width: 18px;
    text-align: center;
    font-size: 15px;
}

.admin-dropdown .dropdown-item:hover {
    background: #f2f6fb;
    color: #1745CA;
}

.admin-dropdown-profile {
    padding: 12px;
    overflow-wrap: anywhere;
}

.admin-dropdown-profile .profile-name {
    color: #172b4d;
    font-weight: 800;
    font-size: 14px;
}

.admin-dropdown-profile .profile-email {
    color: #718096;
    font-size: 12px;
    margin-top: 4px;
}

.admin-dropdown-profile .profile-role {
    display: inline-block;
    margin-top: 9px;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
}

.admin-dropdown-profile .role-superuser {
    color: #b42332;
    background: #fdebed;
}

.admin-dropdown-profile .role-administrator {
    color: #1757b8;
    background: #e8f1ff;
}

.admin-dropdown-profile .role-manager {
    color: #137547;
    background: #e6f6ed;
}

.admin-dropdown-profile .role-user {
    color: #596579;
    background: #eef1f5;
}

@media (max-width: 768px) {
    .admin-header-container {
        padding-left: 15px;
        padding-right: 15px;
    }

    .admin-header-logo {
        max-height: 80px;
    }

    .admin-label {
        display: none;
    }

    .navbar .collapse#adminNavMenu {
        margin-bottom: 0 !important;
        padding-bottom: 8px;
    }

    .navbar .hex-nav {
        display: block !important;
        gap: 0 !important;
        padding: 0 !important;
        margin: 8px 0 0 !important;
    }

    .navbar .hex-nav .nav-item,
    .navbar .hex-nav .nav-item:nth-child(even) {
        margin: 0 !important;
        transform: none !important;
    }

    .navbar .hex-nav .hex-btn svg {
        display: none !important;
    }

    .navbar .hex-nav .hex-btn {
        background: transparent !important;
        clip-path: none !important;
        width: 100% !important;
        height: auto !important;
        padding: 12px 6px !important;
        display: block !important;
        text-align: center;
    }

    .navbar .hex-nav .hex-btn::after {
        content: attr(aria-label);
        display: block;
        font-family: 'Raleway', sans-serif;
        font-weight: 700;
        font-size: 18px;
        color: #2D208A;
        line-height: 1.2;
        padding: 6px 0;
        border-bottom: 1px solid rgba(0,0,0,.08);
    }

    .betachem-admin-actions {
        margin-top: 10px;
        margin-bottom: 10px;
    }
}
</style>

<nav class="navbar navbar-expand-lg betachem-admin-header">
    <div class="admin-header-container">
        <div class="d-flex w-100 justify-content-between align-items-center">

            <!-- LOGO -->

            <div>
                <a
                    class="navbar-brand d-flex align-items-center"
                    href="<?= htmlspecialchars(
                        $baseUrl . '/admin/index.php',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    <img
                        src="<?= htmlspecialchars(
                            $officialHeaderLogo,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="Betachem"
                        class="img-fluid admin-header-logo"
                    >

                    <span class="admin-label">Admin</span>
                </a>
            </div>

            <!-- ADMIN NAVIGATION -->

            <div class="text-center">
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#adminNavMenu"
                    aria-controls="adminNavMenu"
                    aria-expanded="false"
                    aria-label="Toggle admin navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="adminNavMenu">
                    <ul class="text-center hex-nav navbar-nav">
                        <?php foreach ($adminNavLinks as $link): ?>

                            <?php
                            $linkPath = parse_url($link['href'], PHP_URL_PATH) ?: '';
                            $isActive = (
                                $linkPath !== '' &&
                                str_starts_with($currentRequestPath, $linkPath)
                            );

                            // Use the section path to keep parent navigation
                            // highlighted on its index, add and edit pages.
                            $section = basename(dirname($linkPath));

                            if ($section !== '' && $section !== '.') {
                                $isActive = str_contains(
                                    $currentRequestPath,
                                    '/admin/' . $section . '/'
                                ) || $currentRequestPath === $linkPath;
                            }
                            ?>

                            <li class="nav-item hover-zoom <?= $isActive ? 'active' : '' ?>">
                                <a
                                    href="<?= htmlspecialchars(
                                        $link['href'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    aria-label="<?= htmlspecialchars(
                                        $link['text'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="hex-btn nav-link"
                                    title="<?= htmlspecialchars(
                                        $link['text'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 200 173.205"
                                        preserveAspectRatio="xMidYMid meet"
                                    >
                                        <path
                                            d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                                            fill="<?= $isActive ? '#1745CA' : '#fff' ?>"
                                            stroke="<?= $isActive ? '#1745CA' : '#1D88E9' ?>"
                                            stroke-width="6"
                                        ></path>

                                        <text
                                            x="100"
                                            y="90"
                                            text-anchor="middle"
                                            dominant-baseline="middle"
                                            font-family="Raleway, Arial, sans-serif"
                                            font-weight="900"
                                            font-size="23"
                                            fill="<?= $isActive ? '#fff' : '#2D208A' ?>"
                                        ><?= htmlspecialchars(
                                            $link['text'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></text>
                                    </svg>

                                    <?php if (
                                        $link['text'] === 'Messages' &&
                                        $newContactMessages > 0
                                    ): ?>
                                        <span
                                            class="position-absolute badge rounded-pill bg-danger"
                                            style="margin-left:-18px;margin-top:4px;font-size:10px;"
                                        >
                                            <?= $newContactMessages ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>

                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- RIGHT SIDE -->

            <div class="d-flex flex-column align-items-center betachem-admin-actions">

                <!-- LINKEDIN -->

                <a
                    class="pb-1 mx-auto navbar-brand"
                    href="https://za.linkedin.com/company/betachem"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <img
                        src="<?= htmlspecialchars(
                            $officialLinkedInIcon,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="LinkedIn"
                        class="img-fluid"
                        style="max-height:30px;vertical-align:middle;"
                    >
                </a>

                <!-- ADMIN ACCOUNT DROPDOWN -->

                <div class="dropdown">
                    <button
                        class="btn btn-sm dropdown-toggle admin-account-button"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="fa fa-user-circle me-1"></i>
                        <?= htmlspecialchars(
                            $currentUserName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end admin-dropdown">

                        <!-- CURRENT ACCOUNT DETAILS -->

                        <li>
                            <div class="admin-dropdown-profile">
                                <div class="profile-name">
                                    <i class="fa fa-user-circle me-2"></i>
                                    <?= htmlspecialchars(
                                        $currentUserName,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>

                                <?php if ($currentUserEmail !== ''): ?>
                                    <div class="profile-email">
                                        <i class="fa fa-envelope-o me-1"></i>
                                        <?= htmlspecialchars(
                                            $currentUserEmail,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $roleClass = [
                                    'superuser' => 'role-superuser',
                                    'administrator' => 'role-administrator',
                                    'manager' => 'role-manager',
                                    'user' => 'role-user',
                                ][$currentRole] ?? 'role-user';
                                ?>

                                <span class="profile-role <?= htmlspecialchars(
                                    $roleClass,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">
                                    <i class="fa fa-shield me-1"></i>
                                    <?= htmlspecialchars(
                                        $currentRoleName,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>
                            </div>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <!-- ADMIN PORTAL -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars(
                                    $baseUrl . '/admin/index.php',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <i class="fa fa-dashboard text-primary"></i>
                                Admin Portal
                            </a>
                        </li>

                        <!-- STORE FRONT -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="<?= htmlspecialchars(
                                    $baseUrl . '/shop.php',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <i class="fa fa-shopping-bag text-primary"></i>
                                Store Front
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <!-- LOGOUT -->

                        <li>
                            <a
                                class="dropdown-item text-danger"
                                href="<?= htmlspecialchars(
                                    $baseUrl . '/logout.php?redirect=official',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <i class="fa fa-sign-out"></i>
                                Logout
                            </a>
                        </li>

                    </ul>
                </div>
            </div>

        </div>
    </div>
</nav>

<main class="container-fluid px-4 py-4">

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
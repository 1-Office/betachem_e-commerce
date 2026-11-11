<?php
/*
|--------------------------------------------------------------------------
| BETACHEM E-COMMERCE HEADER
| PHP translation of the official header.blade.php
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cart_functions.php';

/*
|--------------------------------------------------------------------------
| BASE URLS
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';
$officialBaseUrl = '/betachem/betachem.betaoffice.co.za/public_html';

$logoUrl = '/assets/header_logo.png';
$linkedinUrl = '/assets/linkedin_icon.png';

/*
|--------------------------------------------------------------------------
| ESCAPE OUTPUT
|--------------------------------------------------------------------------
*/

if (!function_exists('betachemHeaderEscape')) {
    function betachemHeaderEscape($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

/*
|--------------------------------------------------------------------------
| USER SESSION
|--------------------------------------------------------------------------
*/

$isLoggedIn = !empty($_SESSION['user_id']);
$currentUser = $_SESSION['user'] ?? [];

if (!is_array($currentUser)) {
    $currentUser = [];
}

$userId = $_SESSION['user_id'] ?? null;

$firstName = (string) (
    $_SESSION['first_name']
    ?? $currentUser['first_name']
    ?? ''
);

$lastName = (string) (
    $_SESSION['last_name']
    ?? $currentUser['last_name']
    ?? ''
);

$userEmail = (string) (
    $_SESSION['user_email']
    ?? $_SESSION['email']
    ?? $currentUser['email']
    ?? ''
);

$displayName = trim($firstName . ' ' . $lastName);

if ($displayName === '') {
    $displayName = (string) (
        $_SESSION['user_name']
        ?? $_SESSION['name']
        ?? $userEmail
        ?: 'My Account'
    );
}

$userRole = strtolower(trim((string) (
    $_SESSION['user_role']
    ?? $_SESSION['role']
    ?? $_SESSION['user_type']
    ?? $currentUser['user_role']
    ?? $currentUser['role']
    ?? $currentUser['user_type']
    ?? ''
)));

$adminRoles = [
    'administrator',
    'superuser',
    'manager',
    'user',
];

$showAdminPortal = $isLoggedIn
    && in_array($userRole, $adminRoles, true);

/*
|--------------------------------------------------------------------------
| NAVIGATION URLS
|--------------------------------------------------------------------------
*/

$shopUrl = $isLoggedIn
    ? $officialBaseUrl . '/shop-sso'
    : $baseUrl . '/shop.php';

$signInUrl = $baseUrl . '/login.php';
$registerUrl = $baseUrl . '/register.php';

$accountUrl = $baseUrl . '/account.php';
$ordersUrl = $baseUrl . '/orders.php';
$notificationsUrl = $baseUrl . '/notifications.php';
$cartUrl = $baseUrl . '/cart.php';

/*
|--------------------------------------------------------------------------
| ADMIN PORTAL
|--------------------------------------------------------------------------
| Open the PHP e-commerce admin dashboard directly.
| This does not use the Laravel admin portal redirect route.
|--------------------------------------------------------------------------
*/

$adminPortalUrl = $baseUrl . '/admin/index.php';

$logoutUrl = $baseUrl . '/logout.php?redirect=official';

/*
|--------------------------------------------------------------------------
| CART AND NOTIFICATIONS
|--------------------------------------------------------------------------
*/

$cartCount = 0;

if (function_exists('getCartCount')) {
    try {
        $cartCount = (int) getCartCount();
    } catch (Throwable $e) {
        $cartCount = 0;
    }
}

$unreadNotificationsCount = 0;

if (
    $isLoggedIn
    && $userId
    && function_exists('getUnreadNotificationsCount')
) {
    try {
        $unreadNotificationsCount =
            (int) getUnreadNotificationsCount($userId);
    } catch (Throwable $e) {
        $unreadNotificationsCount = 0;
    }
}

/*
|--------------------------------------------------------------------------
| NAVIGATION LINKS
|--------------------------------------------------------------------------
*/

$navLinks = [
    ['text' => 'Home',       'href' => $officialBaseUrl . '/'],
    ['text' => 'About',      'href' => $officialBaseUrl . '/about'],
    ['text' => 'Industries', 'href' => $officialBaseUrl . '/industries'],
    ['text' => 'Products',   'href' => $officialBaseUrl . '/products'],
    ['text' => 'Services',   'href' => $officialBaseUrl . '/services'],
    ['text' => 'Team',       'href' => $officialBaseUrl . '/team'],
    ['text' => 'Resources',  'href' => $officialBaseUrl . '/resources'],
    ['text' => 'Shop',       'href' => $shopUrl],
    ['text' => 'Contact',    'href' => $officialBaseUrl . '/contact'],
];

$requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

$activeLink = '';

foreach ($navLinks as $link) {
    $linkPath = parse_url($link['href'], PHP_URL_PATH) ?: '/';

    if (
        $linkPath === $requestPath
        || (
            $link['text'] === 'Home'
            && rtrim($requestPath, '/') === rtrim($officialBaseUrl, '/')
        )
    ) {
        $activeLink = $link['text'];
        break;
    }
}

$hexAnimations = [
    'animate__fadeInTopLeft',
    'animate__fadeInBottomLeft',
    'animate__fadeInDown',
    'animate__fadeInUp',
    'animate__fadeInDown',
    'animate__fadeInBottomRight',
    'animate__fadeInTopRight',
    'animate__fadeIn',
    'animate__fadeIn',
];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link
    href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
>

<nav class="bg-white shadow-sm navbar sticky-top navbar-expand-lg site-header">
    <div class="container">
        <div class="d-flex w-100 justify-content-between align-items-center">

            <!-- LOGO / OFFICIAL HOME -->
            <div class="header-logo-container">
                <a
                    class="navbar-brand d-flex align-items-center"
                    href="<?= betachemHeaderEscape($officialBaseUrl . '/') ?>"
                >
                    <img
                        src="<?= betachemHeaderEscape($logoUrl) ?>"
                        alt="Betachem"
                        class="img-fluid"
                        style="max-height: 140px; vertical-align: middle;"
                    >
                </a>
            </div>

            <!-- MAIN NAVIGATION -->
            <div class="header-navigation text-center">
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navMenu"
                    aria-controls="navMenu"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navMenu">
                    <ul class="text-center hex-nav navbar-nav">

                        <?php foreach ($navLinks as $index => $link): ?>
                            <?php
                            $label = $link['text'];
                            $href = $link['href'];
                            $isActive = $activeLink === $label;
                            $isContact = $label === 'Contact';
                            ?>

                            <li class="nav-item hover-zoom">
                                <a
                                    href="<?= betachemHeaderEscape($href) ?>"
                                    aria-label="<?= betachemHeaderEscape($label) ?>"
                                    class="hex-btn nav-link animate__animated <?= betachemHeaderEscape($hexAnimations[$index]) ?>"
                                    <?= $label === 'Shop' ? 'data-shop-link="true"' : '' ?>
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 200 173.205"
                                        preserveAspectRatio="xMidYMid meet"
                                    >
                                        <path
                                            d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                                            fill="<?= $isContact ? '#1D88E9' : '#fff' ?>"
                                            stroke="#1D88E9"
                                            stroke-width="6"
                                        />

                                        <text
                                            x="100"
                                            y="90"
                                            text-anchor="middle"
                                            dominant-baseline="middle"
                                            font-family="Raleway, Arial, sans-serif"
                                            font-weight="900"
                                            font-size="26"
                                            fill="<?= $isContact ? '#fff' : ($isActive ? '#000000' : '#2D208A') ?>"
                                        ><?= betachemHeaderEscape($label) ?></text>
                                    </svg>
                                </a>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="header-right d-flex flex-column align-items-center">

                <!-- LINKEDIN -->
                <a
                    class="pb-1 mx-auto navbar-brand"
                    href="https://za.linkedin.com/company/betachem"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <img
                        src="<?= betachemHeaderEscape($linkedinUrl) ?>"
                        alt="LinkedIn"
                        class="img-fluid"
                        style="max-height: 30px; vertical-align: middle;"
                    >
                </a>

                <!-- ACCOUNT LINKS -->
                <div class="header-actions d-flex align-items-center gap-2">

                    <?php if (!$isLoggedIn): ?>

                        <a
                            href="<?= betachemHeaderEscape($signInUrl) ?>"
                            class="btn header-action-btn btn-primary"
                        >Sign In</a>

                        <a
                            href="<?= betachemHeaderEscape($registerUrl) ?>"
                            class="btn header-action-btn btn-outline-primary"
                        >Register</a>

                    <?php else: ?>

                        <div class="dropdown">
                            <button
                                class="btn header-action-btn btn-outline-primary dropdown-toggle account-dropdown"
                                type="button"
                                id="accountDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="fa fa-user-circle me-1"></i>
                                <?= betachemHeaderEscape($displayName) ?>
                            </button>

                            <ul
                                class="dropdown-menu dropdown-menu-end"
                                aria-labelledby="accountDropdown"
                            >
                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="<?= betachemHeaderEscape($accountUrl) ?>"
                                    >
                                        <i class="fa fa-user me-2"></i>
                                        My Account
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="<?= betachemHeaderEscape($ordersUrl) ?>"
                                    >
                                        <i class="fa fa-shopping-bag me-2"></i>
                                        My Orders
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="<?= betachemHeaderEscape($notificationsUrl) ?>"
                                    >
                                        <i class="fa fa-bell me-2"></i>
                                        Notifications

                                        <?php if ($unreadNotificationsCount > 0): ?>
                                            <span class="badge bg-danger rounded-pill ms-2">
                                                <?= (int) $unreadNotificationsCount ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </li>

                                <?php if ($showAdminPortal): ?>
                                    <li><hr class="dropdown-divider"></li>

                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="<?= betachemHeaderEscape($adminPortalUrl) ?>"
                                        >
                                            <i class="fa fa-shield me-2"></i>
                                            Admin Portal
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="<?= betachemHeaderEscape($logoutUrl) ?>"
                                    >
                                        <i class="fa fa-sign-out me-2"></i>
                                        Sign Out
                                    </a>
                                </li>
                            </ul>
                        </div>

                    <?php endif; ?>

                
<a
    href="<?= betachemHeaderEscape($cartUrl) ?>"
    class="btn header-action-btn btn-primary cart-header-btn"
    aria-label="Shopping cart: <?= (int) $cartCount ?> items"
>
    <i class="fa fa-shopping-cart me-1"></i>
    <span>Cart</span>

    <span class="cart-count-badge" aria-live="polite">
        <?= (int) $cartCount ?>
    </span>
</a>

                </div>
            </div>

        </div>
    </div>
</nav>

<style>


.cart-header-btn {
    position: relative;
    overflow: visible;
}

.cart-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 19px;
    height: 19px;
    padding: 0 5px;
    margin-left: 4px;
    border-radius: 50px;
    background: #ffffff;
    color: #1745CA;
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
}

.site-header,
nav.navbar.sticky-top {
    z-index: 1030;
}

.header-logo-container {
    flex: 0 0 auto;
}

.header-logo-container img {
    width: auto;
    max-width: 210px;
    object-fit: contain;
}

.header-navigation {
    flex: 1 1 auto;
    min-width: 0;
    padding: 0 12px;
}

.header-right {
    flex: 0 0 auto;
}

/* HEXAGON NAVIGATION */
.hex-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: nowrap;
    gap: 0;
    margin: 0;
    padding: 0;
}

.hex-nav .nav-item {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ZIG-ZAG: ALTERNATE HEXAGONS MOVE DOWN */
.hex-nav .nav-item:nth-child(even) {
    transform: translateY(32px);
}

.hex-nav .hex-btn {
    display: block;
    width: 95px;
    max-width: 100%;
    padding: 0;
    margin: 0;
    background: transparent;
    border: 0;
    transition: transform .2s ease;
}

.hex-nav .hex-btn svg {
    display: block;
    width: 100%;
    height: auto;
}

.hex-nav .hex-btn:hover {
    transform: scale(1.04);
}

.header-actions {
    flex-wrap: nowrap;
}

.header-action-btn {
    box-sizing: border-box;
    width: 100px;
    min-width: 100px;
    height: 36px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    font-size: 13px;
    line-height: 1;
    border-radius: 5px;
    text-decoration: none;
}

.header-action-btn.btn-outline-primary {
    border-color: #1745CA;
    color: #1745CA;
}

.header-action-btn.btn-outline-primary:hover {
    background: #1745CA;
    color: #fff;
}

.header-action-btn.btn-primary {
    background: #1745CA;
    border-color: #1745CA;
}

.header-action-btn.btn-primary:hover {
    background: #1D88E9;
    border-color: #1D88E9;
}

.account-dropdown {
    max-width: 190px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.header-actions .dropdown-menu {
    z-index: 1050;
    min-width: 200px;
}

@media (max-width: 1199.98px) {
    .header-logo-container img {
        max-width: 160px;
        max-height: 100px !important;
    }

    .hex-nav .hex-btn {
        width: 78px;
    }

    .header-navigation {
        padding: 0 5px;
    }

    .header-action-btn {
        width: 88px;
        min-width: 88px;
    }
}

@media (max-width: 991.98px) {
    nav.navbar.sticky-top > .container > .d-flex {
        flex-wrap: wrap;
    }

    .header-logo-container img {
        max-width: 150px;
        max-height: 85px !important;
    }

    .header-navigation {
        flex: 0 0 auto;
        order: 3;
        width: 100%;
        padding: 10px 0 0;
        text-align: left !important;
    }

    .header-navigation .navbar-toggler {
        margin: 0 0 10px;
    }

    .header-navigation .navbar-collapse {
        width: 100%;
    }

    .hex-nav {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        gap: 5px;
    }

    .hex-nav .nav-item {
        width: 100%;
    }

    /* DISABLE ZIG-ZAG ON MOBILE */
    .hex-nav .nav-item:nth-child(even) {
        transform: none;
    }

    .hex-nav .hex-btn {
        width: 100%;
        max-width: 100%;
    }

    .hex-nav .hex-btn svg {
        width: 95px;
        max-width: 100%;
        margin: 0 auto;
    }

    .header-right {
        margin-left: auto;
    }

    .header-actions {
        justify-content: flex-end;
        flex-wrap: wrap;
    }
}

@media (max-width: 575.98px) {
    .header-logo-container img {
        max-width: 115px;
        max-height: 65px !important;
    }

    .header-right > a img {
        max-height: 24px !important;
    }

    .header-actions {
        gap: 5px !important;
    }

    .header-action-btn {
        width: 76px;
        min-width: 76px;
        height: 34px;
        padding: 0 5px;
        font-size: 11px;
    }

    .account-dropdown {
        max-width: 145px;
    }
}

/* SHOP-CLICK ANIMATION */
.hex-nav.shop-pop-up .nav-item {
    animation: betachemShopPop .38s ease both;
}

.hex-nav.shop-pop-up .nav-item:nth-child(2) {
    animation-delay: .04s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(3) {
    animation-delay: .08s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(4) {
    animation-delay: .12s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(5) {
    animation-delay: .16s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(6) {
    animation-delay: .20s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(7) {
    animation-delay: .24s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(8) {
    animation-delay: .28s;
}

.hex-nav.shop-pop-up .nav-item:nth-child(9) {
    animation-delay: .32s;
}

@keyframes betachemShopPop {
    0% {
        opacity: .5;
        transform: scale(.92);
    }

    65% {
        opacity: 1;
        transform: scale(1.06);
    }

    100% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shopLink = document.querySelector('[data-shop-link="true"]');

    if (!shopLink) {
        return;
    }

    shopLink.addEventListener('click', function (event) {
        if (
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.button !== 0
        ) {
            return;
        }

        const nav = shopLink.closest('.hex-nav');

        if (!nav) {
            return;
        }

        event.preventDefault();

        const destination = shopLink.href;

        nav.classList.remove('shop-pop-up');
        void nav.offsetWidth;
        nav.classList.add('shop-pop-up');

        window.setTimeout(function () {
            window.location.href = destination;
        }, 430);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
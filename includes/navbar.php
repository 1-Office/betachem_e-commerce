<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Customer Navbar
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/cart_functions.php';
require_once __DIR__ . '/notification_functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);

$isCustomer =
    isset($_SESSION['user_id']) &&
    isset($_SESSION['user_role']) &&
    $_SESSION['user_role'] === 'customer';

$cartCount = 0;
$notificationCount = 0;

if ($isCustomer) {

    $cartCount = getCartCount();

    $notificationCount = getUnreadNotificationCount(
        (int) $_SESSION['user_id']
    );
}

?>

<nav
    class="navbar navbar-expand-lg navbar-light bg-white sticky-top"
    style="
        border-bottom:1px solid #e8eef3;
        box-shadow:0 3px 15px rgba(20,50,80,.05);
    "
>

    <div class="container">

        <!-- BRAND -->

        <a
            class="navbar-brand fw-bold"
            href="/betachem_e-commerce/index.php"
            style="
                color:#1769aa;
                letter-spacing:1px;
            "
        >
            BETACHEM
        </a>


        <!-- MOBILE TOGGLE -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- NAVIGATION -->

        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

                <!-- HOME -->

                <li class="nav-item">

                    <a
                        class="nav-link <?= $currentPage === 'index.php' ? 'active fw-semibold' : '' ?>"
                        href="/betachem_e-commerce/index.php"
                    >
                        Home
                    </a>

                </li>


                <!-- SHOP -->

                <li class="nav-item">

                    <a
                        class="nav-link <?= $currentPage === 'shop.php' ? 'active fw-semibold' : '' ?>"
                        href="/betachem_e-commerce/shop.php"
                    >
                        Shop
                    </a>

                </li>


                <!-- CATEGORIES -->

                <li class="nav-item">

                    <a
                        class="nav-link <?= $currentPage === 'category.php' ? 'active fw-semibold' : '' ?>"
                        href="/betachem_e-commerce/category.php"
                    >
                        Categories
                    </a>

                </li>

                <li class="nav-item">
    <a
        class="nav-link"
        href="/betachem_e-commerce/contact.php"
    >
        Contact Us
    </a>
</li>

                <!-- CART -->

                <li class="nav-item">

                    <a
                        class="nav-link position-relative"
                        href="/betachem_e-commerce/cart.php"
                    >

                        <i class="bi bi-cart3 me-1"></i>

                        Cart

                        <span
                            class="badge rounded-pill bg-primary ms-1"
                        >
                            <?= (int) $cartCount ?>
                        </span>

                    </a>

                </li>


                <?php if ($isCustomer): ?>

                    <!-- NOTIFICATIONS -->

                    <li class="nav-item">

                        <a
                            class="nav-link position-relative"
                            href="/betachem_e-commerce/notifications.php"
                            title="Notifications"
                        >

                            <i
                                class="bi bi-bell"
                                style="font-size:18px;"
                            ></i>

                            <?php if ($notificationCount > 0): ?>

                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="
                                        font-size:9px;
                                        min-width:18px;
                                    "
                                >
                                    <?= $notificationCount > 99 ? '99+' : $notificationCount ?>

                                    <span class="visually-hidden">
                                        unread notifications
                                    </span>

                                </span>

                            <?php endif; ?>

                        </a>

                    </li>


                    <!-- ACCOUNT -->

                    <li class="nav-item">

                        <a
                            class="nav-link <?= $currentPage === 'account.php' ? 'active fw-semibold' : '' ?>"
                            href="/betachem_e-commerce/account.php"
                        >

                            <i class="bi bi-person-circle me-1"></i>

                            My Account

                        </a>

                    </li>


                    <!-- LOGOUT -->

                    <li class="nav-item">

                        <a
                            class="btn btn-outline-primary btn-sm ms-lg-2"
                            href="/betachem_e-commerce/logout.php"
                        >

                            <i class="bi bi-box-arrow-right me-1"></i>

                            Logout

                        </a>

                    </li>


                <?php else: ?>


                    <!-- LOGIN -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="/betachem_e-commerce/login.php"
                        >
                            Login
                        </a>

                    </li>


                    <!-- REGISTER -->

                    <li class="nav-item">

                        <a
                            class="btn btn-primary btn-sm ms-lg-2"
                            href="/betachem_e-commerce/register.php"
                        >
                            Create Account
                        </a>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>
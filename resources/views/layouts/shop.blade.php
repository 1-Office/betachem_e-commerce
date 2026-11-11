<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Betachem | Industrial & Laboratory Chemicals')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    @stack('styles')

    <style>
        /* =====================================================
           GENERAL LAYOUT
        ===================================================== */

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            font-family: 'Raleway', Arial, sans-serif;
        }

        main {
            flex: 1 0 auto;
        }

        /* =====================================================
           ORIGINAL BETACHEM HEADER
        ===================================================== */

        .site-header,
        nav.navbar.sticky-top {
            z-index: 1030;
        }

        .site-header {
            background: #fff;
            position: sticky;
            top: 0;
            box-shadow: 0 3px 12px rgba(0,0,0,.10);
        }

        .header-logo-container {
            flex: 0 0 auto;
        }

        .header-logo-container img {
            width: auto;
            max-width: 210px;
            max-height: 140px;
            object-fit: contain;
            vertical-align: middle;
        }

        .header-navigation {
            flex: 1 1 auto;
            min-width: 0;
            padding: 0 12px;
        }

        .header-right {
            flex: 0 0 auto;
        }

        /* =====================================================
           HEXAGON NAVIGATION
        ===================================================== */

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

        /* Original zig-zag arrangement */

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

        /* =====================================================
           HEADER ACTION BUTTONS
        ===================================================== */

        .header-actions {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 8px;
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
            font-family: 'Raleway', Arial, sans-serif;
            font-size: 13px;
            font-weight: 700;
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
            color: #fff;
        }

        .header-action-btn.btn-primary:hover {
            background: #1D88E9;
            border-color: #1D88E9;
            color: #fff;
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

        /* =====================================================
           CART
        ===================================================== */

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
            background: #fff;
            color: #1745CA;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
        }

        /* =====================================================
           SHOP CLICK ANIMATION
        ===================================================== */

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

        /* =====================================================
           FOOTER
        ===================================================== */

        .footer-custom {
            flex-shrink: 0;
            margin: 0;
            position: relative;
            z-index: 2;
            background: linear-gradient(
                135deg,
                #2D208A 0%,
                #1745CA 50%,
                #1D88E9 100%
            );
            color: #fff;
        }

        .footer-custom a {
            color: #fff;
        }

        .footer-custom a:hover {
            color: #fff;
        }

        .footer-custom h5,
        .footer-custom h6 {
            font-family: 'Raleway', Arial, sans-serif;
            font-weight: 900;
            color: #fff;
        }

        .footer-custom p {
            line-height: 1.55;
            color: #fff;
        }

        .footer-custom .footer-hex {
            background: none;
            clip-path: none;
            padding: 0;
            width: auto;
            height: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .footer-custom .footer-hex svg {
            width: 95px;
            height: 65px;
            display: block;
            transition: transform .3s ease, filter .3s ease;
        }

        .footer-custom .footer-hex:hover svg {
            transform: scale(1.2);
            filter: drop-shadow(0 0 8px rgba(255,255,255,.45));
        }

        .footer-custom .footer-hex svg path {
            stroke-width: 6;
        }

        .footer-logo {
            max-height: 180px;
            max-width: 100%;
            object-fit: contain;
        }

        .footer-custom .btn-info {
            color: #fff !important;
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            white-space: normal;
        }

        .footer-custom .btn-info:hover {
            color: #fff !important;
            background-color: #0aa7c2;
            border-color: #0aa7c2;
        }

        #pdfModal .modal-title,
        #mapModal .modal-title {
            color: #000;
        }

        #viewerWrap {
            background: #fff;
        }

        #viewerLoader {
            display: none;
            z-index: 2;
        }

        #viewerWrap.loading #viewerLoader {
            display: block;
        }

        /* =====================================================
           RESPONSIVE HEADER
        ===================================================== */

        @media (max-width: 1199.98px) {
            .header-logo-container img {
                max-width: 160px;
                max-height: 100px;
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
                max-height: 85px;
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

            .footer-custom {
                position: static !important;
            }
        }

        @media (max-width: 575.98px) {
            .header-logo-container img {
                max-width: 115px;
                max-height: 65px;
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

            .footer-custom .btn-info {
                font-size: 10px;
                padding: 5px 3px;
            }
        }
    </style>
</head>

<body>

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    /*
    |--------------------------------------------------------------------------
    | OFFICIAL BETACHEM WEBSITE
    |--------------------------------------------------------------------------
    */

    $officialBaseUrl = 'https://betachem.betaoffice.co.za';

    /*
    |--------------------------------------------------------------------------
    | HEADER ASSETS
    |--------------------------------------------------------------------------
    */

    $logoUrl = asset('assets/header_logo.png');
    $linkedinUrl = asset('assets/linkedin_icon.png');

    /*
    |--------------------------------------------------------------------------
    | NAVIGATION
    |--------------------------------------------------------------------------
    */

    $shopUrl = url('/shop');

    $navLinks = [
        ['text' => 'Home',       'url' => $officialBaseUrl . '/',          'active' => false],
        ['text' => 'About',      'url' => $officialBaseUrl . '/about',     'active' => false],
        ['text' => 'Industries', 'url' => $officialBaseUrl . '/industries','active' => false],
        ['text' => 'Products',   'url' => $officialBaseUrl . '/products', 'active' => false],
        ['text' => 'Services',   'url' => $officialBaseUrl . '/services', 'active' => false],
        ['text' => 'Team',       'url' => $officialBaseUrl . '/team',     'active' => false],
        ['text' => 'Resources',  'url' => $officialBaseUrl . '/resources','active' => false],
        ['text' => 'Shop',       'url' => $shopUrl,                       'active' => request()->is('shop*', 'cart*', 'checkout*', 'orders*', 'account*')],
        ['text' => 'Contact',    'url' => $officialBaseUrl . '/contact',  'active' => false],
    ];

    /*
    |--------------------------------------------------------------------------
    | HEXAGON ANIMATIONS
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED USER
    |--------------------------------------------------------------------------
    */

    $loggedIn = Auth::check();
    $user = Auth::user();

    $displayName = 'My Account';

    if ($loggedIn) {
        $firstName = $user->first_name ?? '';
        $lastName = $user->last_name ?? '';

        $displayName = trim($firstName . ' ' . $lastName);

        if ($displayName === '') {
            $displayName = $user->name
                ?? $user->email
                ?? 'My Account';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | USER ROLE
    |--------------------------------------------------------------------------
    */

    $userRole = strtolower(trim((string) (
        $user->user_role
        ?? $user->role
        ?? $user->user_type
        ?? ''
    )));

    $adminRoles = [
        'user',
        'administrator',
        'superuser',
        'manager',
    ];

    $showAdminPortal =
        $loggedIn
        && in_array($userRole, $adminRoles, true)
        && Route::has('admin.dashboard');

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    $unreadNotificationsCount = 0;

    if ($loggedIn && method_exists($user, 'unreadNotifications')) {
        try {
            $unreadNotificationsCount = $user->unreadNotifications()->count();
        } catch (\Throwable $e) {
            $unreadNotificationsCount = 0;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CART COUNT
    |--------------------------------------------------------------------------
    */

    $cartCount = 0;

    try {
        $cartQuery = \App\Models\Cart::query();

        if ($loggedIn) {
            $cartQuery->where('user_id', Auth::id());
        } else {
            $cartQuery->where('session_id', session()->getId());
        }

        $cart = $cartQuery->withCount('items')->first();

        $cartCount = (int) ($cart?->items_count ?? 0);

    } catch (\Throwable $e) {
        $cartCount = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | FOOTER ASSETS
    |--------------------------------------------------------------------------
    */

    $officialFooterLogo = $officialBaseUrl . '/assets/header_logo.png';

    $privacyPolicy = $officialBaseUrl . '/assets/docs/Privacy%20Policy.pdf';

    $betachemMap = $officialBaseUrl . '/assets/docs/Map.pdf';
@endphp


<!-- =========================================================
     ORIGINAL BETACHEM HEADER
========================================================= -->

<nav class="bg-white shadow-sm navbar sticky-top navbar-expand-lg site-header">

    <div class="container">

        <div class="d-flex w-100 justify-content-between align-items-center">

            <!-- LOGO / OFFICIAL HOME -->

            <div class="header-logo-container">

                <a
                    class="navbar-brand d-flex align-items-center"
                    href="{{ $officialBaseUrl }}/"
                >
                    <img
                        src="{{ $logoUrl }}"
                        alt="Betachem"
                        class="img-fluid"
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

                        @foreach($navLinks as $index => $link)

                            @php
                                $label = $link['text'];
                                $href = $link['url'];
                                $isActive = $link['active'];
                                $isContact = $label === 'Contact';
                            @endphp

                            <li class="nav-item hover-zoom">

                                <a
                                    href="{{ $href }}"
                                    aria-label="{{ $label }}"
                                    class="hex-btn nav-link animate__animated {{ $hexAnimations[$index] }}"
                                    @if($label === 'Shop')
                                        data-shop-link="true"
                                    @endif
                                >

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 200 173.205"
                                        preserveAspectRatio="xMidYMid meet"
                                        aria-hidden="true"
                                    >

                                        <path
                                            d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                                            fill="{{ $isContact ? '#1D88E9' : '#fff' }}"
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
                                            fill="{{ $isContact ? '#fff' : ($isActive ? '#000000' : '#2D208A') }}"
                                        >{{ $label }}</text>

                                    </svg>

                                </a>

                            </li>

                        @endforeach

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
                    aria-label="Betachem LinkedIn"
                >
                    <img
                        src="{{ $linkedinUrl }}"
                        alt="LinkedIn"
                        class="img-fluid"
                        style="max-height: 30px; vertical-align: middle;"
                    >
                </a>


                <!-- ACCOUNT LINKS -->

<div class="header-actions d-flex align-items-center gap-2">

    @auth

        <div class="dropdown">

            <button
                class="btn header-action-btn btn-outline-primary dropdown-toggle account-dropdown"
                type="button"
                id="accountDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <i class="fa fa-user-circle me-1"></i>
                {{ $displayName }}
            </button>

            <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="accountDropdown"
            >

                {{-- MY ACCOUNT --}}

                <li>
                    <a
                        class="dropdown-item"
                        href="{{ url('/shop/account') }}"
                    >
                        <i class="fa fa-user me-2"></i>
                        My Account
                    </a>
                </li>

                {{-- MY ORDERS --}}

                <li>
                    <a
                        class="dropdown-item"
                        href="{{ route('shop.account.orders') }}"
                    >
                        <i class="fa fa-shopping-bag me-2"></i>
                        My Orders
                    </a>
                </li>

               {{-- NOTIFICATIONS --}}

@if(Route::has('shop.notifications.index'))
    <li>
        <a
            class="dropdown-item d-flex justify-content-between align-items-center"
            href="{{ route('shop.notifications.index') }}"
        >
            <span>
                <i class="fa fa-bell me-2"></i>
                Notifications
            </span>

            @if($unreadNotificationsCount > 0)
                <span class="badge bg-danger rounded-pill">
                    {{ $unreadNotificationsCount }}
                </span>
            @endif
        </a>
    </li>
@endif

                {{-- ADMIN PORTAL --}}

                @if($showAdminPortal)

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a
                            class="dropdown-item"
                            href="{{ route('admin.dashboard') }}"
                        >
                            <i class="fa fa-shield me-2"></i>
                            Admin Portal
                        </a>
                    </li>

                @endif

                <li>
                    <hr class="dropdown-divider">
                </li>

                {{-- SIGN OUT --}}

                <li>
                    <form
                        action="{{ url('/shop/logout') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="dropdown-item"
                        >
                            <i class="fa fa-sign-out me-2"></i>
                            Sign Out
                        </button>
                    </form>
                </li>

            </ul>

        </div>

    @else

        <a
            href="{{ url('/shop/login') }}"
            class="btn header-action-btn btn-primary"
        >
            Sign In
        </a>

        <a
            href="{{ url('/shop/register') }}"
            class="btn header-action-btn btn-outline-primary"
        >
            Register
        </a>

    @endauth

    {{-- SHOPPING CART --}}

    <a
        href="{{ route('shop.cart.index') }}"
        class="btn header-action-btn btn-primary cart-header-btn"
        aria-label="Shopping cart: {{ $cartCount }} items"
    >
        <i class="fa fa-shopping-cart me-1"></i>

        <span>Cart</span>

        <span class="cart-count-badge" aria-live="polite">
            {{ $cartCount }}
        </span>
    </a>

</div>

            </div>

        </div>

    </div>

</nav>


<!-- =========================================================
     PAGE CONTENT
========================================================= -->

<main class="container-fluid py-4">

    @yield('content')

</main>


<!-- =========================================================
     BETACHEM FOOTER
========================================================= -->

<footer class="pt-3 mt-auto text-white footer-custom">

    <div class="container">

        <div class="row text-white">

            {{-- DESKTOP --}}

            <div class="d-none d-lg-flex w-100">

                <div class="row w-100 align-items-start">

                    {{-- LOGO --}}

                    <div class="col-lg-1">

                        <div class="mb-3 text-center">

                            <img
                                src="{{ $officialFooterLogo }}"
                                alt="Betachem Footer Logo"
                                class="img-fluid footer-logo"
                            >

                        </div>

                    </div>


                    {{-- NUMBERS AND EMAIL --}}

                    <div class="col-lg-3 text-center fs-6">

                        <h5 class="mb-2">
                            Numbers &amp; Email
                        </h5>

                        <p class="mb-1">

                            t:
                            <a href="tel:+27117828789">
                                +27 11 782 8789
                            </a>

                            <br>

                            f:
                            <a href="tel:+27118887458">
                                +27 11 888 7458
                            </a>

                            <br>

                            m:
                            <a href="tel:+27728045528">
                                +27 72 804 5528
                            </a>

                            <br>

                            <a href="mailto:contact@bchem.co.za">
                                contact@bchem.co.za
                            </a>

                        </p>

                    </div>


                    {{-- PHYSICAL ADDRESSES --}}

                    <div class="col-lg-4">

                        <div class="row">

                            <h5 class="mb-3 text-center">
                                Physical Addresses
                            </h5>

                            <div class="col-sm-6 text-center">

                                <h6 class="text-uppercase">
                                    Administration Office
                                </h6>

                                <p class="mb-3 small">
                                    31 Pafuri Road
                                    <br>
                                    Emmarentia
                                    <br>
                                    Johannesburg
                                    <br>
                                    South Africa
                                </p>

                            </div>

                            <div class="col-sm-6 text-center">

                                <h6 class="text-uppercase">
                                    Factory &amp; Laboratory
                                </h6>

                                <p class="mb-3 small">
                                    Plot 5, Producta
                                    <br>
                                    Heidelberg
                                    <br>
                                    Gauteng
                                    <br>

                                    <a
                                        href="https://www.google.com/maps?q=-26.414592,28.353447"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        -26.414592, 28.353447
                                    </a>

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- RESOURCES --}}

                    <div class="col-lg-4 text-center">

                        <a
                            href="{{ $officialBaseUrl }}/resources"
                            class="footer-hex"
                            aria-label="Resources"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 200 173.205"
                                preserveAspectRatio="xMidYMid meet"
                            >

                                <path
                                    d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                                    fill="#fff"
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
                                    fill="#2D208A"
                                >
                                    Resources
                                </text>

                            </svg>

                        </a>


                        <div class="row mt-4 mb-0 text-center">

                            <div class="col-4">

                                <a
                                    href="{{ $privacyPolicy }}"
                                    class="btn btn-sm btn-info doc-link"
                                    data-title="Privacy Policy"
                                    data-viewer="google"
                                >
                                    Privacy Policy
                                </a>

                            </div>


                            <div class="col-4">

                                <a
                                    href="#"
                                    class="btn btn-sm btn-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#mapModal"
                                >
                                    Courier Address
                                </a>

                            </div>


                            <div class="col-4">

                                <a
                                    href="{{ $betachemMap }}"
                                    class="btn btn-sm btn-info doc-link"
                                    data-title="Betachem Map"
                                    data-viewer="google"
                                >
                                    Betachem Map
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- TABLET --}}

            <div class="d-none d-md-block d-lg-none w-100">

                <div class="row align-items-start">

                    <div class="col-md-4 text-center fs-6">

                        <h5 class="mb-2">
                            Numbers &amp; Email
                        </h5>

                        <p class="mb-1">

                            t:
                            <a href="tel:+27117828789">
                                +27 11 782 8789
                            </a>

                            <br>

                            f:
                            <a href="tel:+27118887458">
                                +27 11 888 7458
                            </a>

                            <br>

                            m:
                            <a href="tel:+27728045528">
                                +27 72 804 5528
                            </a>

                            <br>

                            <a href="mailto:contact@bchem.co.za">
                                contact@bchem.co.za
                            </a>

                        </p>

                    </div>


                    <div class="col-md-8">

                        <div class="row">

                            <h5 class="mb-2 text-center">
                                Physical Addresses
                            </h5>

                            <div class="col-6 text-center">

                                <h6 class="text-uppercase">
                                    Administration Office
                                </h6>

                                <p class="mb-2 small">
                                    31 Pafuri Road
                                    <br>
                                    Emmarentia
                                    <br>
                                    Johannesburg
                                    <br>
                                    South Africa
                                </p>

                            </div>

                            <div class="col-6 text-center">

                                <h6 class="text-uppercase">
                                    Factory &amp; Laboratory
                                </h6>

                                <p class="mb-2 small">
                                    Plot 5, Producta
                                    <br>
                                    Heidelberg
                                    <br>
                                    Gauteng
                                    <br>

                                    <a
                                        href="https://www.google.com/maps?q=-26.414592,28.353447"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        -26.414592, 28.353447
                                    </a>

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- MOBILE --}}

            <div class="d-block d-md-none w-100 text-center fs-6">

                <h5 class="mb-2">
                    Numbers &amp; Email
                </h5>

                <p class="mb-1">

                    t:
                    <a href="tel:+27117828789">
                        +27 11 782 8789
                    </a>

                    <br>

                    f:
                    <a href="tel:+27118887458">
                        +27 11 888 7458
                    </a>

                    <br>

                    m:
                    <a href="tel:+27728045528">
                        +27 72 804 5528
                    </a>

                    <br>

                    <a href="mailto:contact@bchem.co.za">
                        contact@bchem.co.za
                    </a>

                </p>

            </div>

        </div>

    </div>


    {{-- PDF DOCUMENT VIEWER MODAL --}}

    <div
        class="modal fade"
        id="pdfModal"
        tabindex="-1"
        aria-labelledby="pdfTitle"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        id="pdfTitle"
                        class="modal-title"
                    >
                        Document
                    </h5>

                    <a
                        id="downloadBtn"
                        href="#"
                        class="btn btn-sm me-2"
                        download
                    >
                        <i class="fa fa-download fs-3"></i>
                    </a>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>


                <div
                    id="viewerWrap"
                    class="position-relative"
                    style="min-height:70vh;"
                >

                    <div
                        class="position-absolute top-50 start-50 translate-middle"
                        id="viewerLoader"
                        aria-hidden="true"
                    >

                        <div
                            class="spinner-border"
                            role="status"
                            aria-label="Loading"
                        ></div>

                    </div>


                    <iframe
                        id="pdfFrame"
                        title="Embedded document viewer"
                        style="width:100%;height:70vh;border:0;"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>

                </div>

            </div>

        </div>

    </div>


    {{-- COURIER ADDRESS MAP MODAL --}}

    <div
        class="modal fade"
        id="mapModal"
        tabindex="-1"
        aria-labelledby="mapModalLabel"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-xl modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="mapModalLabel"
                    >
                        Factory &amp; Laboratory – GPS Location
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>


                <div class="modal-body p-0">

                    <iframe
                        width="100%"
                        height="600"
                        frameborder="0"
                        style="border:0"
                        src="https://www.google.com/maps?q=-26.414592,28.353447&hl=en&z=15&output=embed"
                        allowfullscreen
                        loading="lazy"
                        title="Betachem Factory and Laboratory Map"
                    ></iframe>

                </div>

            </div>

        </div>

    </div>

</footer>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('assets/js/scripts.js') }}"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ORIGINAL SHOP CLICK ANIMATION
    |--------------------------------------------------------------------------
    */

    const shopLink = document.querySelector('[data-shop-link="true"]');

    if (shopLink) {

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

    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT VIEWER
    |--------------------------------------------------------------------------
    */

    const triggers = document.querySelectorAll('.pdf-link, .doc-link');

    const modalEl = document.getElementById('pdfModal');

    if (
        !modalEl ||
        !triggers.length ||
        typeof bootstrap === 'undefined'
    ) {
        return;
    }

    const modal = new bootstrap.Modal(modalEl);

    const frame = document.getElementById('pdfFrame');
    const viewerWrap = document.getElementById('viewerWrap');
    const titleEl = document.getElementById('pdfTitle');
    const downloadBtn = document.getElementById('downloadBtn');


    function getViewerURL(fileUrl, override) {

        const url = String(fileUrl || '').trim();

        if (!url) {
            return '';
        }

        const match = url
            .split('?')[0]
            .split('#')[0]
            .match(/\.([a-z0-9]+)$/i);

        const ext = match ? match[1].toLowerCase() : '';

        const officeExts = [
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx'
        ];

        if (override === 'native') {
            return url;
        }

        if (override === 'office' || officeExts.includes(ext)) {

            return (
                'https://view.officeapps.live.com/op/embed.aspx?src='
                + encodeURIComponent(url)
            );

        }

        if (override === 'google' || ext === 'pdf') {

            return (
                'https://docs.google.com/viewer?embedded=true&url='
                + encodeURIComponent(url)
            );

        }

        return (
            'https://docs.google.com/viewer?embedded=true&url='
            + encodeURIComponent(url)
        );

    }


    function openDoc(targetEl) {

        const explicitUrl =
            targetEl.getAttribute('href') ||
            targetEl.getAttribute('data-doc');

        if (!explicitUrl || explicitUrl === '#') {
            return;
        }

        const title =
            targetEl.getAttribute('data-title') ||
            targetEl.textContent.trim() ||
            'Document';

        const override = (
            targetEl.getAttribute('data-viewer') || ''
        ).toLowerCase();

        viewerWrap.classList.add('loading');

        titleEl.textContent = title;

        downloadBtn.href = explicitUrl;

        frame.src = getViewerURL(
            explicitUrl,
            override
        );

        modal.show();

    }


    triggers.forEach(function (el) {

        el.addEventListener('click', function (event) {

            const isAnchor =
                el.tagName.toLowerCase() === 'a';

            const openInNew =
                isAnchor &&
                (
                    event.ctrlKey ||
                    event.metaKey ||
                    event.shiftKey ||
                    event.button === 1
                );

            if (openInNew) {
                return;
            }

            event.preventDefault();

            openDoc(el);

        });

    });


    frame.addEventListener('load', function () {

        viewerWrap.classList.remove('loading');

    });


    modalEl.addEventListener('hidden.bs.modal', function () {

        frame.src = '';

        viewerWrap.classList.add('loading');

    });

});
</script>


@stack('scripts')

</body>
</html>

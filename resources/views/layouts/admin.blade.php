<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Dashboard | Betachem')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('assets/header_logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --bc-purple: #2D208A;
            --bc-blue: #1745CA;
            --bc-light-blue: #1D88E9;
            --bc-text: #263953;
            --bc-muted: #718096;
            --bc-border: #e1e8f1;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Raleway', Arial, sans-serif;
            color: var(--bc-text);
            background: #f5f8fc;
        }

        .admin-main {
            flex: 1 0 auto;
            width: 100%;
        }

        /* =====================================================
           ADMIN HEADER — MATCHING ORIGINAL PHP
        ===================================================== */

        .betachem-admin-header {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            position: sticky;
            top: 0;
            z-index: 1030;
            border-bottom: 1px solid var(--bc-border);
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

        /* Account button */

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

        /* Account dropdown */

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

        /* =====================================================
           HEXAGON ADMIN NAVIGATION
        ===================================================== */

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

        .admin-mobile-toggle {
            display: none;
        }

        /* =====================================================
           ADMIN CONTENT
        ===================================================== */

        .admin-content {
            width: 100%;
            padding: 25px;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        .footer-custom {
            flex-shrink: 0;
            width: 100%;
            margin-top: auto;
            background: linear-gradient(
                135deg,
                #2D208A 0%,
                #1745CA 50%,
                #1D88E9 100%
            );
            color: #fff;
            position: relative;
            z-index: 2;
        }

        .footer-custom a {
            color: #fff;
            text-decoration: none;
        }

        .footer-custom a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .footer-custom h5,
        .footer-custom h6 {
            font-weight: 900;
            color: #fff;
        }

        .footer-custom p {
            line-height: 1.55;
            color: #fff;
        }

        .footer-logo {
            max-height: 180px;
        }

        .footer-hex {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: transform .3s ease, filter .3s ease;
        }

        .footer-hex svg {
            width: 95px;
            height: 65px;
            display: block;
        }

        .footer-hex:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 8px rgba(255,255,255,.45));
        }

        .footer-custom .btn-info {
            color: #fff !important;
            background: #0dcaf0;
            border-color: #0dcaf0;
            font-size: 11px;
            font-weight: 700;
        }

        .footer-custom .btn-info:hover {
            background: #0aa7c2;
            border-color: #0aa7c2;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,.25);
            padding: 12px 0;
            margin-top: 15px;
            font-size: 12px;
            text-align: center;
        }

        /* =====================================================
           MODALS
        ===================================================== */

        #pdfModal .modal-title,
        #mapModal .modal-title {
            color: #000;
        }

        #viewerWrap {
            background: #fff;
        }

        /* =====================================================
           TABLET
        ===================================================== */

        @media (max-width: 991.98px) {

            .admin-header-container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .admin-header-logo {
                max-height: 90px;
            }

            .admin-header-container > div {
                gap: 12px;
            }

            .hex-nav {
                gap: 10px;
            }

            .hex-nav .nav-item {
                margin: 0 -8px;
            }

            .hex-nav .nav-item:nth-child(even) {
                transform: translateY(25px);
            }

            .hex-nav .hex-btn svg {
                width: 75px;
                height: 55px;
            }

            .hex-nav .hex-btn svg text {
                font-size: 20px;
            }

            .betachem-admin-actions {
                margin-left: 5px;
            }
        }

        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .admin-header-container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .admin-header-logo {
                max-height: 65px;
            }

            .admin-label {
                display: none;
            }

            .admin-mobile-toggle {
                display: inline-block;
                border: 1px solid #1745CA;
                border-radius: 6px;
                background: #fff;
                color: #1745CA;
                padding: 7px 11px;
                font-size: 20px;
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

            .navbar .hex-nav .nav-item.active .hex-btn::after {
                color: #1745CA;
            }

            .betachem-admin-actions {
                margin-top: 10px;
                margin-bottom: 10px;
            }

            .admin-account-button {
                font-size: 12px;
                padding: 7px 9px;
            }

            .admin-content {
                padding: 12px;
            }

            .footer-logo {
                max-height: 110px;
                margin-bottom: 15px;
            }

            .footer-custom {
                padding-top: 20px;
            }
        }

        @media (max-width: 420px) {

            .admin-header-logo {
                max-height: 55px;
                max-width: 130px;
            }

            .admin-header-container {
                padding-left: 10px;
                padding-right: 10px;
            }

            .admin-header-container > div {
                gap: 6px;
            }

            .admin-account-button {
                padding: 6px 8px;
                font-size: 11px;
            }

            .admin-dropdown {
                min-width: 230px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

@php
    /*
    |--------------------------------------------------------------------------
    | Current Administrator
    |--------------------------------------------------------------------------
    */

    $adminUser = auth()->user();

    $adminRole = $adminUser->role ?? 'user';

    $roleNames = [
        'superuser' => 'Superuser',
        'administrator' => 'Administrator',
        'manager' => 'Manager',
        'user' => 'Staff User',
    ];

    $roleBadgeClasses = [
        'superuser' => 'role-superuser',
        'administrator' => 'role-administrator',
        'manager' => 'role-manager',
        'user' => 'role-user',
    ];

    $adminRoleName = $roleNames[$adminRole] ?? ucfirst($adminRole);
    $adminRoleBadge = $roleBadgeClasses[$adminRole] ?? 'role-user';

    $adminName = trim(
        ($adminUser->first_name ?? '') . ' ' .
        ($adminUser->last_name ?? '')
    );

    if ($adminName === '') {
        $adminName = $adminUser->name ?? 'Account';
    }

    $adminEmail = $adminUser->email ?? '';

    /*
    |--------------------------------------------------------------------------
    | Navigation Permissions
    |--------------------------------------------------------------------------
    */

    $adminPermissions = [
        'dashboard' => ['user', 'manager', 'administrator', 'superuser'],
        'categories' => ['manager', 'administrator', 'superuser'],
        'products' => ['user', 'manager', 'administrator', 'superuser'],
        'orders' => ['manager', 'administrator', 'superuser'],
        'customers' => ['manager', 'administrator', 'superuser'],
        'contact_messages' => ['manager', 'administrator', 'superuser'],
        'admin_users' => ['superuser'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Unread Contact Messages
    |--------------------------------------------------------------------------
    */

    $newContactMessages = (int) ($unreadMessages ?? 0);

    /*
    |--------------------------------------------------------------------------
    | Admin Navigation
    |--------------------------------------------------------------------------
    */

    $adminNavigation = [];

    if (in_array($adminRole, $adminPermissions['dashboard'], true)) {
        $adminNavigation[] = [
            'text' => 'Dashboard',
            'href' => '/admin',
            'icon' => 'fa-dashboard',
            'permission' => 'dashboard',
        ];
    }

    if (in_array($adminRole, $adminPermissions['categories'], true)) {
        $adminNavigation[] = [
            'text' => 'Categories',
            'href' => '/admin/categories',
            'icon' => 'fa-th-large',
            'permission' => 'categories',
        ];
    }

    if (in_array($adminRole, $adminPermissions['products'], true)) {
        $adminNavigation[] = [
            'text' => 'Products',
            'href' => '/admin/products',
            'icon' => 'fa-cubes',
            'permission' => 'products',
        ];
    }

    if (in_array($adminRole, $adminPermissions['orders'], true)) {
        $adminNavigation[] = [
            'text' => 'Orders',
            'href' => '/admin/orders',
            'icon' => 'fa-shopping-cart',
            'permission' => 'orders',
        ];
    }

    if (in_array($adminRole, $adminPermissions['customers'], true)) {
        $adminNavigation[] = [
            'text' => 'Customers',
            'href' => '/admin/customers',
            'icon' => 'fa-users',
            'permission' => 'customers',
        ];
    }

    if (in_array($adminRole, $adminPermissions['contact_messages'], true)) {
        $adminNavigation[] = [
            'text' => 'Messages',
            'href' => '/admin/contact-messages',
            'icon' => 'fa-envelope',
            'permission' => 'contact_messages',
        ];
    }

    if (in_array($adminRole, $adminPermissions['admin_users'], true)) {
        $adminNavigation[] = [
            'text' => 'Users',
            'href' => '/admin/users',
            'icon' => 'fa-shield',
            'permission' => 'admin_users',
        ];
    }
@endphp

<!-- =====================================================
     ADMIN HEADER
===================================================== -->

<nav class="navbar navbar-expand-lg betachem-admin-header">

    <div class="admin-header-container">

        <div class="d-flex w-100 justify-content-between align-items-center">

            <!-- LOGO -->

            <div>

                <a
                    class="navbar-brand d-flex align-items-center"
                    href="{{ route('admin.dashboard') }}"
                >

                    <img
                        src="{{ asset('assets/header_logo.png') }}"
                        alt="Betachem"
                        class="img-fluid admin-header-logo"
                    >

                    <span class="admin-label">
                        Admin
                    </span>

                </a>

            </div>

            <!-- ADMIN NAVIGATION -->

            <div class="text-center">

                <button
                    class="navbar-toggler admin-mobile-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#adminNavMenu"
                    aria-controls="adminNavMenu"
                    aria-expanded="false"
                    aria-label="Toggle admin navigation"
                >
                    <i class="fa fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse" id="adminNavMenu">

                    <ul class="text-center hex-nav navbar-nav">

                        @foreach($adminNavigation as $link)

                            @php
                                $linkPath = trim(parse_url($link['href'], PHP_URL_PATH) ?? '', '/');

                                $isActive = request()->is($linkPath)
                                    || request()->is($linkPath . '/*');

                                if ($link['permission'] === 'dashboard') {
                                    $isActive = request()->is('admin');
                                }
                            @endphp

                            <li class="nav-item {{ $isActive ? 'active' : '' }}">

                                <a
                                    href="{{ url($link['href']) }}"
                                    aria-label="{{ $link['text'] }}"
                                    class="hex-btn nav-link"
                                    title="{{ $link['text'] }}"
                                >

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 200 173.205"
                                        preserveAspectRatio="xMidYMid meet"
                                    >

                                        <path
                                            d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                                            fill="{{ $isActive ? '#1745CA' : '#fff' }}"
                                            stroke="{{ $isActive ? '#1745CA' : '#1D88E9' }}"
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
                                            fill="{{ $isActive ? '#fff' : '#2D208A' }}"
                                        >{{ $link['text'] }}</text>

                                    </svg>

                                    @if(
                                        $link['text'] === 'Messages' &&
                                        $newContactMessages > 0
                                    )

                                        <span
                                            class="position-absolute badge rounded-pill bg-danger"
                                            style="margin-left:-18px;margin-top:4px;font-size:10px;"
                                        >
                                            {{ $newContactMessages }}
                                        </span>

                                    @endif

                                </a>

                            </li>

                        @endforeach

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
                        src="{{ asset('assets/linkedin_icon.png') }}"
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

                        {{ $adminName }}

                    </button>

                    <ul class="dropdown-menu dropdown-menu-end admin-dropdown">

                        <!-- CURRENT ACCOUNT DETAILS -->

                        <li>

                            <div class="admin-dropdown-profile">

                                <div class="profile-name">

                                    <i class="fa fa-user-circle me-2"></i>

                                    {{ $adminName }}

                                </div>

                                @if($adminEmail !== '')

                                    <div class="profile-email">

                                        <i class="fa fa-envelope-o me-1"></i>

                                        {{ $adminEmail }}

                                    </div>

                                @endif

                                <span class="profile-role {{ $adminRoleBadge }}">

                                    <i class="fa fa-shield me-1"></i>

                                    {{ $adminRoleName }}

                                </span>

                            </div>

                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- ADMIN PORTAL -->

                        <li>

                            <a
                                class="dropdown-item"
                                href="{{ route('admin.dashboard') }}"
                            >

                                <i class="fa fa-dashboard text-primary"></i>

                                Admin Portal

                            </a>

                        </li>

                        <!-- STORE FRONT -->

                        <li>

                            <a
                                class="dropdown-item"
                                href="{{ route('shop.home') }}"
                            >

                                <i class="fa fa-shopping-bag text-primary"></i>

                                Store Front

                            </a>

                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- LOGOUT -->

                        @if(Route::has('shop.logout'))

                            <li>

                                <form method="POST" action="{{ route('shop.logout') }}">

                                    @csrf

                                    <button
                                        type="submit"
                                        class="dropdown-item text-danger"
                                    >

                                        <i class="fa fa-sign-out"></i>

                                        Logout

                                    </button>

                                </form>

                            </li>

                        @elseif(Route::has('logout'))

                            <li>

                                <form method="POST" action="{{ route('logout') }}">

                                    @csrf

                                    <button
                                        type="submit"
                                        class="dropdown-item text-danger"
                                    >

                                        <i class="fa fa-sign-out"></i>

                                        Logout

                                    </button>

                                </form>

                            </li>

                        @endif

                    </ul>

                </div>

            </div>

        </div>

    </div>

</nav>

<!-- =====================================================
     ADMIN PAGE CONTENT
===================================================== -->

<main class="admin-main">

    <div class="admin-content">

        @yield('content')

    </div>

</main>

<!-- =====================================================
     BETACHEM FOOTER
===================================================== -->

<footer class="footer-custom pt-4">

    <div class="container">

        <!-- DESKTOP -->

        <div class="row align-items-start d-none d-lg-flex">

            <div class="col-lg-1 text-center">

                <img
                    src="https://bchem.co.za/assets/header_logo.png"
                    alt="Betachem Logo"
                    class="img-fluid footer-logo"
                >

            </div>

            <div class="col-lg-3 text-center fs-6">

                <h5 class="mb-2">Numbers &amp; Email</h5>

                <p class="mb-1">
                    t:
                    <a href="tel:+27117828789">+27 11 782 8789</a>
                    <br>

                    f:
                    <a href="tel:+27118887458">+27 11 888 7458</a>
                    <br>

                    m:
                    <a href="tel:+27728045528">+27 72 804 5528</a>
                    <br>

                    <a href="mailto:contact@bchem.co.za">
                        contact@bchem.co.za
                    </a>
                </p>

            </div>

            <div class="col-lg-4">

                <h5 class="mb-3 text-center">Physical Addresses</h5>

                <div class="row">

                    <div class="col-sm-6 text-center">

                        <h6 class="text-uppercase">Administration Office</h6>

                        <p class="small mb-3">
                            31 Pafuri Road<br>
                            Emmarentia<br>
                            Johannesburg<br>
                            South Africa
                        </p>

                    </div>

                    <div class="col-sm-6 text-center">

                        <h6 class="text-uppercase">Factory &amp; Laboratory</h6>

                        <p class="small mb-3">
                            Plot 5, Producta<br>
                            Heidelberg<br>
                            Gauteng<br>

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

            <div class="col-lg-4 text-center">

                <a
                    href="https://bchem.co.za/resources"
                    class="footer-hex"
                    aria-label="Resources"
                >

                    <svg viewBox="0 0 200 173.205">

                        <path
                            d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                            fill="#fff"
                            stroke="#1D88E9"
                            stroke-width="6"
                        ></path>

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

                <div class="row mt-4">

                    <div class="col-4">

                        <a
                            href="https://bchem.co.za/assets/docs/Privacy%20Policy.pdf"
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
                            href="https://bchem.co.za/assets/docs/Map.pdf"
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

        <!-- TABLET -->

        <div class="row d-none d-md-flex d-lg-none">

            <div class="col-md-4 text-center">

                <h5>Numbers &amp; Email</h5>

                <p>
                    t: <a href="tel:+27117828789">+27 11 782 8789</a><br>
                    f: <a href="tel:+27118887458">+27 11 888 7458</a><br>
                    m: <a href="tel:+27728045528">+27 72 804 5528</a><br>
                    <a href="mailto:contact@bchem.co.za">contact@bchem.co.za</a>
                </p>

            </div>

            <div class="col-md-8">

                <h5 class="text-center">Physical Addresses</h5>

                <div class="row">

                    <div class="col-6 text-center">

                        <h6>Administration Office</h6>

                        <p class="small">
                            31 Pafuri Road<br>
                            Emmarentia<br>
                            Johannesburg<br>
                            South Africa
                        </p>

                    </div>

                    <div class="col-6 text-center">

                        <h6>Factory &amp; Laboratory</h6>

                        <p class="small">
                            Plot 5, Producta<br>
                            Heidelberg<br>
                            Gauteng<br>

                            <a href="https://www.google.com/maps?q=-26.414592,28.353447">
                                -26.414592, 28.353447
                            </a>
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- MOBILE -->

        <div class="d-block d-md-none text-center">

            <img
                src="https://bchem.co.za/assets/header_logo.png"
                alt="Betachem Logo"
                class="img-fluid footer-logo"
            >

            <h5>Numbers &amp; Email</h5>

            <p>
                t: <a href="tel:+27117828789">+27 11 782 8789</a><br>
                f: <a href="tel:+27118887458">+27 11 888 7458</a><br>
                m: <a href="tel:+27728045528">+27 72 804 5528</a><br>
                <a href="mailto:contact@bchem.co.za">contact@bchem.co.za</a>
            </p>

            <h5 class="mt-4">Physical Addresses</h5>

            <h6>Administration Office</h6>

            <p class="small">
                31 Pafuri Road<br>
                Emmarentia<br>
                Johannesburg<br>
                South Africa
            </p>

            <h6>Factory &amp; Laboratory</h6>

            <p class="small">
                Plot 5, Producta<br>
                Heidelberg<br>
                Gauteng<br>

                <a href="https://www.google.com/maps?q=-26.414592,28.353447">
                    -26.414592, 28.353447
                </a>
            </p>

            <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">

                <a
                    href="https://bchem.co.za/assets/docs/Privacy%20Policy.pdf"
                    class="btn btn-sm btn-info doc-link"
                    data-title="Privacy Policy"
                    data-viewer="google"
                >
                    Privacy Policy
                </a>

                <button
                    type="button"
                    class="btn btn-sm btn-info"
                    data-bs-toggle="modal"
                    data-bs-target="#mapModal"
                >
                    Courier Address
                </button>

                <a
                    href="https://bchem.co.za/assets/docs/Map.pdf"
                    class="btn btn-sm btn-info doc-link"
                    data-title="Betachem Map"
                    data-viewer="google"
                >
                    Betachem Map
                </a>

            </div>

        </div>

        <div class="footer-bottom">
            &copy; {{ date('Y') }} Betachem. All Rights Reserved.
        </div>

    </div>

    <!-- DOCUMENT VIEWER MODAL -->

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

                    <h5 id="pdfTitle" class="modal-title">
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
                        id="viewerLoader"
                        class="position-absolute top-50 start-50 translate-middle"
                    >
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
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

    <!-- FACTORY MAP MODAL -->

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

                    <h5 class="modal-title" id="mapModalLabel">
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
                    ></iframe>

                </div>

            </div>

        </div>

    </div>

</footer>

<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Document Viewer
    |--------------------------------------------------------------------------
    */

    const triggers = document.querySelectorAll('.doc-link');
    const modalElement = document.getElementById('pdfModal');

    if (!modalElement || !triggers.length) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const frame = document.getElementById('pdfFrame');
    const title = document.getElementById('pdfTitle');
    const download = document.getElementById('downloadBtn');
    const loader = document.getElementById('viewerLoader');

    function getViewerURL(fileUrl, override) {

        const url = String(fileUrl || '').trim();

        if (!url) {
            return '';
        }

        if (override === 'native') {
            return url;
        }

        if (override === 'office') {
            return 'https://view.officeapps.live.com/op/embed.aspx?src='
                + encodeURIComponent(url);
        }

        if (override === 'google') {
            return 'https://docs.google.com/viewer?embedded=true&url='
                + encodeURIComponent(url);
        }

        return url;
    }

    triggers.forEach(function (trigger) {

        trigger.addEventListener('click', function (event) {

            if (
                event.ctrlKey ||
                event.metaKey ||
                event.shiftKey ||
                event.button === 1
            ) {
                return;
            }

            event.preventDefault();

            const fileUrl = trigger.getAttribute('href');

            if (!fileUrl || fileUrl === '#') {
                return;
            }

            title.textContent =
                trigger.getAttribute('data-title') ||
                trigger.textContent.trim() ||
                'Document';

            download.href = fileUrl;

            if (loader) {
                loader.style.display = 'block';
            }

            frame.src = getViewerURL(
                fileUrl,
                (trigger.getAttribute('data-viewer') || '').toLowerCase()
            );

            modal.show();

        });

    });

    frame.addEventListener('load', function () {

        if (loader) {
            loader.style.display = 'none';
        }

    });

    modalElement.addEventListener('hidden.bs.modal', function () {

        frame.src = '';

        if (loader) {
            loader.style.display = 'block';
        }

    });

});
</script>

@stack('scripts')

</body>
</html>

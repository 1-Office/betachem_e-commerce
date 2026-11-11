<?php

/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * ADMIN AUTHENTICATION & ROLE PERMISSIONS
 * =========================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';


/*
|--------------------------------------------------------------------------
| Admin Login URL
|--------------------------------------------------------------------------
*/

function adminLoginUrl()
{
    return '/betachem/betachem_e-commerce/admin/index.php';
}


/*
|--------------------------------------------------------------------------
| Admin Roles
|--------------------------------------------------------------------------
*/

function adminRoles()
{
    return [
        'user',
        'manager',
        'administrator',
        'superuser'
    ];
}


/*
|--------------------------------------------------------------------------
| Check Admin Login
|--------------------------------------------------------------------------
*/

function isAdminLoggedIn()
{
    return (
        isset($_SESSION['user_id']) &&
        isset($_SESSION['user_role']) &&
        in_array(
            $_SESSION['user_role'],
            adminRoles(),
            true
        )
    );
}


/*
|--------------------------------------------------------------------------
| Current Admin Role
|--------------------------------------------------------------------------
*/

function currentAdminRole()
{
    return $_SESSION['user_role'] ?? null;
}


/*
|--------------------------------------------------------------------------
| Role Level
|--------------------------------------------------------------------------
*/

function adminRoleLevel($role = null)
{
    $role = $role ?? currentAdminRole();

    $levels = [
        'user'          => 1,
        'manager'       => 2,
        'administrator' => 3,
        'superuser'     => 4
    ];

    return $levels[$role] ?? 0;
}


/*
|--------------------------------------------------------------------------
| Check Minimum Role
|--------------------------------------------------------------------------
*/

function hasAdminRole($requiredRole)
{
    if (!isAdminLoggedIn()) {
        return false;
    }

    return adminRoleLevel() >= adminRoleLevel($requiredRole);
}


/*
|--------------------------------------------------------------------------
| Require Admin Login
|--------------------------------------------------------------------------
*/

function requireAdmin()
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . adminLoginUrl());
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Require User
|--------------------------------------------------------------------------
*/

function requireUserAccess()
{
    requireAdmin();

    if (!hasAdminRole('user')) {
        accessDenied();
    }
}


/*
|--------------------------------------------------------------------------
| Require Manager
|--------------------------------------------------------------------------
*/

function requireManager()
{
    requireAdmin();

    if (!hasAdminRole('manager')) {
        accessDenied();
    }
}


/*
|--------------------------------------------------------------------------
| Require Administrator
|--------------------------------------------------------------------------
*/

function requireAdministrator()
{
    requireAdmin();

    if (!hasAdminRole('administrator')) {
        accessDenied();
    }
}


/*
|--------------------------------------------------------------------------
| Require Superuser
|--------------------------------------------------------------------------
*/

function requireSuperuser()
{
    requireAdmin();

    if (currentAdminRole() !== 'superuser') {
        accessDenied();
    }
}


/*
|--------------------------------------------------------------------------
| Permission System
|--------------------------------------------------------------------------
|
| This controls individual areas of the admin panel.
|
*/

function adminPermissions()
{
    return [

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        'dashboard' => [
            'user',
            'manager',
            'administrator',
            'superuser'
        ],


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        'categories' => [
            'manager',
            'administrator',
            'superuser'
        ],


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        'products' => [
            'user',
            'manager',
            'administrator',
            'superuser'
        ],


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        'orders' => [
            'manager',
            'administrator',
            'superuser'
        ],


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        'customers' => [
            'manager',
            'administrator',
            'superuser'
        ],

        'contact_messages' => [
    'manager',
    'administrator',
    'superuser'
],
        /*
        |--------------------------------------------------------------------------
        | Admin Users
        |--------------------------------------------------------------------------
        */

        'admin_users' => [
            'superuser'
        ]

    ];
}


/*
|--------------------------------------------------------------------------
| Check Permission
|--------------------------------------------------------------------------
*/

function hasPermission($permission)
{
    if (!isAdminLoggedIn()) {
        return false;
    }

    $permissions = adminPermissions();

    if (!isset($permissions[$permission])) {
        return false;
    }

    return in_array(
        currentAdminRole(),
        $permissions[$permission],
        true
    );
}


/*
|--------------------------------------------------------------------------
| Require Permission
|--------------------------------------------------------------------------
*/

function requirePermission($permission)
{
    requireAdmin();

    if (!hasPermission($permission)) {
        accessDenied();
    }
}


/*
|--------------------------------------------------------------------------
| Access Denied
|--------------------------------------------------------------------------
*/

function accessDenied()
{
    http_response_code(403);

    $role = adminRoleName();

    die(
        '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Access Denied | Betachem</title>

            <link
                href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
                rel="stylesheet"
            >

            <style>
                body {
                    background: #f4f7fb;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: Arial, sans-serif;
                }

                .card {
                    max-width: 500px;
                    width: 100%;
                    border: 0;
                    border-radius: 14px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.08);
                }

                .icon {
                    font-size: 52px;
                }
            </style>
        </head>

        <body>

            <div class="card p-5 text-center">

                <div class="icon mb-3">
                    🔒
                </div>

                <h2 class="fw-bold">
                    Access Denied
                </h2>

                <p class="text-muted mb-2">
                    You do not have permission to access this area.
                </p>

                <p class="small text-muted">
                    Current role:
                    <strong>' . e($role) . '</strong>
                </p>

                <a
                    href="index.php"
                    class="btn btn-primary"
                >
                    Return to Dashboard
                </a>

            </div>

        </body>
        </html>'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Role Names
|--------------------------------------------------------------------------
*/

function adminRoleName($role = null)
{
    $role = $role ?? currentAdminRole();

    $names = [
        'superuser'     => 'Superuser',
        'administrator' => 'Administrator',
        'manager'       => 'Manager',
        'user'          => 'User',
        'customer'      => 'Customer'
    ];

    return $names[$role] ?? 'Unknown';
}


/*
|--------------------------------------------------------------------------
| Role Badge
|--------------------------------------------------------------------------
*/

function adminRoleBadge($role = null)
{
    $role = $role ?? currentAdminRole();

    $badges = [
        'superuser'     => 'bg-danger',
        'administrator' => 'bg-primary',
        'manager'       => 'bg-success',
        'user'          => 'bg-secondary',
        'customer'      => 'bg-light text-dark'
    ];

    return $badges[$role] ?? 'bg-secondary';
}
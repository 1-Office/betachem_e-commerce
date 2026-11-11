<?php
/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * AUTHENTICATION AND ACCESS CONTROL
 * =========================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Require a logged-in user.
 * Guests are sent to the official website login page.
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /betachem/betachem_e-commerce/login.php');
        exit;
    }
}

/**
 * Require administrator access.
 * Unauthorized users are sent to the public shop,
 * not back to the protected admin dashboard.
 */
function requireAdmin()
{
    if (!isLoggedIn()) {
        header('Location: /betachem/betachem_e-commerce/login.php');
        exit;
    }

    if (!isAdmin()) {
        header('Location: /betachem/betachem_e-commerce/shop.php');
        exit;
    }
}
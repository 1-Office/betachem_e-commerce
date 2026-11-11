<?php
/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * GENERAL FUNCTIONS
 * =========================================================
 */

/**
 * Escape HTML output safely.
 */
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/**
 * Redirect to another page.
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/**
 * Format South African currency.
 */
function money($amount)
{
    return "R " . number_format(
        (float) $amount,
        2,
        ".",
        ","
    );
}


/**
 * Generate a URL-friendly slug.
 */
function createSlug($text)
{
    $text = strtolower(trim($text));

    $text = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $text
    );

    $text = trim($text, '-');

    return $text;
}


/**
 * Generate an order number.
 */
function generateOrderNumber()
{
    return "BET-" . date("Ymd") . "-" . strtoupper(
        substr(md5(uniqid(mt_rand(), true)), 0, 6)
    );
}


/**
 * Check whether a customer is logged in.
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}


/**
 * Check whether the logged-in user is an admin.
 */
function isAdmin()
{
    return isset($_SESSION['user_role'])
        && $_SESSION['user_role'] === 'admin';
}


/**
 * Get the current logged-in user's ID.
 */
function currentUserId()
{
    return $_SESSION['user_id'] ?? null;
}
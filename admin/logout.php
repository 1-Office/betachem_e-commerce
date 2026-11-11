<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Clear Session
|--------------------------------------------------------------------------
*/

$_SESSION = [];

session_destroy();


/*
|--------------------------------------------------------------------------
| Redirect to Admin Login
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';

header('Location: ' . $baseUrl . '/admin/login.php');
exit;
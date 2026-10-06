<?php
/** Logout. */
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    logActivity('logout');
}
$_SESSION = [];
session_destroy();
header('Location: /login.php');
exit;

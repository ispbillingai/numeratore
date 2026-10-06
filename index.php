<?php
/** Home: to the login, or to the user's start page. */
require_once __DIR__ . '/includes/functions.php';

$user = getCurrentUser();
header('Location: ' . ($user ? homeUrl($user['role']) : '/login.php'));
exit;

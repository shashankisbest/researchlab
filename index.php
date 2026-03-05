<?php
require_once 'includes/auth.php';

// If the user is already logged in, send them to the dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Otherwise, redirect to the login page
header("Location: login.php");
exit;
?>
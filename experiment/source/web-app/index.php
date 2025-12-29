<?php
/**
 * Index page - Redirects to login or dashboard
 */
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>




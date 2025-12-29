<?php
/**
 * Application Configuration
 * HEC-Sense AI Farm App
 */

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application Settings
define('APP_NAME', 'HEC-Sense AI Farm App');
define('APP_VERSION', '1.0.0');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? 'user';
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

function formatDistance($distance) {
    return number_format($distance, 2) . 'm';
}

function getZoneColor($zone) {
    switch($zone) {
        case 'SAFE': return '#10b981'; // Green
        case 'WARNING': return '#f59e0b'; // Orange
        case 'DANGER': return '#ef4444'; // Red
        default: return '#6b7280'; // Gray
    }
}

function getZoneBadge($zone) {
    $colors = [
        'SAFE' => 'success',
        'WARNING' => 'warning',
        'DANGER' => 'danger'
    ];
    $color = $colors[$zone] ?? 'secondary';
    return "<span class='badge badge-{$color}'>{$zone}</span>";
}




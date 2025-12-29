<?php
/**
 * Setup Admin User Script
 * Run this once to create the default admin user
 */
require_once 'config/database.php';

$db = getDB();

// Create default admin user
$username = 'admin';
$email = 'admin@hec-sense.local';
$password = 'admin'; // Default password: admin
$full_name = 'System Administrator';
$role = 'admin';

$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing admin
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, role = ?, is_active = 1 WHERE id = ?");
        $stmt->execute([$password_hash, $role, $existing['id']]);
        echo "✅ Admin user updated successfully!\n";
        echo "Username: $username\n";
        echo "Password: $password\n";
    } else {
        // Create new admin
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, full_name, role, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$username, $email, $password_hash, $full_name, $role]);
        echo "✅ Admin user created successfully!\n";
        echo "Username: $username\n";
        echo "Password: $password\n";
    }
    
    echo "\n⚠️  IMPORTANT: Change the default password after first login!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>


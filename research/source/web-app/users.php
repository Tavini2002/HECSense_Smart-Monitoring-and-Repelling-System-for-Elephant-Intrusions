<?php
require_once 'config/config.php';
require_once 'config/database.php';

requireLogin();

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$db = getDB();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'user';
        
        if ($username && $email && $password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $db->prepare("
                    INSERT INTO users (username, email, password_hash, full_name, role, is_active)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$username, $email, $password_hash, $full_name, $role]);
                $message = "User created successfully!";
            } catch (Exception $e) {
                $error = "Error creating user: " . $e->getMessage();
            }
        } else {
            $error = "Please fill all required fields.";
        }
    } elseif ($action === 'toggle') {
        $user_id = (int)$_POST['user_id'];
        $is_active = (int)$_POST['is_active'];
        try {
            $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$is_active, $user_id]);
            $message = "User status updated!";
        } catch (Exception $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
}

// Get all users
$stmt = $db->query("SELECT id, username, email, full_name, role, is_active, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #667eea; --secondary: #764ba2; }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .sidebar .nav-link { color: #4b5563; padding: 12px 20px; border-radius: 8px; margin: 5px 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-users me-2"></i>User Management</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Create User Form -->
                <div class="form-container">
                    <h5 class="mb-3"><i class="fas fa-user-plus me-2"></i>Create New User</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Users Table -->
                <div class="table-container">
                    <h5 class="mb-3">All Users</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'secondary'; ?>">
                                                <?php echo strtoupper($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $user['is_active'] ? 0 : 1; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>">
                                                    <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                                    <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




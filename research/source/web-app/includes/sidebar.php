<div class="col-md-2 sidebar p-0">
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-chart-line me-2"></i>Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'detections.php' ? 'active' : ''; ?>" href="detections.php">
            <i class="fas fa-search me-2"></i>Detections
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sessions.php' ? 'active' : ''; ?>" href="sessions.php">
            <i class="fas fa-video me-2"></i>Sessions
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'alerts.php' ? 'active' : ''; ?>" href="alerts.php">
            <i class="fas fa-bell me-2"></i>Alerts
        </a>
        <?php if (isAdmin()): ?>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
            <i class="fas fa-users me-2"></i>Users
        </a>
        <?php endif; ?>
    </nav>
</div>




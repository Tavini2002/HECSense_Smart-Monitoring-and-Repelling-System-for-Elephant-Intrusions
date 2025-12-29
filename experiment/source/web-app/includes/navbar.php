<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            <i class="fas fa-elephant me-2"></i><?php echo APP_NAME; ?>
        </span>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>
</nav>




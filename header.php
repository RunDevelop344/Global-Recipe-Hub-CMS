<nav>
    <a href="index.php">Home</a>

    <!-- Admin Dashboard only for admins -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="dashboard.php">Dashboard</a>
    <?php endif; ?>

    <!-- Logout if logged in -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
</nav>

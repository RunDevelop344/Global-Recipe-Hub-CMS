<?php
// Ensure session is active for navigation controls
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Recipe Hub</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<header>
    <h1>Global Recipe Hub</h1>

    <nav>
        <a href="index.php">Home</a>

        <!-- Show Add Recipe only for admins -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="create.php">Add Recipe</a>
            <a href="dashboard.php">Dashboard</a>
        <?php endif; ?>

        <!-- Show Logout if logged in -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>

        <!-- Show Login if not logged in -->
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
<hr>

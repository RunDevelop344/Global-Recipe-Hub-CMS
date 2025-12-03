<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p style='color:red; text-align:center;'>Access denied. Admins only.</p>";
    exit;
}
?>

<?php
require 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ADMIN PROTECTION
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    exit;
}

?>

<div class="dashboard-container">

    <div class="dashboard-title">Admin Dashboard</div>

    <div class="admin-grid">

        <div class="admin-card">
            <!-- <span class="admin-icon"></span> -->
            <a href="create.php">Add New Recipe</a>
        </div>

        <div class="admin-card">
            <!-- <span class="admin-icon"></span> -->
            <a href="edit.php">Edit Recipes</a>
        </div>

        <div class="admin-card">
            <!-- <span class="admin-icon"></span> -->
            <a href="moderate_comments.php">Moderate Comments</a>
        </div>

        <div class="admin-card">
            <a href="categories.php">Manage Categories</a>
        </div>


        <div class="admin-card">
            <!-- <span class="admin-icon"></span> -->
            <a href="users.php">Manage Users</a>
        </div>

    </div>

</div>

<?php include 'footer.php'; ?>

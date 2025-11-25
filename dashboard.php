<?php
require 'admin.php';
require 'header.php';
?>

<div class="dashboard-container">

    <div class="dashboard-title">Admin Dashboard</div>

    <div class="admin-grid">

        <div class="admin-card">
            <!-- <span class="admin-icon"></span> -->
            <a href="create.php">Add New Recipe</a>
        </div>

        <div class="admin-card">
            <!-- <span class="admin-icon">📚</span> -->
            <a href="edit.php">Edit Recipes</a>
        </div>

        <div class="admin-card">
            <!-- <span class="admin-icon">📝</span> -->
            <a href="manage_comments.php">Moderate Comments</a>
        </div>

        <div class="admin-card">
            <!-- <span class="admin-icon">👥</span> -->
            <a href="users.php">Manage Users</a>
        </div>

    </div>

</div>

<?php include 'footer.php'; ?>

<?php
require 'admin.php';
require 'header.php';
require 'connect.php';
?>

<h1>Admin Dashboard</h1>

<h2>Recipe Management</h2>
<ul>
    <li><a href="create.php">➕ Add New Recipe</a></li>
    <li><a href="dashboard_recipes.php">📚 Edit Existing Recipes</a></li>
</ul>

<?php include 'footer.php'; ?>

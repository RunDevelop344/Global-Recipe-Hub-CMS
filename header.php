<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
 require_once 'connect.php';

// Fetch categories for dropdown menu
$categoryMenu = $db->query("
    SELECT category_id, category_name 
    FROM categories 
    ORDER BY category_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
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
    <h1> Global Recipe Hub</h1>

<nav>

    <a href="index.php">Home</a>

    <!-- CATEGORY DROPDOWN -->
<div class="dropdown">
    <button class="dropbtn" onclick="toggleCategory(event)">Categories</button>

    <div class="dropdown-content" id="catMenu">
        <?php foreach ($categoryMenu as $cat): ?>
            <a href="category.php?cat=<?= $cat['category_id'] ?>">
                <?= htmlspecialchars($cat['category_name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>


    <!-- CLICK DROPDOWN -->
    <div class="dropdown">
        <button class="dropbtn" onclick="toggleMenu(event)">Dashboard</button>

        <div class="dropdown-content" id="dashMenu">
            <a href="dashboard.php">Admin Home</a>
            <a href="create.php">Add Recipe</a>
            <a href="edit.php">Edit Recipes</a>
            <a href="moderate_comments.php">Moderate Comments</a>
        </div>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>

</nav>
   

</header>
<hr>

<script>
function toggleMenu(event) {
    event.stopPropagation(); // prevent closing immediately
    const menu = document.getElementById("dashMenu");
    if (menu) {
        menu.classList.toggle("show");
    }
}

document.addEventListener("click", function(event) {
    const menu = document.getElementById("dashMenu");
    if (menu && !event.target.closest(".dropdown")) {
        menu.classList.remove("show");
    }
});
</script>

<script>
function toggleCategory(event) {
    event.stopPropagation();
    const menu = document.getElementById("catMenu");
    if (menu) {
        menu.classList.toggle("show");
    }
}

document.addEventListener("click", function(event) {
    const menu = document.getElementById("catMenu");
    if (menu && !event.target.closest(".dropdown")) {
        menu.classList.remove("show");
    }
});
</script>



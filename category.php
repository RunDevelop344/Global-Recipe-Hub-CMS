<?php
// require 'connect.php';
require 'header.php';

$catId = $_GET['cat'] ?? null;

if (!$catId) {
    echo "<h2>Invalid category</h2>";
    include 'footer.php';
    exit;
}

// Fetch category name
$catStmt = $db->prepare("SELECT category_name FROM categories WHERE category_id = ?");
$catStmt->execute([$catId]);
$categoryName = $catStmt->fetchColumn();

if (!$categoryName) {
    echo "<h2>Category not found</h2>";
    include 'footer.php';
    exit;
}

echo "<h2>Recipes in: " . htmlspecialchars($categoryName) . "</h2>";

// Fetch recipes for this category
$recipeStmt = $db->prepare("
    SELECT MIN(meal_id) AS meal_id, meal_name, image_url
    FROM meals
    WHERE category_id = ?
    GROUP BY meal_name, image_url
    ORDER BY meal_name ASC
");
$recipeStmt->execute([$catId]);
$recipes = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="recipes" style="display:flex; flex-wrap:wrap; gap:15px;">
<?php if (!empty($recipes)): ?>
    <?php foreach ($recipes as $r): ?>
        <div class="recipe-card" style="border:1px solid #ccc; padding:10px; width:220px; border-radius:8px;">
            <img src="<?= htmlspecialchars($r['image_url']) ?>" width="200" height="150">
            <h3><?= htmlspecialchars($r['meal_name']) ?></h3>
            <a href="post.php?id=<?= $r['meal_id'] ?>">View</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No recipes found in this category.</p>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>

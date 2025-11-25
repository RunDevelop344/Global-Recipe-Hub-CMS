<?php
require 'admin.php';
require 'connect.php';
require 'header.php';

$statement = $db->query("SELECT meal_id, meal_name FROM meals ORDER BY meal_name ASC");
$recipes = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="recipe-list-container">
    <h2>Edit Existing Recipes</h2>

    <?php if (empty($recipes)): ?>
        <p>No recipes found.</p>
    <?php else: ?>
        <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-item">
                <?= htmlspecialchars($recipe['meal_name']) ?>
                <a href="edit.php?id=<?= $recipe['meal_id'] ?>">Edit</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

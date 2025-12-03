<?php
session_start();
require 'connect.php';
require 'header.php';

// ADMIN PROTECTION
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    exit;
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Fetch all recipes for dropdown
$recipeStmt = $db->query("
    SELECT MIN(meal_id) AS meal_id, meal_name
    FROM meals
    GROUP BY meal_name
    ORDER BY meal_name ASC
");

$recipes = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);

// Selected recipe
$selectedRecipe = $_GET['recipe'] ?? null;

// Moderate actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['action'])) {
    $commentId = (int) $_POST['comment_id'];
    $action = $_POST['action'];

    if ($action === 'delete') {
        $del = $db->prepare("DELETE FROM comments WHERE comment_id = ?");
        $del->execute([$commentId]);
    } elseif ($action === 'hide') {
        $hide = $db->prepare("UPDATE comments SET visible = 0 WHERE comment_id = ?");
        $hide->execute([$commentId]);
    }

    header("Location: moderate_comments.php?recipe=$selectedRecipe");
    exit;
}

// Fetch comments for selected recipe
$comments = [];
if ($selectedRecipe) {
    $commentStmt = $db->prepare("
        SELECT c.comment_id, c.comment, c.visible, u.username, m.meal_name
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        JOIN meals m ON c.meal_id = m.meal_id
        WHERE c.meal_id = ?
        ORDER BY c.created_at DESC
    ");
    $commentStmt->execute([$selectedRecipe]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h2>Moderate Comments</h2>

<!-- Recipe Dropdown -->
<form method="GET" action="moderate_comments.php" style="max-width:400px; margin:0 auto;">
    <label>Select a Recipe:</label>
    <select name="recipe" required>
        <option value="">-- Choose Recipe --</option>
        <?php foreach ($recipes as $recipe): ?>
            <option value="<?= $recipe['meal_id'] ?>" <?= ($selectedRecipe == $recipe['meal_id'] ? 'selected' : '') ?>>
                <?= htmlspecialchars($recipe['meal_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Load Comments</button>
</form>

<hr>

<?php if ($selectedRecipe && empty($comments)): ?>
    <p style="text-align:center;">No comments found for this recipe.</p>
<?php endif; ?>

<?php if (!empty($comments)): ?>
    <div class="recipe-list-container">
        <h3>Comments for: <?= htmlspecialchars($comments[0]['meal_name']) ?></h3>

        <?php foreach ($comments as $comment): ?>
            <div class="comment-box">
                <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                    <?= $comment['visible'] ? htmlspecialchars($comment['comment']) : '<em>Hidden</em>' ?>
                </p>

                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                    <input type="hidden" name="recipe" value="<?= $selectedRecipe ?>">

                    <button type="submit" name="action" value="delete"
                        onclick="return confirm('Delete this comment?');">Delete</button>

                    <?php if ($comment['visible']): ?>
                        <button type="submit" name="action" value="hide">Hide</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>

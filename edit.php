<?php 
require_once 'admin.php'; 
require_once 'connect.php'; 
require 'header.php'; 
?>

<?php
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get recipe ID
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p>Invalid recipe ID.</p>";
    include 'footer.php';
    exit;
}

// Fetch recipe
$statement = $db->prepare("SELECT * FROM meals WHERE meal_id = ?");
$statement->execute([$id]);
$recipe = $statement->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "<p>Recipe not found.</p>";
    include 'footer.php';
    exit;
}

$category_id = $recipe['category_id'];

// DELETE RECIPE
if (isset($_POST['delete'])) {
    $delete = $db->prepare("DELETE FROM meals WHERE meal_id = ?");
    $delete->execute([$id]);
    header("Location: index.php");
    exit;
}

// UPDATE RECIPE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name']) && !isset($_POST['moderate_action'])) {

    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category'];
    $region = trim($_POST['region']);
    $instructions = trim($_POST['instructions']);
    $image = trim($_POST['image']);

    $update = $db->prepare("
        UPDATE meals 
        SET meal_name=?, category_id=?, region=?, instructions=?, image_url=? 
        WHERE meal_id=?
    ");
    $update->execute([$name, $category_id, $region, $instructions, $image, $id]);

    header("Location: index.php");
    exit;
}

// COMMENT MODERATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['moderate_action'], $_POST['comment_id'])) {
    
    $comment_id = (int)$_POST['comment_id'];
    $action = $_POST['moderate_action'];

    if ($action === 'delete') {
        $statement = $db->prepare("DELETE FROM comments WHERE comment_id = ?");
        $statement->execute([$comment_id]);

    } elseif ($action === 'hide') {
        $statement = $db->prepare("UPDATE comments SET visible = 0 WHERE comment_id = ?");
        $statement->execute([$comment_id]);
    }

    header("Location: edit.php?id=$id");
    exit;
}

// FETCH COMMENTS (using role = 'user' rather than is_admin)
$commentStatement = $db->prepare("
    SELECT c.comment_id, c.user_id, c.comment, c.visible, u.username 
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.meal_id = ? AND u.role = 'user'
    ORDER BY c.created_at DESC
");
$commentStatement->execute([$id]);
$comments = $commentStatement->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Edit Recipe</h2>

<form method="POST">
    <label>Recipe Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($recipe['meal_name']) ?>"><br><br>

    <label>Category:</label><br>
    <select id="category" name="category">
        <option value="">- Category -</option>

        <?php for ($i = 1; $i <= 14; $i++): ?>
            <option value="<?= $i ?>" <?= ($category_id == $i ? 'selected' : '') ?>>
                Category <?= $i ?>
            </option>
        <?php endfor; ?>
    </select><br><br>

    <label>Region:</label>
    <input type="text" name="region" value="<?= htmlspecialchars($recipe['region']) ?>"><br><br>

    <label>Instructions:</label><br>
    <textarea name="instructions" rows="5" cols="40"><?= htmlspecialchars($recipe['instructions']) ?></textarea><br><br>

    <label>Image URL:</label><br>
    <input type="text" name="image" value="<?= htmlspecialchars($recipe['image_url']) ?>"><br><br>

    <button type="submit">Update Recipe</button>
</form>

<form method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
    <button type="submit" name="delete">Delete Recipe</button>
</form>

<h2>Moderate Comments</h2>

<?php if (empty($comments)): ?>
    <p>No comments to moderate.</p>
<?php else: ?>
    <?php foreach ($comments as $comment): ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                <?= $comment['visible'] ? htmlspecialchars($comment['comment']) : '<em>Hidden</em>' ?>
            </p>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">

                <button name="moderate_action" value="delete" onclick="return confirm('Delete this comment?');">Delete</button>
                <button name="moderate_action" value="hide">Hide</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>

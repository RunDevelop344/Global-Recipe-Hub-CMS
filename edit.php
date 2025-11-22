
<?php
require_once 'admin.php'; 
require_once 'connect.php'; 
require 'header.php'; 

?>



<?php
$id = $_GET['id'] ?? null;
$statement = $db->prepare("SELECT * FROM meals WHERE meal_id = ?");
$statement->execute([$id]);
$recipe = $statement->fetch(PDO::FETCH_ASSOC);
$category_id = $recipe['category_id'];

if (isset($_POST['delete'])) {
    $delete = $db->prepare("DELETE FROM meals WHERE meal_id = ?");
    $delete->execute([$id]);
    header("Location: index.php"); // redirect after deletion
    exit;
}

// Update recipe ONLY when update form is submitted
if (isset($_POST['name']) && !isset($_POST['moderate_action']) && !isset($_POST['delete'])) {

    $name = $_POST['name'];
    $category_id = $_POST['category'];
    $region = htmlspecialchars($_POST['region']);
    $instructions = $_POST['instructions'];
    $image = $_POST['image'];

    $update = $db->prepare("UPDATE meals SET meal_name=?, category_id=?, region=?, instructions=?, image_url=? WHERE meal_id=?");
    $update->execute([$name, $category_id, $region, $instructions, $image, $id]);

    header("Location: index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['moderate_action'], $_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];
    $action = $_POST['moderate_action'];

    switch ($action) {
        case 'delete':
            $statement = $db->prepare("DELETE FROM comments WHERE comment_id = ?");
            $statement->execute([$comment_id]);
            break;

        case 'hide':
            $statement = $db->prepare("UPDATE comments SET visible = 0 WHERE comment_id = ?");
            $statement->execute([$comment_id]);
            break;

    }
    header("Location: edit.php?id=$id"); // Refresh page after moderation
    exit;
}

// Fetch comments by non-admins for this recipe
$commentStatement = $db->prepare("
    SELECT c.comment_id, c.user_id, c.comment, c.visible, u.username 
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.meal_id = ? AND u.is_admin = 0
    ORDER BY c.created_at DESC
");
$commentStatement->execute([$id]);
$comments = $commentStatement->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Edit Recipe</h2>
<form method="POST">
    <label>Recipe Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($recipe['meal_name'] ?? '') ?>"><br><br>

    <label><strong>Category:</strong></label><br>
    <select id="category" name="category" required style="width:100%; padding:8px;">
        <option value="">- Category -</option>
        <option value="1" <?php if($category_id==1) echo 'selected'; ?>>Dessert</option>
        <option value="2" <?php if($category_id==2) echo 'selected'; ?>>Beef</option>
        <option value="3" <?php if($category_id==3) echo 'selected'; ?>>Vegetarian</option>
        <option value="4" <?php if($category_id==4) echo 'selected'; ?>>Chicken</option>
        <option value="5" <?php if($category_id==5) echo 'selected'; ?>>Side</option>
        <option value="6" <?php if($category_id==6) echo 'selected'; ?>>Seafood</option>
        <option value="7" <?php if($category_id==7) echo 'selected'; ?>>Pork</option>
        <option value="8" <?php if($category_id==8) echo 'selected'; ?>>Miscellaneous</option>
        <option value="9" <?php if($category_id==9) echo 'selected'; ?>>Breakfast</option>
        <option value="10" <?php if($category_id==10) echo 'selected'; ?>>Starter</option>
        <option value="11" <?php if($category_id==11) echo 'selected'; ?>>Pasta</option>
        <option value="12" <?php if($category_id==12) echo 'selected'; ?>>Vegan</option>
        <option value="13" <?php if($category_id==13) echo 'selected'; ?>>Lamb</option>
        <option value="14" <?php if($category_id==14) echo 'selected'; ?>>Goat</option>
    </select><br><br>

    
    <label>Region:</label>
    <input type="text" name="region" value="<?= htmlspecialchars($recipe['region'] ?? '') ?>"><br><br>


    <label>Instructions:</label><br>
    <textarea name="instructions" rows="5" cols="40"><?= htmlspecialchars($recipe['instructions']) ?></textarea><br><br>

    <label>Image URL:</label><br>
    <input type="text" name="image" value="<?= htmlspecialchars($recipe['image_url']) ?>"><br><br>

    <button type="submit">Update Recipe</button>
</form>

    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
    <button type="submit" name="delete" >Delete Recipe</button>
</form>

<h2>Moderate Comments</h2>

<?php if (empty($comments)): ?>
    <p>No comments to moderate.</p>
<?php else: ?>
    <?php foreach ($comments as $comment): ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> 
                <?= $comment['visible'] ? htmlspecialchars($comment['comment']) : '<em>Hidden</em>'; ?>
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

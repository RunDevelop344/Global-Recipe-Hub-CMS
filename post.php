<?php 


include 'header.php'; 
require 'connect.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message="";

$id = $_GET['id'] ?? null; 
if (!$id) {  
    echo "<p>Invalid recipe ID.</p>"; 
    include 'footer.php'; 
    exit; 
} 

$message="";
?>
<?php


$statement = $db->prepare("SELECT meals.*, categories.category_name AS category 
FROM meals JOIN categories ON meals.category_id = categories.category_id WHERE meals.meal_id = ?"); 
$statement->execute([$id]); $recipe = $statement->fetch(PDO::FETCH_ASSOC); 
if (!$recipe) { echo "<p>Recipe not found.</p>"; exit; } 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 

    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'] ?? null;
    $captcha_input = trim($_POST['captcha'] ?? '');

    // Check CAPTCHA first
    if ($captcha_input !== ($_SESSION['captcha_code'] ?? '')) {
        $message = "<p style='color:red;'>Incorrect CAPTCHA. Please try again.</p>";
    }
    else if ($user_id && !empty($comment)) {

        // CAPTCHA OK → save comment
        $statement = $db->prepare("INSERT INTO comments (user_id, meal_id, comment) VALUES (?, ?, ?)");
        $statement->execute([$user_id, $id, $comment]);

        $message = "<p style='color:green;'>Comment added successfully!</p>";
    } 
    else {
        $message = "<p style='color:red;'>Please log in and enter a comment.</p>";
    }
}

?> 
<h2><?= htmlspecialchars($recipe['meal_name']) ?></h2> <img src="<?= htmlspecialchars($recipe['image_url']) ?>" width="300"> 
<p><strong>Category:</strong> <?= htmlspecialchars($recipe['category']) ?></p> 
<p><strong>Created at:</strong> <?= htmlspecialchars($recipe['created_at']) ?></p> 
<p><strong>Updated at:</strong> <?= htmlspecialchars($recipe['updated_at']) ?></p> 
<p><strong>Instructions:</strong></p> <p><?= nl2br(htmlspecialchars($recipe['instructions'])) ?></p> 
<a href="index.php">← Back to all recipes</a> 

<h3>Comments</h3> 
<?php $comments = $db->prepare("SELECT users.username, comments.comment 
FROM comments JOIN users ON comments.user_id = users.user_id WHERE comments.meal_id = ? 
ORDER BY comments.comment_id DESC "); 
$comments->execute([$id]); 

$all_comments = $comments->fetchAll(PDO::FETCH_ASSOC); 
if ($all_comments) {
     foreach ($all_comments as $row) { 
        echo "<p><strong>" . htmlspecialchars($row['username']) . ":</strong> " . htmlspecialchars($row['comment']) . "</p>"; 
    }
         }
else { 
    echo "<p>No comments yet. Be the first to comment!</p>"; 
    } ?> 

    <a href="#leave-comment"><h3>Leave a Comment</h3></a>
<?= $message ?>

<?php if (!isset($_SESSION['user_id'])): ?>

    <p style="color:red;">
        You must <a href="login.php?return=post.php?id=<?= urlencode($id) ?>">log in</a> to leave a comment.
    </p>

<?php else: ?>

    <form method="POST">
        <textarea name="comment" rows="3" cols="50" required><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
        <br><br>

        <!-- CAPTCHA IMAGE -->
        <img src="captcha.php" alt="CAPTCHA Image"><br>

        <label>Enter CAPTCHA:</label><br>
        <input type="text" name="captcha" required>

        <br><br>
        <button type="submit">Submit comment</button>
    </form>

<?php endif; ?>

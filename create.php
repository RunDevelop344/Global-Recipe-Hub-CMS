<?php 
require_once 'connect.php';
require_once 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ADMIN PROTECTION
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    exit;
}
 
?>

<?php
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$name = '';
$category_id = '';
$region = '';
$instructions = '';
$image = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category'];
    $region = trim($_POST['region']);
    $instructions = trim($_POST['instructions']);
    $image = trim($_POST['image']);

    if ($category_id > 0 && !empty($name)) {
        try {
            $statement = $db->prepare("
                INSERT INTO meals (meal_name, category_id, region, instructions, image_url) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $statement->execute([$name, $category_id, $region, $instructions, $image]);

            $message = "<p style='color:green;'>Recipe added successfully!</p>";

            // Clear form
            $name = $instructions = $image = $region = '';
            $category_id = '';

        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Please fill out all fields and select a valid category.</p>";
    }
}
?>

<h2>Add a New Recipe</h2>
<?= $message ?>

<form method="POST">

    <label><strong>Recipe Name:</strong></label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required><br><br>

    <label><strong>Category:</strong></label><br>
    <select id="category" name="category" required>
        <option value="">- Category -</option>

    <?php
$catStmt = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$cats = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php foreach ($cats as $c): ?>
    <option value="<?= $c['category_id'] ?>" <?= ($category_id == $c['category_id'] ? 'selected' : '') ?>>
        <?= htmlspecialchars($c['category_name']) ?>
    </option>
<?php endforeach; ?>
    
    </select><br><br>

    <label>Region:</label>
    <input type="text" name="region" value="<?= htmlspecialchars($region) ?>"><br><br>

    <label>Instructions:</label><br>
    <textarea name="instructions" rows="5" cols="40"><?= htmlspecialchars($instructions) ?></textarea><br><br>

    <label>Image URL:</label><br>
    <input type="text" name="image" value="<?= htmlspecialchars($image) ?>"><br><br>

    <button type="submit">Add Recipe</button>

</form>

<?php include 'footer.php'; ?>

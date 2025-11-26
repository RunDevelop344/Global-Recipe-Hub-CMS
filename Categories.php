<?php
require 'connect.php';
require 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ADMIN ONLY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    include 'footer.php';
    exit;
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Add Category
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $newCat = trim($_POST['category_name']);

    if ($newCat !== "") {
        $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$newCat]);
    }
    header("Location: categories.php");
    exit;
}

// Update Category
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $name = trim($_POST['category_name']);
    $id = (int)$_POST['category_id'];

    $stmt = $db->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    $stmt->execute([$name, $id]);

    header("Location: categories.php");
    exit;
}

// Fetch all categories
$categories = $db->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manage Categories</h2>

<!-- Add New Category -->
<form method="POST">
    <label>New Category:</label>
    <input type="text" name="category_name" required>
    <button type="submit" name="add">Add Category</button>
</form>

<hr>

<!-- Edit Categories -->
<h3>Existing Categories</h3>

<?php foreach ($categories as $cat): ?>
<form method="POST" style="margin-bottom:10px;">
    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
    <input type="text" name="category_name" value="<?= htmlspecialchars($cat['category_name']) ?>" required>
    <button type="submit" name="update">Update</button>
</form>
<?php endforeach; ?>

<?php include 'footer.php'; ?>

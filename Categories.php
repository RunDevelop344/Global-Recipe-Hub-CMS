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

$error = "";
$success = "";

// --- VALIDATION FUNCTION ---
function validateCategory($name) {
    if (strlen($name) < 2) {
        return "Category name must be at least 2 characters long.";
    }
    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        return "Category name should contain letters only.";
    }
    return "";
}

/* ==========================
   ADD NEW CATEGORY
========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $newCat = trim($_POST['category_name']);

    $error = validateCategory($newCat);

    if ($error === "") {
        try {
            $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->execute([$newCat]);
            $success = "Category added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding category.";
        }
    }
}

/* ==========================
   UPDATE CATEGORY
========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {

    $name = trim($_POST['category_name']);
    $id = (int) $_POST['category_id'];

    $error = validateCategory($name);

    if ($error === "") {
        $stmt = $db->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$name, $id]);
        $success = "Category updated!";
    }
}

/* ==========================
   DELETE CATEGORY
========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete'])) {

    $id = (int)$_POST['category_id'];

    // Prevent deleting categories that are still used by recipes
    $check = $db->prepare("SELECT COUNT(*) FROM meals WHERE category_id = ?");
    $check->execute([$id]);
    $inUse = $check->fetchColumn();

    if ($inUse > 0) {
        $error = "Cannot delete this category. It is assigned to existing recipes.";
    } else {
        $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$id]);
        $success = "Category deleted successfully.";
    }
}

// Fetch all categories
$categories = $db->query("SELECT * FROM categories ORDER BY category_name ASC")
                ->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manage Categories</h2>

<!-- Error / Success Messages -->
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<!-- Add New Category -->
<form method="POST">
    <label>New Category:</label>
    <input type="text" name="category_name" required>
    <button type="submit" name="add">Add Category</button>
</form>

<hr>

<!-- Edit/Delete Categories -->
<h3>Existing Categories</h3>

<?php foreach ($categories as $cat): ?>
<form method="POST" style="margin-bottom:10px;">
    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">

    <input type="text" name="category_name"
           value="<?= htmlspecialchars($cat['category_name']) ?>"
           required>

    <button type="submit" name="update">Update</button>

    <button type="submit" name="delete"
        onclick="return confirm('Delete this category? This cannot be undone.');">
        Delete
    </button>
</form>
<?php endforeach; ?>

<?php include 'footer.php'; ?>

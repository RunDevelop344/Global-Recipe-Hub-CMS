<?php 
require 'connect.php';
require 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ADMIN PROTECTION
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    exit;
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>

<h2>Search for a Recipe to Edit</h2>

<?php
$searchQuery = trim($_GET['search'] ?? '');
$searchResults = [];
$error = "";
$success = "";

if ($searchQuery !== "") {
    $sql = "
        SELECT 
            MIN(meals.meal_id) AS meal_id,
            meals.meal_name,
            categories.category_name
        FROM meals
        JOIN categories ON meals.category_id = categories.category_id
        WHERE meals.meal_name LIKE :s OR categories.category_name LIKE :s
        GROUP BY meals.meal_name, categories.category_name
        ORDER BY meals.meal_name ASC
    ";

    $stm = $db->prepare($sql);
    $stm->bindValue(":s", "%$searchQuery%");
    $stm->execute();
    $searchResults = $stm->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Search Bar -->
<form method="GET" action="edit.php">
    <label>Search by Name or Category:</label>
    <input type="text" name="search" placeholder="e.g. Chicken, Dessert..." 
           value="<?= htmlspecialchars($searchQuery) ?>">
    <button type="submit">Search</button>
</form>

<?php if ($searchQuery !== "" && empty($searchResults)): ?>
    <p>No recipes found matching your search.</p>
<?php endif; ?>

<?php if (!empty($searchResults)): ?>
    <div class="recipe-list-container">
        <h3>Search Results</h3>
        <?php foreach ($searchResults as $result): ?>
            <div class="recipe-item">
                <?= htmlspecialchars($result['meal_name']) ?> 
                (<?= htmlspecialchars($result['category_name']) ?>)
                <a href="edit.php?search=<?= urlencode($searchQuery) ?>&id=<?= $result['meal_id'] ?>">Edit</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<hr><br>

<?php
$id = $_GET['id'] ?? null;

if (!$id) {
    include 'footer.php';
    return;
}

// Fetch Recipe
$statement = $db->prepare("SELECT * FROM meals WHERE meal_id = ?");
$statement->execute([$id]);
$recipe = $statement->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "<p>Recipe not found.</p>";
    include 'footer.php';
    return;
}

$category_id = $recipe['category_id'];

// DELETE
if (isset($_POST['delete'])) {
    $delete = $db->prepare("DELETE FROM meals WHERE meal_id = ?");
    $delete->execute([$id]);
    header("Location: edit.php?search=" . urlencode($searchQuery));
    exit;
}

// UPDATE with validation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name']) && !isset($_POST['moderate_action'])) {

    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category'];
    $region = trim($_POST['region']);
    $instructions = trim($_POST['instructions']);
    $image = trim($_POST['image']);

    // VALIDATION RULES ---------------------

    // 1. Recipe name
    if (strlen($name) < 3) {
        $error = "Recipe name must be at least 3 characters.";
    }

    // 2. Category validation
    $validCategories = $db->query("SELECT category_id FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($category_id, $validCategories)) {
        $error = "Invalid category selected.";
    }

    // 3. Region
    if (!empty($region) && !preg_match("/^[A-Za-z ]{2,50}$/", $region)) {
        $error = "Region must contain only letters and spaces, and be under 50 characters.";
    }

    // 4. Image URL validation
    if (!empty($image) && !filter_var($image, FILTER_VALIDATE_URL)) {
        $error = "Image must be a valid URL, or leave blank.";
    }

    // 5. Instructions optional but trimmed
    if (strlen($instructions) > 2000) {
        $error = "Instructions are too long (max 2000 characters).";
    }

    // Save only if NO validation errors
    if ($error === "") {
        $update = $db->prepare("
            UPDATE meals 
            SET meal_name=?, category_id=?, region=?, instructions=?, image_url=? 
            WHERE meal_id=?
        ");
        $update->execute([$name, $category_id, $region, $instructions, $image, $id]);

        $success = "Recipe updated successfully!";
        
        header("Location: edit.php?search=" . urlencode($searchQuery) . "&id=$id");
        exit;
    }
}
?>

<h2>Edit Recipe</h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Recipe Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($recipe['meal_name']) ?>" required>

    <label>Category:</label>
    <select name="category" required>
        <option value="">Select Category</option>

        <?php
        $cats = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC")
                  ->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php foreach ($cats as $c): ?>
            <option value="<?= $c['category_id'] ?>" <?= ($category_id == $c['category_id'] ? 'selected' : '') ?>>
                <?= htmlspecialchars($c['category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Region:</label>
    <input type="text" name="region" value="<?= htmlspecialchars($recipe['region']) ?>">

    <label>Instructions:</label>
    <textarea name="instructions" rows="5"><?= htmlspecialchars($recipe['instructions']) ?></textarea>

    <label>Image URL:</label>
    <input type="text" name="image" value="<?= htmlspecialchars($recipe['image_url']) ?>">

    <button type="submit">Update Recipe</button>
</form>

<form method="POST" onsubmit="return confirm('Delete this recipe?');">
    <button type="submit" name="delete">Delete Recipe</button>
</form>

<?php include 'footer.php'; ?>

<?php 
require_once 'connect.php';
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

<!-- ==============================
     SEARCH SECTION
=================================== -->
<h2>Search for a Recipe to Edit</h2>

<?php
$searchQuery = trim($_GET['search'] ?? '');
$searchResults = [];

if ($searchQuery !== "") {
    $sql = "
        SELECT meals.meal_id, meals.meal_name, categories.category_name
        FROM meals
        JOIN categories ON meals.category_id = categories.category_id
        WHERE meals.meal_name LIKE :s OR categories.category_name LIKE :s
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

<!-- Search Results -->
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

<!-- ==============================
     LOAD RECIPE IF ID IS SELECTED
=================================== -->
<?php
$id = $_GET['id'] ?? null;

// If no recipe selected, stop BEFORE edit form (but keep search visible)
if (!$id) {
    include 'footer.php';
    return;
}

// Fetch Recipe Data
$statement = $db->prepare("SELECT * FROM meals WHERE meal_id = ?");
$statement->execute([$id]);
$recipe = $statement->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "<p>Recipe not found.</p>";
    include 'footer.php';
    return;
}

$category_id = $recipe['category_id'];

// ==============================
// DELETE RECIPE
// ==============================
if (isset($_POST['delete'])) {
    $delete = $db->prepare("DELETE FROM meals WHERE meal_id = ?");
    $delete->execute([$id]);
    header("Location: edit.php?search=" . urlencode($searchQuery));
    exit;
}

// ==============================
// UPDATE RECIPE
// ==============================
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

    header("Location: edit.php?search=" . urlencode($searchQuery) . "&id=$id");
    exit;
}

?>

<!-- ==============================
    //  EDIT FORM
 =================================== -->

<h2>Edit Recipe</h2>

<form method="POST">
    <label>Recipe Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($recipe['meal_name']) ?>" required>

    <label>Category:</label>
    <select name="category" required>
        <option value="">Select Category</option>
        <?php for ($i = 1; $i <= 14; $i++): ?>
            <option value="<?= $i ?>" <?= ($category_id == $i ? 'selected' : '') ?>>
                Category <?= $i ?>
            </option>
        <?php endfor; ?>
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


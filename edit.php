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
$searchQuery = trim($_GET['search'] ?? "");
$id = $_GET['id'] ?? null;

/* ============================================
   SEARCH RECIPES
============================================= */
$sql = "
    SELECT 
        MIN(meals.meal_id) AS meal_id,
        meals.meal_name,
        categories.category_name
    FROM meals
    JOIN categories ON meals.category_id = categories.category_id
";


if ($searchQuery !== "") {
    $sql .= " WHERE meals.meal_name LIKE :s OR categories.category_name LIKE :s ";
}

$sql .= "
    GROUP BY meals.meal_name, categories.category_name
    ORDER BY meals.meal_name ASC
";


$stm = $db->prepare($sql);
if ($searchQuery !== "") {
    $stm->bindValue(":s", "%$searchQuery%");
}
$stm->execute();
$recipes = $stm->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Manage Recipes</h2>

<!-- SEARCH BAR -->
<form method="GET" action="edit.php" style="margin-bottom:20px;">
    <label>Search:</label>
    <input type="text" name="search" placeholder="Search by recipe or category..." 
           value="<?= htmlspecialchars($searchQuery) ?>">
    <button type="submit">Search</button>
</form>

<!-- RECIPE LIST TABLE -->
<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;">
    <tr style="background:#eee;">
        <th>ID</th>
        <th>Recipe Name</th>
        <th>Category</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($recipes as $r): ?>
    <tr>
        <td><?= $r['meal_id'] ?></td>
        <td><?= htmlspecialchars($r['meal_name']) ?></td>
        <td><?= htmlspecialchars($r['category_name']) ?></td>
        <td>
            <a href="edit.php?id=<?= $r['meal_id'] ?>&search=<?= urlencode($searchQuery) ?>">Edit</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<hr><br>

<?php
/* ============================================
   LOAD RECIPE FOR EDITING
============================================= */
if (!$id) {
    include 'footer.php';
    exit;
}

$stm = $db->prepare("SELECT * FROM meals WHERE meal_id = ?");
$stm->execute([$id]);
$recipe = $stm->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "<p>Recipe not found.</p>";
    include 'footer.php';
    exit;
}

$category_id = $recipe['category_id'];

/* ============================================
   DELETE RECIPE
============================================= */
if (isset($_POST['delete'])) {
    $del = $db->prepare("DELETE FROM meals WHERE meal_id=?");
    $del->execute([$id]);
    header("Location: edit.php?search=" . urlencode($searchQuery));
    exit;
}

/* ============================================
   UPDATE RECIPE
============================================= */
if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category'];
    $region = trim($_POST['region']);
    $instructions = trim($_POST['instructions']);
    $image = trim($_POST['image']);

    // VALIDATIONS -------------------------
    if (strlen($name) < 3) {
        $error = "Recipe name must be at least 3 characters.";
    }

    $validCategories = $db->query("SELECT category_id FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($category_id, $validCategories)) {
        $error = "Invalid category selected.";
    }

    if (!empty($region) && !preg_match("/^[A-Za-z ]{2,50}$/", $region)) {
        $error = "Region must contain only letters and spaces.";
    }

    if (!empty($image) && !filter_var($image, FILTER_VALIDATE_URL)) {
        $error = "Image must be a valid URL.";
    }

    if (strlen($instructions) > 2000) {
        $error = "Instructions too long (2000 max).";
    }

    // SAVE IF VALID
    if ($error === "") {

        $update = $db->prepare("
            UPDATE meals
            SET meal_name=?, category_id=?, region=?, instructions=?, image_url=?
            WHERE meal_id=?
        ");

        $update->execute([
            $name, $category_id, $region, $instructions, $image, $id
        ]);

        $success = "Recipe updated successfully!";
        header("Location: edit.php?id=$id&search=" . urlencode($searchQuery));
        exit;
    }
}
?>

<h2>Edit Recipe</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<!-- EDIT FORM -->
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
            <option value="<?= $c['category_id'] ?>" <?= $category_id == $c['category_id'] ? "selected" : "" ?>>
                <?= htmlspecialchars($c['category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Region:</label>
    <input type="text" name="region" value="<?= htmlspecialchars($recipe['region']) ?>">

    <label>Instructions:</label>
    <textarea name="instructions" id="instructions"><?= $recipe['instructions'] ?></textarea>

    <label>Image URL:</label>
    <input type="text" name="image" value="<?= htmlspecialchars($recipe['image_url']) ?>">

    <button type="submit" name="update">Update Recipe</button>
</form>

<form method="POST" onsubmit="return confirm('Delete this recipe?');">
    <button type="submit" name="delete">Delete</button>
</form>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('instructions');
</script>



<?php include 'footer.php'; ?>

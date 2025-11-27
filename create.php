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

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Form variables
$name = '';
$category_id = '';
$region = '';
$instructions = '';
$image = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Trim inputs
    $name         = trim($_POST['name']);
    $category_id  = (int) $_POST['category'];
    $region       = trim($_POST['region']);
    $instructions = trim($_POST['instructions']);
    $image        = trim($_POST['image']);

    // -------------------------------
    // VALIDATIONS
    // -------------------------------
    $errors = [];

    // Recipe name validation
    if (empty($name)) {
        $errors[] = "Recipe name is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9\s'-]{2,50}$/", $name)) {
        $errors[] = "Recipe name can only contain letters, numbers, spaces, apostrophes, and hyphens.";
    }

    // Category validation
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category.";
    }

    // Region validation (optional)
    if (!empty($region) && !preg_match("/^[a-zA-Z\s'-]{2,50}$/", $region)) {
        $errors[] = "Region can only contain letters, spaces, hyphens, and apostrophes.";
    }

    // Instructions validation (optional)
    if (!empty($instructions) && strlen($instructions) < 10) {
        $errors[] = "Instructions must be at least 10 characters long.";
    }

    // Image URL validation (optional)
    if (!empty($image) && !filter_var($image, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid image URL.";
    }

    // -------------------------------
    // IF VALIDATION FAILS
    // -------------------------------
    if (!empty($errors)) {
        $message = "<p class='error'>" . implode("<br>", $errors) . "</p>";

    } else {
        // -------------------------------
        // INSERT INTO DATABASE
        // -------------------------------
        try {
            $statement = $db->prepare("
                INSERT INTO meals (meal_name, category_id, region, instructions, image_url) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $statement->execute([$name, $category_id, $region, $instructions, $image]);

            $message = "<p class='success'>Recipe added successfully!</p>";

            // Clear form
            $name = $instructions = $image = $region = '';
            $category_id = '';

        } catch (PDOException $e) {
            $message = "<p class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h2>Add a New Recipe</h2>
<?= $message ?>

<form method="POST">

    <label><strong>Recipe Name:</strong></label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label><strong>Category:</strong></label>
    <select id="category" name="category" required>
        <option value="">- Category -</option>

        <?php
        $catStmt = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
        $cats = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php foreach ($cats as $c): ?>
            <option value="<?= $c['category_id'] ?>" 
                <?= ($category_id == $c['category_id'] ? 'selected' : '') ?>>
                <?= htmlspecialchars($c['category_name']) ?>
            </option>
        <?php endforeach; ?>

    </select>

    <label>Region:</label>
    <input type="text" name="region" value="<?= htmlspecialchars($region) ?>">

    <label>Instructions:</label>
    <textarea name="instructions" rows="5"><?= htmlspecialchars($instructions) ?></textarea>

    <label>Image URL:</label>
    <input type="text" name="image" value="<?= htmlspecialchars($image) ?>">

    <button type="submit">Add Recipe</button>

</form>

<?php include 'footer.php'; ?>

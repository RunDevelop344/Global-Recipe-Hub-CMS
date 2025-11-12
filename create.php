<?php
// Force logout by sending 401 headers to browser
header('HTTP/1.1 401 Unauthorized');
header('WWW-Authenticate: Basic realm="Admin Area"');
exit("Login required.");
?>

<?php 
require_once 'admin.php';
require_once 'connect.php'; 
include 'header.php'; 
 
  ?>
<!-- git initialized -->

<?php

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$name = '';
$category_id = '';
$instructions = '';
$image = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $category_id = (int)$_POST['category'];
    $area = htmlspecialchars($_POST['area']);
    $instructions = htmlspecialchars($_POST['instructions']);
    $image = htmlspecialchars($_POST['image']);

if ($category_id > 0 && !empty($name)) {
    try {
    $statement = $db->prepare("INSERT INTO meals (meal_name, category_id, area, instructions, image_url) VALUES (?, ?, ?, ?, ?)");
    $statement->execute([$name, $category_id, $area, $instructions, $image]);
    $message = "<p style='color:green;'>Recipe added successfully!</p>";
    
    $name = $instructions = $image = '';
    $category_id = '';
    } catch (PDOException $e) {
        $message = "<p style = 'color:red;'>Database Error: " . $e->getMessage() . "</p>";
    }
} else {
     $message = "<p style='color:red;'>Please fill out all fields and select a valid category.</p>";
}

}
?>

<h2>Add a New Recipe</h2>
<?php echo $message; ?>

<form method="POST">
    <label><strong>Recipe Name:</strong></label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br><br>

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

    <label>Area:</label>
    <input type="text" name="area"><br><br>


    <label>Instructions:</label><br>
    <textarea name="instructions" rows="5" cols="40"></textarea><br><br>

    <label>Image URL:</label><br>
    <input type="text" name="image"><br><br>

    <button type="submit">Add Recipe</button>
</form>

<?php include 'footer.php'; ?>

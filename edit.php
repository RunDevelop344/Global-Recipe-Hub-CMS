<?php
// Force logout by sending 401 headers to browser
header('HTTP/1.1 401 Unauthorized');
header('WWW-Authenticate: Basic realm="Admin Area"');
exit("Login required.");
?>

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category_id = $_POST['category'];
    $area = htmlspecialchars($_POST['area']);
    $instructions = $_POST['instructions'];
    $image = $_POST['image'];

    $update = $db->prepare("UPDATE meals SET meal_name=?, category_id=?, area=?, instructions=?, image_url=? WHERE meal_id=?");
    $update->execute([$name, $category_id, $area, $instructions, $image, $id]);
    echo "<p> Recipe updated successfully!</p>";
}
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

    <label>Area:</label>
    <input type="text" name="area"><br><br>

    <label>Instructions:</label><br>
    <textarea name="instructions" rows="5" cols="40"><?= htmlspecialchars($recipe['instructions']) ?></textarea><br><br>

    <label>Image URL:</label><br>
    <input type="text" name="image" value="<?= htmlspecialchars($recipe['image_url']) ?>"><br><br>

    <button type="submit">Update Recipe</button>

    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
    <button type="submit" name="delete" >Delete Recipe</button>
</form>

</form>

<?php include 'footer.php'; ?>

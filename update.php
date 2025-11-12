<?php
require 'connect.php';
require('authenticate.php');



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category'];
    $instructions = $_POST['instructions'];
    $image = $_POST['image'];

    $statement = $db->prepare("UPDATE meals SET name=?, category_id=?, instructions=?, image=? WHERE id=?");
    $statement->execute([$name, $category_id, $instructions, $image, $id]);

    header("Location: index.php");
    exit;
}
?>

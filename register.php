<?php
require 'connect.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $statement = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($statement->execute([$username, $email, $password])) {
        $message = "Registration successful! You can now log in.";
    } else {
        $message = " Something went wrong.";
    }
}
include 'header.php';
?>

<h2>Register</h2>
<form method="POST">
    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <button type="submit">Register</button>
</form>

<?php if ($message): ?>
    <p><?= $message ?></p>
<?php endif; ?>

<?php include 'footer.php'; ?>

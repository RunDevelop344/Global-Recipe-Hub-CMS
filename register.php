<?php
session_start();
require 'connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $email    = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    $role     = $_POST['role']; // user or admin

    if ($username && $email && $password && $role) {

        // Check if email is already used
        $check = $db->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed, $role]);

            $success = "Registration successful! You may now log in.";
        }

    } else {
        $error = "All fields are required.";
    }
}
?>

<?php include 'header.php'; ?>

<h2>Register</h2>

<form method="POST" action="register.php">

    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>Register as:</label>
    <select name="role" required>
        <option value="user">User</option>
        <option value="admin">Admin</option> <!-- Admin registration allowed -->
    </select>

    <button type="submit">Register</button>
</form>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php include 'footer.php'; ?>

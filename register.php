<?php
session_start();
require 'connect.php';

$error = "";
$success = "";

// ===========================
//  VALIDATION HELPERS
// ===========================
function clean($value) {
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = clean($_POST['username']);
    $email    = strtolower(clean($_POST['email']));
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);
    $role     = $_POST['role']; // Raw (validated later)

    // ===========================
    //  BASIC VALIDATION
    // ===========================
    if (!$username || !$email || !$password || !$confirm || !$role) {
        $error = "All fields are required.";
    }
    elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    }
    elseif (!in_array($role, ['user', 'admin'])) {
        $error = "Invalid role selected.";
    }
    else {

        // ===========================
        //  CHECK IF EMAIL EXISTS
        // ===========================
        $check = $db->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "Email already registered!";
        } else {

            // ===========================
            //  HASH AND INSERT
            // ===========================
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashed, $role]);

            $success = "Registration successful! You may now log in.";

        }
    }
}
?>

<?php include 'header.php'; ?>

<h2>Register</h2>

<form method="POST" action="register.php">

    <label>Username:</label>
    <input type="text" name="username" minlength="3" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" minlength="6" required>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" minlength="6" required>

    <label>Register as:</label>
    <select name="role" required>
        <option value="user">User</option>
        <option value="admin">Admin</option>
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

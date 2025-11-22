<?php
session_start();
require 'connect.php';

$error = "";

$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'index.php';

if (isset($_GET['logout'])) {
    $redirect = 'post.php';
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    $statement = $db->prepare("SELECT * FROM users WHERE LOWER(email) = ?");
    $statement->execute([$email]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

        header("Location: " . $redirect);
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}    

?>

<?php include 'header.php' ?>

<h2>Login</h2>

<form method="POST" action="login.php">
    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<p>Don't have an account? <a href="register.php">Register here</a></p>


<br><br><br>

<?php
require 'connect.php';
require 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    include 'footer.php';
    exit;
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = "";
$success = "";

/* ================================================================
   VALIDATION HELPERS
================================================================ */

function valid_username($u) {
    return strlen($u) >= 3 && preg_match("/^[A-Za-z0-9 _-]+$/", $u);
}

function valid_email($e) {
    return filter_var($e, FILTER_VALIDATE_EMAIL);
}

/* ================================================================
   ADD NEW USER
================================================================ */
if (isset($_POST['add_user'])) {

    $username = trim($_POST['username']);
    $email    = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    $role     = $_POST['role'];

    // BASIC VALIDATIONS
    if (!$username || !$email || !$password || !$role) {
        $error = "All fields are required to create a user.";
    }
    elseif (!valid_username($username)) {
        $error = "Invalid username. Minimum 3 characters. Allowed: letters, numbers, spaces, - , _";
    }
    elseif (!valid_email($email)) {
        $error = "Invalid email format.";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    else {
        // Check duplicate email
        $check = $db->prepare("SELECT * FROM users WHERE email=?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "A user with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $add = $db->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, ?)
            ");
            $add->execute([$username, $email, $hashed, $role]);

            $success = "User added successfully!";
        }
    }

    header("Location: users.php");
    exit;
}

/* ================================================================
   UPDATE USER
================================================================ */
if (isset($_POST['update_user'])) {

    $id       = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $email    = strtolower(trim($_POST['email']));
    $role     = $_POST['role'];
    $password = trim($_POST['password']); // optional

    // VALIDATIONS
    if (!valid_username($username)) {
        $error = "Invalid username.";
    }
    elseif (!valid_email($email)) {
        $error = "Invalid email format.";
    } else {

        // Prevent duplicate email conflicts
        $check = $db->prepare("SELECT * FROM users WHERE email=? AND user_id != ?");
        $check->execute([$email, $id]);

        if ($check->rowCount() > 0) {
            $error = "Another user already uses this email.";
        } 
        else {
            // Password update
            if ($password !== "") {
                if (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update = $db->prepare("
                        UPDATE users 
                        SET username=?, email=?, password=?, role=?
                        WHERE user_id=?
                    ");
                    $update->execute([$username, $email, $hashed, $role, $id]);
                }
            } 
            else {
                // No password change
                $update = $db->prepare("
                    UPDATE users 
                    SET username=?, email=?, role=?
                    WHERE user_id=?
                ");
                $update->execute([$username, $email, $role, $id]);
            }

            $success = "User updated successfully!";
        }
    }

    header("Location: users.php");
    exit;
}

/* ================================================================
   DELETE USER
================================================================ */
if (isset($_POST['delete_user'])) {
    $id = (int)$_POST['user_id'];

    if ($id !== $_SESSION['user_id']) { // prevent deleting yourself
        $delete = $db->prepare("DELETE FROM users WHERE user_id=?");
        $delete->execute([$id]);
    }

    header("Location: users.php");
    exit;
}

/* ================================================================
   FETCH USERS
================================================================ */
$users = $db->query("SELECT * FROM users ORDER BY user_id ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Manage Users</h2>

<!-- SUCCESS / ERROR MESSAGES -->
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>


<!-- ADD NEW USER -->
<h3>Add New User</h3>

<form method="POST" style="max-width:500px; margin:auto;">
    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>Role:</label>
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit" name="add_user">Add User</button>
</form>

<hr>

<h3>Existing Users</h3>
<div style="max-width:700px; margin:auto;">
<?php foreach ($users as $user): ?>
    <form method="POST" style="background:#fff; padding:15px; border-radius:10px; margin-bottom:10px; box-shadow:0 3px 6px rgba(0,0,0,0.1);">
        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Role:</label>
        <select name="role">
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <label>New Password (optional):</label>
        <input type="password" name="password" placeholder="Leave blank to keep existing password">

        <button type="submit" name="update_user">Update User</button>

        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
            <button type="submit" name="delete_user" onclick="return confirm('Delete this user?')">Delete</button>
        <?php else: ?>
            <p style="color:red;"><em>You cannot delete your own admin account.</em></p>
        <?php endif; ?>

    </form>
<?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>

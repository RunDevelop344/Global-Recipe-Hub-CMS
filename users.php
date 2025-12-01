<?php
require 'connect.php';
require 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    include 'footer.php';
    exit;
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = "";
$success = "";

/* ============================================
   VALIDATION FUNCTIONS
============================================ */
function valid_username($u) {
    return strlen($u) >= 3 && preg_match("/^[A-Za-z0-9 _-]+$/", $u);
}

function valid_email($e) {
    return filter_var($e, FILTER_VALIDATE_EMAIL);
}

/* ============================================
   ADD USER
============================================ */
if (isset($_POST['add_user'])) {

    $username = trim($_POST['username']);
    $email    = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    $role     = $_POST['role'];

    if (!$username || !$email || !$password) {
        $error = "All fields are required.";
    }
    elseif (!valid_username($username)) {
        $error = "Invalid username.";
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

            $insert = $db->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, ?)
            ");
            $insert->execute([$username, $email, $hashed, $role]);

            $success = "User created successfully!";
        }
    }
}

/* ============================================
   UPDATE USER
============================================ */
if (isset($_POST['update_user'])) {

    $id       = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $email    = strtolower(trim($_POST['email']));
    $role     = $_POST['role'];
    $password = trim($_POST['password']); // optional

    if (!valid_username($username)) {
        $error = "Invalid username.";
    }
    elseif (!valid_email($email)) {
        $error = "Invalid email format.";
    } 
    else {
        // Check if another user uses same email
        $check = $db->prepare("SELECT * FROM users WHERE email=? AND user_id != ?");
        $check->execute([$email, $id]);

        if ($check->rowCount() > 0) {
            $error = "Another user already uses this email.";
        }
        else {
            if ($password !== "") {
                if (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    $update = $db->prepare("
                        UPDATE users SET username=?, email=?, password=?, role=? WHERE user_id=?
                    ");
                    $update->execute([$username, $email, $hashed, $role, $id]);
                }
            } else {
                // Update without password
                $update = $db->prepare("
                    UPDATE users SET username=?, email=?, role=? WHERE user_id=?
                ");
                $update->execute([$username, $email, $role, $id]);
            }

            $success = "User updated successfully!";
        }
    }
}

/* ============================================
   DELETE USER
============================================ */
if (isset($_POST['delete_user'])) {

    $id = (int)$_POST['user_id'];

    if ($id != $_SESSION['user_id']) {
        $delete = $db->prepare("DELETE FROM users WHERE user_id=?");
        $delete->execute([$id]);
        $success = "User deleted.";
    } else {
        $error = "You cannot delete your own admin account.";
    }
}

/* ============================================
   FETCH USERS
============================================ */
$users = $db->query("SELECT * FROM users ORDER BY user_id ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Manage Users</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<!-- ADD NEW USER FORM -->
<h3>Add New User</h3>
<form method="POST" style="max-width:500px; margin-bottom:30px;">
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


<!-- USER LIST TABLE -->
<h3>All Users</h3>

<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;">
    <tr style="background:#eee;">
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>New Password</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($users as $user): ?>
    <tr>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

            <td><?= $user['user_id'] ?></td>

            <td>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </td>

            <td>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </td>

            <td>
                <select name="role">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </td>

            <td>
                <input type="password" name="password" placeholder="Leave blank">
            </td>

            <td>
                <button type="submit" name="update_user">Update</button>

                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                    <button type="submit" name="delete_user" onclick="return confirm('Delete this user?')">Delete</button>
                <?php endif; ?>
            </td>
        </form>
    </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>

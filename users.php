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

// Delete user
if (isset($_POST['delete'])) {
    $id = (int)$_POST['user_id'];

    // Do NOT let admin delete themselves
    if ($id !== $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
    }

    header("Location: users.php");
    exit;
}

// Fetch all users
$users = $db->query("SELECT user_id, username, email, role FROM users ORDER BY user_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manage Users</h2>

<div style="max-width:700px; margin:auto;">
<?php foreach ($users as $user): ?>
    <form method="POST" style="background:#fff; padding:15px; border-radius:10px; margin-bottom:10px; box-shadow:0 3px 6px rgba(0,0,0,0.1);">
        
        <p><strong><?= htmlspecialchars($user['username']) ?></strong></p>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Role: <?= htmlspecialchars($user['role']) ?></p>

        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
            <button type="submit" name="delete" onclick="return confirm('Delete this user?')">Delete User</button>
        <?php else: ?>
            <p><em>You cannot delete your own admin account.</em></p>
        <?php endif; ?>

    </form>
<?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>

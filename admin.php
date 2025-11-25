<?php
session_start();

// User not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Check user role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<h2 style='color:red;'>Access Denied — Admins Only</h2>";
    exit;
}
?>

<?php

// Prevent constants from being redefined
if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql:host=localhost;dbname=serverside;charset=utf8');
}

if (!defined('DB_USER')) {
    define('DB_USER', 'serveruser');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', 'gorgonzola7!');
}

try {
    // Create PDO connection
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
} catch (PDOException $e) {
    print "Error: " . $e->getMessage();
    die();
}

?>

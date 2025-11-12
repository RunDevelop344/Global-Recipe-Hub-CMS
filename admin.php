<?php
define('ADMIN_LOGIN','ghostface');
define('ADMIN_PASSWORD','killa');

// Optional FastCGI fix for XAMPP/WAMP
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
        list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $auth);
    }
}

// Generate a dynamic realm using current timestamp
$realm = "Admin Area " . time();

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== ADMIN_LOGIN ||
    $_SERVER['PHP_AUTH_PW'] !== ADMIN_PASSWORD
) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="'.$realm.'"'); // dynamic realm
    exit("<h2>Access Denied</h2>");
}
?>

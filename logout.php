<?php
require_once __DIR__ . '/init/init.php';

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Restart clean session to set flash message
session_start();
set_flash_message('info', 'You have been logged out successfully.');

redirect('login.php');
?>

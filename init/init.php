<?php
// Start Session for user login tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable Output Buffering to prevent header errors
ob_start();

// Include Database connection and helper functions
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
?>
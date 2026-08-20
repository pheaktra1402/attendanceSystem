<?php
// ====================================================================
// Helper Functions for Attendance System
// ====================================================================

/**
 * Sanitize User Input to prevent XSS (Cross Site Scripting)
 */
function sanitize($data) {
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $db->real_escape_string($data);
}

/**
 * Redirect browser to a target URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if a user is logged in.
 * If not logged in, redirect them to login page.
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        set_flash_message('danger', 'Please log in to access this page.');
        redirect('login.php');
    }
}

/**
 * Set a Flash Alert Message in Session
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Display and clear Flash Alert Message
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'];
        $msg  = $_SESSION['flash_message']['message'];
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                <i class='fa-solid fa-circle-info me-2'></i>" . htmlspecialchars($msg) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        
        // Remove after displaying so it only shows once
        unset($_SESSION['flash_message']);
    }
}
?>

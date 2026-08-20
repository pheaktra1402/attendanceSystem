<?php
// ====================================================================
// Database Connection Configuration (mysqli)
// ====================================================================

// Database Credentials
$host     = 'localhost';
$dbname   = 'attendanceSystem';
$user     = 'root';
$password = '';

// Create MySQL connection using PHP's builtin mysqli extension
$db = new mysqli($host, $user, $password, $dbname);

// Check if connection succeeded
if ($db->connect_error) {
    // If database connection fails, stop execution and show error message
    die("Database Connection Failed: " . $db->connect_error . "<br>Please make sure MySQL is running in XAMPP and database 'attendanceSystem' exists.");
}

// Set character set to UTF-8 to support special characters
$db->set_charset("utf8mb4");
?>
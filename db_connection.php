<?php
// Database configuration
$server = "localhost";
$username = "root";       // Default for MAMP
$password = "root";       // Default for MAMP
$dbname = "events_db";    // Your database name

// Create database connection
$db = new mysqli($server, $username, $password, $dbname);

// Check for connection errors
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Optional: Set character encoding
$db->set_charset("utf8mb4");

// Optional: Report MySQLi errors (for debugging only, remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>

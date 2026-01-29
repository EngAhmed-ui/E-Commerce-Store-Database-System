<?php
// config/db.php
$host = "host";
$user = "user";
$pass = "password";
$dbname = "E_Commerce_Store";

// Connection established and assigned to $conn
$conn = new mysqli($host, $user, $pass, $dbname);

// Error check for connection failure
if ($conn->connect_error) {
    die("Database Error: " . $conn->connect_error);
}

// Define base URL
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/ecommerce_project');
?>
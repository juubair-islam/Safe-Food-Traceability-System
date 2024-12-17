<?php
// Database connection settings
$host = "localhost";      // Database host
$username = "root";       // Default MySQL user for XAMPP
$password = "";           // Leave blank for XAMPP's root user
$database = "safe_food_traceability"; // Your project database

// Create a connection
$conn = new mysqli($host, $username, $password, $database, 3306); // Port 3306 for MySQL

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

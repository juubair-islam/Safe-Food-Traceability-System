<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "safe_food_traceability");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from tables
$totalCropsQuery = "SELECT COUNT(*) AS total_crops FROM crops";
$totalUsersQuery = "SELECT COUNT(*) AS total_users FROM users";
$availableQuantityQuery = "SELECT SUM(quantity) AS total_quantity FROM crops";
$recentActivityQuery = "SELECT * FROM recent_activity ORDER BY activity_time DESC LIMIT 5";

$totalCropsResult = $conn->query($totalCropsQuery);
$totalUsersResult = $conn->query($totalUsersQuery);
$availableQuantityResult = $conn->query($availableQuantityQuery);
$recentActivityResult = $conn->query($recentActivityQuery);

$totalCrops = $totalCropsResult->fetch_assoc()["total_crops"] ?? 0;
$totalUsers = $totalUsersResult->fetch_assoc()["total_users"] ?? 0;
$availableQuantity = $availableQuantityResult->fetch_assoc()["total_quantity"] ?? 0;

$recentActivities = [];
if ($recentActivityResult->num_rows > 0) {
    while ($row = $recentActivityResult->fetch_assoc()) {
        $recentActivities[] = $row;
    }
}

$conn->close();
?>

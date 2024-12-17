<?php
include 'database.php';

// Query the database to get the latest user_id
$query = "SELECT MAX(user_id) AS latest_id FROM users";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $next_id = $row['latest_id'] + 1; // Increment the highest user_id by 1
} else {
    $next_id = 1; // Default to 1 if no users exist
}

// Return the next User ID as JSON
header('Content-Type: application/json');
echo json_encode(['next_user_id' => $next_id]);
?>

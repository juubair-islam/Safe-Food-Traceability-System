<?php
include('db_connection.php');

// Get the farmer ID from the request
$farmerId = $_GET['farmer_id'];

// Check if the farmer ID exists in the database
$sql = "SELECT farmer_id FROM farmers WHERE farmer_id = '$farmerId'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo 'exists';
} else {
    echo 'not_exists';
}
?>

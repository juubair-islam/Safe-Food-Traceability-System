<?php
// Include database connection
include('db_connection.php');

if (isset($_GET['crop_name'])) {
    $crop_name = $_GET['crop_name'];

    // Query to get the nutrition data for the selected crop
    $query = "SELECT * FROM nutrition_crop WHERE crop_name = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$crop_name]);
    $crop_info = $stmt->fetch();

    if ($crop_info) {
        echo json_encode($crop_info);
    } else {
        echo json_encode(null);
    }
}
?>

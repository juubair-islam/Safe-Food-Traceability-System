<?php
include('db_connection.php');

// Get the crop name from the request
$cropName = $_GET['crop_name'];

// Fetch batch numbers for the specified crop
$sql = "SELECT batch_number, farmer_id, harvest_date FROM crops WHERE name = '$cropName'";
$result = $conn->query($sql);

$batches = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row;
    }
}

// Return the batch numbers as JSON
echo json_encode($batches);
?>

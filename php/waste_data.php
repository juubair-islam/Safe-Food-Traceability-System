<?php
include 'database.php';

// Query for waste management data
$query = "
    SELECT 
        SUM(CASE WHEN output_type = 'Compost' THEN amount ELSE 0 END) AS compost,
        SUM(CASE WHEN output_type = 'Biogas' THEN amount ELSE 0 END) AS biogas
    FROM waste_management";
$result = $conn->query($query);
$data = $result->fetch_assoc();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>

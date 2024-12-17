<?php
include 'database.php';

// Query for batch statuses
$query = "
    SELECT 
        SUM(CASE WHEN status = 'Storable' THEN 1 ELSE 0 END) AS storable,
        SUM(CASE WHEN status = 'Non-Storable' THEN 1 ELSE 0 END) AS non_storable,
        SUM(CASE WHEN status = 'Damaged' THEN 1 ELSE 0 END) AS damaged
    FROM batches";
$result = $conn->query($query);
$data = $result->fetch_assoc();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>

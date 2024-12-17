<?php
include 'database.php';

// Query for total users
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'];

// Query for total batches
$total_batches_query = "SELECT COUNT(*) AS total_batches FROM batches";
$total_batches_result = $conn->query($total_batches_query);
$total_batches = $total_batches_result->fetch_assoc()['total_batches'];

// Query for total quality checks
$total_quality_checks_query = "SELECT COUNT(*) AS quality_checks FROM quality_checks";
$total_quality_checks_result = $conn->query($total_quality_checks_query);
$total_quality_checks = $total_quality_checks_result->fetch_assoc()['quality_checks'];

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'total_users' => $total_users,
    'total_batches' => $total_batches,
    'quality_checks' => $total_quality_checks
]);
?>
<?php
include 'database.php';

// Query for all users
$query = "SELECT user_id, name, email, role, region FROM users";
$result = $conn->query($query);

// Prepare data for DataTables
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>

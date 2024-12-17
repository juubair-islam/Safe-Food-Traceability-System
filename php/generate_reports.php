// Traceability Report
$query = "SELECT * FROM batches WHERE status = 'Storable'";
$result = $conn->query($query);
$data = $result->fetch_all(MYSQLI_ASSOC);

// Example of generating quality check reports
$qc_query = "SELECT * FROM quality_checks WHERE status = 'Fail'";
$qc_result = $conn->query($qc_query);
$qc_data = $qc_result->fetch_all(MYSQLI_ASSOC);

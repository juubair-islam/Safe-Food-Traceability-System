<?php
$conn = new mysqli("localhost", "root", "", "safe_food_traceability");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT b.batch_id, c.name as crop_name, b.status, b.storage_condition, c.harvest_date, c.origin, b.barcode FROM batches b JOIN crops c ON b.crop_id = c.crop_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // Output data for each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['batch_id'] . "</td>";
    echo "<td>" . $row['crop_name'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . $row['harvest_date'] . "</td>";
    echo "<td>" . $row['origin'] . "</td>";
    echo "<td>" . $row['storage_condition'] . "</td>";
    echo "<td><a href='edit_batch.php?batch_id=" . $row['batch_id'] . "'>Edit</a> | <a href='delete_batch.php?batch_id=" . $row['batch_id'] . "'>Delete</a></td>";
    echo "</tr>";
  }
} else {
  // If no data, display a message
  echo "<tr><td colspan='7'>No batches found.</td></tr>";
}

$conn->close();
?>

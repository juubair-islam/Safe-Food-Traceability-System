<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'safe_food_traceability';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_id = $_POST['crop_id']; // Crop ID from the crops table
    $status = $_POST['status'];
    $storage_condition = $_POST['storage_condition'];
    $processing_date = $_POST['processing_date'];
    $barcode = $_POST['barcode'];

    // Inserting data into batches table
    $sql = "INSERT INTO batches (crop_id, status, storage_condition, processing_date, barcode)
            VALUES ('$crop_id', '$status', '$storage_condition', '$processing_date', '$barcode')";

    if ($conn->query($sql) === TRUE) {
        echo "New batch added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<form method="POST" action="">
    Crop ID (from crops table): <input type="number" name="crop_id" required><br>
    Status: <select name="status" required>
        <option value="Storable">Storable</option>
        <option value="Non-Storable">Non-Storable</option>
        <option value="Damaged">Damaged</option>
    </select><br>
    Storage Condition: <input type="text" name="storage_condition"><br>
    Processing Date: <input type="date" name="processing_date" required><br>
    Barcode: <input type="text" name="barcode" required><br>
    <input type="submit" value="Add Batch">
</form>

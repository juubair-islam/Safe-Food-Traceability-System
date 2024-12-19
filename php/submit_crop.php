<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_name = $_POST['crop_name'];
    $quantity = $_POST['quantity'];
    $harvest_date = $_POST['harvest_date'];
    $district = $_POST['district'];
    $storage_conditions = $_POST['storage_conditions'];

    $conn = new mysqli("localhost", "username", "password", "safe_food_traceability");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO crops (name, quantity, harvest_date, district, storage_conditions)
            VALUES ('$crop_name', '$quantity', '$harvest_date', '$district', '$storage_conditions')";

    if ($conn->query($sql) === TRUE) {
        echo "New crop batch added successfully!";
        header("Location: manage_batches.html"); // Redirect to manage batches page
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

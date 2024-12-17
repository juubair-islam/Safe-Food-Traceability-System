<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $region = $_POST["region"];

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user into the database
    $query = $conn->prepare("INSERT INTO users (name, email, password, role, region, registration_status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $query->bind_param("sssss", $name, $email, $hashed_password, $role, $region);

    if ($query->execute()) {
        echo "User registered successfully!";
    } else {
        echo "Error: " . $query->error;
    }
}
?>

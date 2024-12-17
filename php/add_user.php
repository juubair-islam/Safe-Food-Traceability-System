<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $user_id = $_POST["user_id"] ?? 0; // Fallback to 0 if user_id is not set
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $region = $_POST["region"];

    // Check if the email already exists
    $check_email_query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_email_query->bind_param("s", $email);
    $check_email_query->execute();
    $result = $check_email_query->get_result();

    if ($result->num_rows > 0) {
        die("Error: This email is already registered."); // Stop execution if email exists
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user into the database
    $query = $conn->prepare("INSERT INTO users (user_id, name, email, password, role, region, registration_status) VALUES (?, ?, ?, ?, ?, ?, 'Approved')");
    $query->bind_param("isssss", $user_id, $name, $email, $hashed_password, $role, $region);

    if ($query->execute()) {
        echo "User added successfully!";
    } else {
        echo "Error: " . $query->error;
    }
}
?>

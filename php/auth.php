<?php
session_start();
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST["userId"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    // Query to check user credentials
    $query = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = ?");
    $query->bind_param("is", $userId, $role);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        // Successful login
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["role"] = $user["role"];
        header("Location: ../dashboard_" . strtolower($role) . ".php");
        exit();
    } else {
        echo "Invalid credentials!";
    }
}
?>

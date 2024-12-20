<?php
// Database connection
$host = 'localhost';
$dbname = 'safe_food_traceability';
$username = 'root';
$password = '';

try {
    // Create a PDO instance and connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch farmer data based on the farmer_id passed from AJAX
if (isset($_POST['farmer_id'])) {
    $farmer_id = $_POST['farmer_id'];
    
    // Query the database for farmer details
    $stmt = $pdo->prepare("SELECT * FROM farmers WHERE farmer_id = ?");
    $stmt->execute([$farmer_id]);
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($farmer) {
        echo "<p><strong>Farmer Name:</strong> " . $farmer['name'] . "</p>";
        echo "<p><strong>Contact Number:</strong> " . $farmer['contact_number'] . "</p>";
        echo "<p><strong>District:</strong> " . $farmer['district'] . "</p>";
    } else {
        echo "<p style='color:red;'>Farmer ID not found in the database.</p>";
    }
}
?>

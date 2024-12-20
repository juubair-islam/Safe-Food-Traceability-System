<?php
// Database connection
$host = 'localhost';
$dbname = 'safe_food_traceability';
$username = 'root';
$password = '';

try {
    // Create a PDO instance and connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_POST['crop_name'])) {
    $crop_name = $_POST['crop_name'];

    $stmt = $pdo->prepare("SELECT * FROM nutrition_crop WHERE crop_name = ?");
    $stmt->execute([$crop_name]);
    $crop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crop) {
        // Return crop details as JSON
        echo json_encode([
            'ideal_temp' => $crop['ideal_temperature'],
            'ideal_humidity' => $crop['ideal_humidity'],
            'nutrition_value' => $crop['nutrition_value']
        ]);
    } else {
        echo json_encode(['error' => 'Crop not found']);
    }
} else {
    echo json_encode(['error' => 'No crop selected']);
}
?>

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

// Fetch crop nutrition details based on crop_name
if (isset($_POST['crop_name'])) {
    $crop_name = $_POST['crop_name'];
    
    // Query the database for nutrition details of the selected crop
    $stmt = $pdo->prepare("SELECT * FROM nutrition_crop WHERE crop_name = ?");
    $stmt->execute([$crop_name]);
    $crop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crop) {
        // Display ideal temperature, humidity, and nutrition values for the crop
        echo "<p><strong>Ideal Temperature:</strong> " . $crop['ideal_temp'] . "°C</p>";
        echo "<p><strong>Ideal Humidity:</strong> " . $crop['ideal_humidity'] . "%</p>";
        
        // Display the crop nutrition values by day ranges
        echo "<h3>Nutrition Value by Days</h3>";
        echo "<p><strong>Day 1-3:</strong> " . $crop['nutrition_value_day_1_3'] . "</p>";
        echo "<p><strong>Day 3-5:</strong> " . $crop['nutrition_value_day_3_5'] . "</p>";
        echo "<p><strong>Day 5-7:</strong> " . $crop['nutrition_value_day_5_7'] . "</p>";
        echo "<p><strong>Day 7-10:</strong> " . $crop['nutrition_value_day_7_10'] . "</p>";
        echo "<p><strong>Day 10-15:</strong> " . $crop['nutrition_value_day_10_15'] . "</p>";
        echo "<p><strong>Day 15+:</strong> " . $crop['nutrition_value_day_15_plus'] . "</p>";
    } else {
        echo "<p style='color:red;'>Crop name not found in the database.</p>";
    }
}
?>

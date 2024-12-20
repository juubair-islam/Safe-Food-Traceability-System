<?php
// Database connection
$host = 'localhost';
$dbname = 'safe_food_traceability'; // Replace with your actual database name
$username = 'root';
$password = ''; // Default password for MySQL on XAMPP (empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_POST['crop_name'])) {
    $crop_name = $_POST['crop_name'];

    // Fetch crop nutrition information from the database
    $query = "SELECT * FROM nutrition_crop WHERE crop_name = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$crop_name]);
    $crop = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crop) {
        // Display the nutrition data for the selected crop
        echo "<p><strong>Crop Name:</strong> " . htmlspecialchars($crop['crop_name']) . "</p>";
        echo "<p><strong>Ideal Temperature (°C):</strong> " . htmlspecialchars($crop['ideal_temp']) . "</p>";
        echo "<p><strong>Ideal Humidity (%):</strong> " . htmlspecialchars($crop['ideal_humidity']) . "</p>";
        echo "<p><strong>Nutrition Value (Day 1-3):</strong> " . htmlspecialchars($crop['nutrition_value_day_1_3']) . "</p>";
        echo "<p><strong>Nutrition Value (Day 3-5):</strong> " . htmlspecialchars($crop['nutrition_value_day_3_5']) . "</p>";
        echo "<p><strong>Nutrition Value (Day 5-7):</strong> " . htmlspecialchars($crop['nutrition_value_day_5_7']) . "</p>";
        echo "<p><strong>Nutrition Value (Day 7-10):</strong> " . htmlspecialchars($crop['nutrition_value_day_7_10']) . "</p>";
        echo "<p><strong>Nutrition Value (Day 10-15):</strong> " . htmlspecialchars($crop['nutrition_value_day_10_15']) . "</p>";
        echo "<p><strong>Nutrition Value (Day 15+):</strong> " . htmlspecialchars($crop['nutrition_value_day_15_plus']) . "</p>";
        echo "<p><strong>Custom Nutrition Value:</strong> " . htmlspecialchars($crop['nutrition_value_custom']) . "</p>";
    } else {
        echo "<p>No nutrition information found for this crop.</p>";
    }
}
?>

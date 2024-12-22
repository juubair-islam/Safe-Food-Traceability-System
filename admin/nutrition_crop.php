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

$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_name = $_POST['crop_name'];
    $ideal_temp = $_POST['ideal_temp'];
    $ideal_humidity = $_POST['ideal_humidity'];
    $nutrition_value_day_1_3 = $_POST['nutrition_value_day_1_3'];
    $nutrition_value_day_3_5 = $_POST['nutrition_value_day_3_5'];
    $nutrition_value_day_5_7 = $_POST['nutrition_value_day_5_7'];
    $nutrition_value_day_7_10 = $_POST['nutrition_value_day_7_10'];
    $nutrition_value_day_10_15 = $_POST['nutrition_value_day_10_15'];
    $nutrition_value_day_15_plus = $_POST['nutrition_value_day_15_plus'];
    $nutrition_value_custom = $_POST['nutrition_value_custom']; // Can be used for additional custom input

    // Check if crop already exists
    $check_query = "SELECT * FROM nutrition_crop WHERE crop_name = ?";
    $stmt_check = $pdo->prepare($check_query);
    $stmt_check->execute([$crop_name]);
    $existing_crop = $stmt_check->fetch();

    if ($existing_crop) {
        $update_query = "UPDATE nutrition_crop SET 
                         ideal_temp = ?, 
                         ideal_humidity = ?, 
                         nutrition_value_day_1_3 = ?, 
                         nutrition_value_day_3_5 = ?, 
                         nutrition_value_day_5_7 = ?, 
                         nutrition_value_day_7_10 = ?, 
                         nutrition_value_day_10_15 = ?, 
                         nutrition_value_day_15_plus = ?, 
                         nutrition_value_custom = ? 
                         WHERE crop_name = ?";
        $stmt_update = $pdo->prepare($update_query);
        $stmt_update->execute([
            $ideal_temp, 
            $ideal_humidity, 
            $nutrition_value_day_1_3, 
            $nutrition_value_day_3_5, 
            $nutrition_value_day_5_7, 
            $nutrition_value_day_7_10, 
            $nutrition_value_day_10_15, 
            $nutrition_value_day_15_plus, 
            $nutrition_value_custom, 
            $crop_name
        ]);

        $success_message = "Crop nutrition data updated successfully!";
    } else {
        $insert_query = "INSERT INTO nutrition_crop (crop_name, ideal_temp, ideal_humidity, 
                         nutrition_value_day_1_3, nutrition_value_day_3_5, 
                         nutrition_value_day_5_7, nutrition_value_day_7_10, 
                         nutrition_value_day_10_15, nutrition_value_day_15_plus, nutrition_value_custom) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($insert_query);
        $stmt_insert->execute([
            $crop_name, 
            $ideal_temp, 
            $ideal_humidity, 
            $nutrition_value_day_1_3, 
            $nutrition_value_day_3_5, 
            $nutrition_value_day_5_7, 
            $nutrition_value_day_7_10, 
            $nutrition_value_day_10_15, 
            $nutrition_value_day_15_plus, 
            $nutrition_value_custom
        ]);

        $success_message = "Crop nutrition data added successfully!";
    }

    echo json_encode(['success_message' => $success_message]);
    exit;
}

// Fetch existing crop names for dropdown
$crop_query = "SELECT DISTINCT crop_name FROM nutrition_crop";
$crop_result = $pdo->query($crop_query);
$crops = $crop_result->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition Crop Data</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1>Enter Nutrition Data for Crops</h1>
</div>

<!-- Main Container -->
<div class="container">
    
    <!-- Success/Error Message -->
    <div id="success-message" class="success"></div>

    <form id="crop-form" method="POST" action="nutrition_crop.php">
        <div class="form-group">
            <label for="crop_name">Crop Name:</label>
            <input type="text" name="crop_name" id="crop_name" required>
        </div>
        <div class="form-group">
            <label for="ideal_temp">Ideal Temperature (°C):</label>
            <input type="number" name="ideal_temp" id="ideal_temp" required>
        </div>
        <div class="form-group">
            <label for="ideal_humidity">Ideal Humidity (%):</label>
            <input type="number" name="ideal_humidity" id="ideal_humidity" required>
        </div>

        <div class="nutrition-values">
            <div class="nutrition-row">
                <div class="form-group">
                    <label for="nutrition_value_day_1_3">Nutrition Value of Day 1-3:</label>
                    <input type="number" step="0.01" name="nutrition_value_day_1_3" id="nutrition_value_day_1_3" required>
                </div>
                <div class="form-group">
                    <label for="nutrition_value_day_3_5">Nutrition Value of 3-5:</label>
                    <input type="number" step="0.01" name="nutrition_value_day_3_5" id="nutrition_value_day_3_5" required>
                </div>
                <div class="form-group">
                    <label for="nutrition_value_day_5_7">Nutrition Value of 5-7:</label>
                    <input type="number" step="0.01" name="nutrition_value_day_5_7" id="nutrition_value_day_5_7" required>
                </div>
            </div>

            <div class="nutrition-row">
                <div class="form-group">
                    <label for="nutrition_value_day_7_10">Nutrition Value of 7-10:</label>
                    <input type="number" step="0.01" name="nutrition_value_day_7_10" id="nutrition_value_day_7_10" required>
                </div>
                <div class="form-group">
                    <label for="nutrition_value_day_10_15">Nutrition Value of 10-15:</label>
                    <input type="number" step="0.01" name="nutrition_value_day_10_15" id="nutrition_value_day_10_15" required>
                </div>
                <div class="form-group">
                    <label for="nutrition_value_day_15_plus">Nutrition Value of Day 15+:</label>
                    <input type="number" step="0.01" name="nutrition_value_day_15_plus" id="nutrition_value_day_15_plus" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="nutrition_value_custom">Custom Nutrition Pattern (Optional):</label>
            <textarea id="nutrition_value_custom" name="nutrition_value_custom" rows="3"></textarea>
        </div>
        
        <button type="submit">Submit Nutrition Data</button>
    </form>

    <h3>Existing Crops</h3>
    <ul id="crop-list">
        <?php foreach ($crops as $crop) : ?>
            <li><?php echo $crop['crop_name']; ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Chart Section -->
    <canvas id="nutrition-chart" width="400" height="200"></canvas>
</div>

<script>
$(document).ready(function() {
    $("#crop-form").submit(function(event) {
        event.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "nutrition_crop.php",
            data: $(this).serialize(),
            success: function(response) {
                var data = JSON.parse(response);
                $("#success-message").text(data.success_message);

                // Optionally clear form inputs after successful submission
                $("#crop-form")[0].reset();

                // Update the crop list (could be from the server or just refresh the list)
                $("#crop-list").load(location.href + " #crop-list");
            }
        });
    });
});
</script>

</body>
</html>

<style> 
/* CSS to make nutrition values in two rows, smaller and more compact */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
}

.container {
    width: 80%;
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Header Styles */
.header {
    background-color: #4CAF50;
    padding: 20px;
    color: #fff;
    text-align: center;
    font-size: 24px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input, textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    margin-top: 5px;
}

button {
    padding: 10px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
}

button:hover {
    background-color: #45a049;
}

/* Nutrition Values Row */
.nutrition-values {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.nutrition-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    width: 100%;
}

/* Adjust form-group width for each input in rows */
.form-group {
    flex: 1;
    min-width: 150px;
}

/* Message Styles */
.success {
    background-color: #28a745;
    color: #fff;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
}

ul {
    list-style-type: none;
    padding-left: 0;
}

ul li {
    padding: 5px;
    background-color: #f2f2f2;
    margin-bottom: 10px;
    border-radius: 4px;
}

/* General reset and layout styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
}

.container {
    width: 80%;
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Header Styles */
.header {
    background-color: #4CAF50;
    padding: 20px;
    color: #fff;
    text-align: center;
    font-size: 24px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input, textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    margin-top: 5px;
}

button {
    padding: 10px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
}

button:hover {
    background-color: #45a049;
}

/* Message Styles */
.success {
    background-color: #28a745;
    color: #fff;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
}

ul {
    list-style-type: none;
    padding-left: 0;
}

ul li {
    padding: 5px;
    background-color: #f2f2f2;
    margin-bottom: 10px;
    border-radius: 4px;
}
</style>

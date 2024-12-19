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
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_name = $_POST['crop_name'];
    $farmer_id = $_POST['farmer_id'];
    $harvest_area = $_POST['harvest_area'];
    $quantity = $_POST['quantity'];
    $harvest_date = $_POST['harvest_date'];
    $batch_date = $_POST['batch_date'];
    $nutrition_value = $_POST['nutrition_value'];

    // Generate a unique Batch ID (e.g., from serial number or use auto-increment)
    $batch_id = uniqid('BATCH-', true); // Alternatively, use a sequence or auto-increment in MySQL
    
    // Insert into `crops` table
    $insert_crop_query = "INSERT INTO crops (name, farmer_id, harvest_area, quantity, harvest_date) 
                          VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insert_crop_query);
    $stmt->execute([$crop_name, $farmer_id, $harvest_area, $quantity, $harvest_date]);

    // Get crop_id of the inserted crop
    $crop_id = $pdo->lastInsertId();
    
    // Insert into `batches` table (with initial certification and nutrition value)
    $certifications = "Nutrition Value Checked: " . date('Y-m-d H:i:s'); // Adding timestamp for certification
    
    $insert_batch_query = "INSERT INTO batches (batch_id, crop_id, status, certifications, quality_check_status, batch_date, nutrition_value) 
                           VALUES (?, ?, 'Storable', ?, 'Pass', ?, ?)";
    $stmt = $pdo->prepare($insert_batch_query);
    $stmt->execute([$batch_id, $crop_id, $certifications, $batch_date, $nutrition_value]);
    
    $success_message = "Crop added successfully with Batch ID: $batch_id";
}

// Fetch crop names and districts for dropdowns
$crop_query = "SELECT * FROM nutrition_crop";
$crop_result = $pdo->query($crop_query);
$crops = $crop_result->fetchAll();

$district_query = "SELECT DISTINCT district FROM farmers";
$district_result = $pdo->query($district_query);
$districts = $district_result->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Crop</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1>Add Crop to Safe Food Traceability System</h1>
</div>

<!-- Main Container -->
<div class="container">
    
    <!-- Success/Error Message -->
    <?php if (!empty($error_message)) : ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php elseif (!empty($success_message)) : ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="add_crop.php">
        <!-- Batch ID (Read-only, auto-generated) -->
        <div class="form-group">
            <label for="batch_id">Batch ID:</label>
            <input type="text" name="batch_id" id="batch_id" value="<?php echo isset($batch_id) ? $batch_id : ''; ?>" readonly>
        </div>

        <!-- Farmer ID and District (Same row) -->
        <div class="form-group">
            <label for="farmer_id">Farmer ID:</label>
            <input type="text" name="farmer_id" id="farmer_id" required>
        </div>
        <div class="form-group">
            <label for="district">District:</label>
            <select name="district" id="district" required>
                <option value="">Select District</option>
                <?php foreach ($districts as $district) : ?>
                    <option value="<?php echo $district['district']; ?>"><?php echo $district['district']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Crop Name Dropdown -->
        <div class="form-group">
            <label for="crop_name">Crop Name:</label>
            <select name="crop_name" id="crop_name" required>
                <option value="">Select Crop</option>
                <?php foreach ($crops as $crop) : ?>
                    <option value="<?php echo $crop['crop_name']; ?>"><?php echo $crop['crop_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Check Nutrition Value Button -->
        <div class="form-group">
            <button type="button" id="check_nutrition_button">Check Nutrition Value</button>
        </div>

        <!-- Nutrition Value Chart (Placeholder) -->
        <h3>Nutrition Value Chart</h3>
        <canvas id="nutritionChart" width="400" height="200"></canvas>

        <!-- Ideal Temperature and Humidity -->
        <h3>Ideal Conditions for Crop</h3>
        <div class="form-group" id="ideal_conditions"></div>
        
        <!-- Additional Crop Details -->
        <div class="form-group">
            <label for="harvest_area">Harvest Area (hectares):</label>
            <input type="number" name="harvest_area" id="harvest_area" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity (kg):</label>
            <input type="number" name="quantity" id="quantity" required>
        </div>
        <div class="form-group">
            <label for="harvest_date">Harvest Date:</label>
            <input type="date" name="harvest_date" id="harvest_date" required>
        </div>
        
        <!-- New Batch Date and Nutrition Value -->
        <div class="form-group">
            <label for="batch_date">Batch Date:</label>
            <input type="date" name="batch_date" id="batch_date" required>
        </div>

        <div class="form-group">
            <label for="nutrition_value">Nutrition Value:</label>
            <input type="number" name="nutrition_value" id="nutrition_value" step="any" required>
        </div>

        <!-- Submit Button -->
        <button type="submit">Add Crop</button>
    </form>
</div>

<script>
// JavaScript to dynamically show crop-related data and nutrition chart
document.getElementById('crop_name').addEventListener('change', function() {
    var cropName = this.value;
    
    if (cropName) {
        fetchCropInfo(cropName);
    }
});

function fetchCropInfo(cropName) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_crop_info.php?crop_name=' + cropName, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data) {
                // Update Nutrition Chart
                updateNutritionChart(data.nutrition_value, data.best_nutrition_value);

                // Show Ideal Conditions for the selected crop
                displayIdealConditions(data);
            }
        }
    };
    xhr.send();
}

document.getElementById('check_nutrition_button').addEventListener('click', function() {
    var nutritionValue = document.getElementById('nutrition_value').value;
    
    // Validate and display chart if nutrition value is entered
    if (nutritionValue) {
        updateNutritionChart(nutritionValue, nutritionValue);  // Example of displaying the same value for comparison
    } else {
        alert('Please enter a valid nutrition value.');
    }
});

function updateNutritionChart(nutritionValue, bestNutritionValue) {
    var ctx = document.getElementById('nutritionChart').getContext('2d');
    var nutritionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Nutrition Value', 'Best Nutrition Value'],
            datasets: [{
                label: 'Nutrition Comparison',
                data: [nutritionValue, bestNutritionValue],
                backgroundColor: ['#4CAF50', '#f44336'],
                borderColor: ['#388E3C', '#D32F2F'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function displayIdealConditions(data) {
    var idealConditions = `
        <p><strong>Ideal Temperature (°C):</strong> ${data.ideal_temperature}</p>
        <p><strong>Ideal Humidity (%):</strong> ${data.ideal_humidity}</p>
        <p><strong>Day 1-3:</strong> ${data.day_1_3}</p>
        <p><strong>Day 3-5:</strong> ${data.day_3_5}</p>
        <p><strong>Day 5-7:</strong> ${data.day_5_7}</p>
        <p><strong>Day 7-10:</strong> ${data.day_7_10}</p>
        <p><strong>Day 10-15:</strong> ${data.day_10_15}</p>
        <p><strong>Day 15+:</strong> ${data.day_15_plus}</p>
    `;
    document.getElementById('ideal_conditions').innerHTML = idealConditions;
}
</script>

</body>
</html>

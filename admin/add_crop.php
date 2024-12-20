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

$cropDetails = '';
$farmerDetails = '';
$success_message = '';
$error_message = '';

// List of districts in Bangladesh for the Harvest Area dropdown
$districts = [
    'Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh', 'Comilla', 'Jessore',
    'Bogura', 'Narsingdi', 'Chandpur', 'Feni', 'Kishoreganj', 'Netrakona', 'Tangail', 'Brahmanbaria', 'Manikganj', 'Cumilla'
];

// Fetch crop names for dropdown
$crop_query = "SELECT * FROM nutrition_crop";
$crop_result = $pdo->query($crop_query);
$crops = $crop_result->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_name = $_POST['crop_name'];
    $farmer_id = $_POST['farmer_id'];
    $harvest_area = $_POST['harvest_area'];
    $quantity = $_POST['quantity'];
    $harvest_date = $_POST['harvest_date'];
    $batch_date = $_POST['batch_date'];
    $nutrition_value = $_POST['nutrition_value'];

    // Check if farmer exists in the database
    $check_farmer_query = "SELECT * FROM farmers WHERE farmer_id = ?";
    $stmt = $pdo->prepare($check_farmer_query);
    $stmt->execute([$farmer_id]);
    $farmer_exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$farmer_exists) {
        $error_message = "The Farmer info is Not In The Database.";
    } else {
        try {
            // Start a transaction
            $pdo->beginTransaction();

            // Insert into crops table
            $insert_crop_query = "INSERT INTO crops (name, farmer_id, harvest_area, quantity, harvest_date) 
                                  VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insert_crop_query);
            $stmt->execute([$crop_name, $farmer_id, $harvest_area, $quantity, $harvest_date]);

            // Get crop_id of the inserted crop
            $crop_id = $pdo->lastInsertId();

            // Insert into batches table (with auto-increment batch_id)
            $nutrition_value_certification = "Nutrition Value Checked: " . date('Y-m-d H:i:s');
            $insert_batch_query = "INSERT INTO batches (crop_id, status, certifications, quality_check_status, batch_date, nutrition_value) 
                                   VALUES (?, 'Storable', ?, 'Pass', ?, ?)";
            $stmt = $pdo->prepare($insert_batch_query);
            $stmt->execute([$crop_id, $nutrition_value_certification, $batch_date, $nutrition_value]);

            // Get the auto-generated batch ID
            $batch_id = $pdo->lastInsertId();

            // Commit the transaction
            $pdo->commit();

            $success_message = "Crop added successfully with Batch ID: $batch_id";
        } catch (PDOException $e) {
            // Roll back the transaction if an error occurs
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = "An error occurred while adding the crop: " . $e->getMessage();
        }
    }
}

// Fetch farmer details when Farmer ID is provided
if (isset($_POST['farmer_id'])) {
    $farmer_id = $_POST['farmer_id'];
    $farmer_query = "SELECT * FROM farmers WHERE farmer_id = ?";
    $stmt = $pdo->prepare($farmer_query);
    $stmt->execute([$farmer_id]);
    $farmer_details = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Crop</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }
  
  h1, h2 {
    color: #2c3e50;
  }
  
/* Header Styles */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: #2c3e50;
    color: white;
  }
  
  .header .logo-container {
    display: flex;
    align-items: center;
    flex: 1;
  }
  
  .header .logo {
    height: 50px;
    margin-right: 15px;
  }
  
  .header .project-name {
    display: flex;
    flex-direction: column;
    font-weight: bold;
    font-size: 20px;
    color: white;
    margin-left: 10px;
  }
  
  .header .project-name span {
    font-size: 12px;
    font-weight: normal;
  }
  
  .header .role {
    font-size: 18px;
    font-weight: bold;
    color: white;
    text-align: right;
    flex: 1; /* Align user role to the right */
  }
  
  /* Top Navigation Bar */
  .top-nav {
    background-color: #34495e;
    color: white;
    display: flex;
    justify-content: center;
    padding: 10px 0;
  }
  
  .top-nav .nav-links {
    display: flex;
    gap: 20px; /* Add spacing between options */
  }
  
  .top-nav .nav-links ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
  }
  
  .top-nav .nav-links ul li {
    display: inline-block;
  }
  
  .top-nav .nav-links ul li a {
    text-decoration: none;
    color: white;
    font-size: 16px;
    padding: 8px 15px; /* Add padding to each option */
    border-radius: 5px;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
    text-align: center;
  }
  
  .top-nav .nav-links ul li a:hover {
    background-color: #16a085; /* Highlight the hovered option */
    color: white;
  }
  
  .top-nav .nav-links ul li a.active {
    background-color: #2980b9;
    color: white;
  }
  
  /* Footer Styles */
  .footer {
    background-color: #2c3e50;
    color: white;
    text-align: center;
    padding: 5px 10px; /* Reduce footer padding for a compact size */
    font-size: 12px; /* Smaller text for the footer */
  }
         
  /* Dashboard Summary */
  .dashboard-content {
    padding: 20px;
  }
  
  .summary-cards {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
  }
  
  .card {
    background-color: #ecf0f1;
    border-radius: 10px;
    padding: 20px;
    width: 200px;
    text-align: center;
  }
  
  .card h3 {
    font-size: 1.5em;
  }
  
  .card p {
    font-size: 2em;
    color: #2c3e50;
  }
  
  /* Action Buttons */
  .action-buttons {
    display: flex;
    justify-content: space-around;
    margin: 20px 0;
  }
  
  .action-buttons button {
    padding: 10px 20px;
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .action-buttons button:hover {
    background-color: #16a085;
  }
  
  /* Recent Activity */
  .recent-activity {
    margin-top: 30px;
  }
  
  .recent-activity ul {
    list-style: none;
    padding: 0;
  }
  
  .recent-activity ul li {
    background-color: #ecf0f1;
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
  }
  

  /* Logout button styling */
.logout-button {
    float: right;
    background-color: #d9534f;
    color: white;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }
  
  .logout-button:hover {
    background-color: #c9302c;
    color: #fff;
  }
  
  /* Ensure top navigation links are aligned correctly */
  .top-nav ul {
    display: flex;
    justify-content: space-between; /* Spread out items including logout */
    align-items: center;
    list-style-type: none;
    margin: 0;
    padding: 0;
  }
  
  .top-nav ul li {
    margin-right: 15px;
  }
  
  .top-nav ul li:last-child {
    margin-right: 0; /* Remove right margin for the last item */
  }


  .top-nav ul li a:hover {
    color: #fff;
    background-color: #4CAF50;
}

/* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
  }
  
  /* Header */
  .header {
    background-color: #2c3e50;
    color: white;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .logo-container {
    display: flex;
    align-items: center;
  }
  
  .logo {
    width: 40px;
    margin-right: 10px;
  }
  
  .project-name {
    font-size: 18px;
  }
  
  .role {
    font-size: 22px;
  }
  
  /* Navigation */
  .top-nav {
    background-color: #34495e;
  }
  
  .top-nav ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    display: flex;
  }
  
  .top-nav li {
    padding: 10px;
  }
  
  .top-nav a {
    text-decoration: none;
    color: white;
    padding: 8px 16px;
    display: block;
  }
  
  .top-nav a:hover {
    background-color: #2980b9;
  }
  
  /* Main Content */
  .dashboard-content {
    padding: 20px;
  }
  
  .add-crop-section {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
  }
  
  .add-crop-button {
    padding: 10px 20px;
    background-color: #27ae60;
    color: white;
    text-decoration: none;
    border-radius: 4px;
  }
  
  .search-bar {
    padding: 8px;
    width: 300px;
    margin-right: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
  }
  
  /* Table */
  .crop-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  
  .crop-table th, .crop-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  
  .crop-table th {
    background-color: #2c3e50;
    color: white;
  }
  
  .crop-table tr:hover {
    background-color: #f1f1f1;
  }
  
  .crop-table td a {
    color: #3498db;
    text-decoration: none;
  }
  
  .crop-table td a:hover {
    text-decoration: underline;
  }
  
  /* Footer */
  .footer {
    background-color: #2c3e50;
    color: white;
    text-align: center;
    padding: 10px;
    margin-top: 20px;
  }
  

  .error {
    color: red;
    font-weight: bold;
}
.success {
    color: green;
    font-weight: bold;
}
  </style>




</head>
<body>

  <!-- Header Section -->
  <div class="header">
    <div class="logo-container">
      <img src="../images/logo.png" alt="Logo" class="logo" />
      <div class="project-name">
        <strong>Farm to Fork</strong> <span>Safe Food Traceability System</span>
      </div>
    </div>
    <div class="role">Admin Dashboard</div>
  </div>

  <!-- Top Navigation Bar -->
  <div class="top-nav">
    <div class="nav-links">
      <ul>
      <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_user.php">Manage Users</a></li>

        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_crop.php"class="active">Manage Batches</a></li>
        <li><a href="http://manage_waste.php">Waste Management</a></li>
        <li><a href="generate_reports.php">Generate Reports</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_farmer.php">Farmers</a></li>
        <li><a href="barcode_management.php">Barcodes</a></li>
        <li><a href="transport_management.php">Transport</a></li>
        <li><a href="../index.php" class="logout-button">Logout</a></li>
      </ul>
    </div>
  </div>

  <div class="container">
    <h2>Add Crop</h2>
    <p>Enter crop details to add them to the system.</p>
    
    <style>
    .container h2, .container p {
        margin-bottom: 20px; /* Adjust the value as needed */
    }
</style>
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

        <!-- Farmer ID Section -->
        <div class="form-group">
            <label for="farmer_id">Farmer ID:</label>
            <input type="text" name="farmer_id" id="farmer_id" required>
            <button type="button" id="check_farmer_btn">Check Farmer Info</button>
        </div>

        <!-- Display Farmer Details -->
        <div id="farmerDetails">
            <?php if (!empty($farmer_details)): ?>
                <p><strong>Name:</strong> <?php echo $farmer_details['name']; ?></p>
                <p><strong>District:</strong> <?php echo $farmer_details['district']; ?></p>
            <?php endif; ?>
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
            <button type="button" id="check_crop_btn">Show Crop Details</button>
        </div>

      <!-- Section to display crop nutrition details -->
      <div id="cropDetails"></div>


        <!-- New Batch Date and Nutrition Value -->
        <div class="form-group">
            <label for="nutrition_value">Nutrition Value:</label>
            <input type="number" name="nutrition_value" id="nutrition_value" step="any" required>
        </div>


        <div class="form-group">
            <label for="batch_date">Batch Date:</label>
            <input type="date" name="batch_date" id="batch_date" required>
        </div>



        <!-- Additional Crop Details -->
        <div class="form-group">
            <label for="harvest_area">Harvest Area (District):</label>
            <select name="harvest_area" id="harvest_area" required>
                <option value="">Select District</option>
                <?php foreach ($districts as $district) : ?>
                    <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity (kg):</label>
            <input type="number" name="quantity" id="quantity" required>
        </div>
        <div class="form-group">
            <label for="harvest_date">Harvest Date:</label>
            <input type="date" name="harvest_date" id="harvest_date" required>
        </div>
        


        <!-- Submit Button -->
        <button type="submit">Add Crop</button>
    </form>
</div>

  <!-- Footer Section -->
  <div class="footer">
    &copy; 2024 Farm to Fork: Safe Food Traceability System. All rights reserved.
  </div>

<script>
// jQuery to handle AJAX request for checking farmer info
$('#check_farmer_btn').on('click', function() {
    var farmerId = $('#farmer_id').val();
    
    if(farmerId != '') {
        $.ajax({
            url: 'check_farmer.php',
            method: 'POST',
            data: {farmer_id: farmerId},
            success: function(response) {
                $('#farmerDetails').html(response);
            },
            error: function() {
                alert('Error retrieving farmer details');
            }
        });
    }
});

// jQuery to handle the request and display crop nutrition details
$('#check_crop_btn').on('click', function() {
    var cropName = $('#crop_name').val();
    
    if(cropName != '') {
        $.ajax({
            url: 'check_crop_nutrition.php',  // Path to your nutrition details file
            method: 'POST',
            data: {crop_name: cropName},
            success: function(response) {
                $('#cropDetails').html(response);  // Display nutrition info in the cropDetails div
            },
            error: function() {
                alert('Error retrieving crop details.');
            }
        });
    } else {
        alert('Please select a crop.');
    }
});

</script>

</body>
</html>




<style>

    
/* Basic Reset */

/* Basic Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 0;
}



/* Main Container */
.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Form Elements */
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

input[type='text'], input[type='number'], input[type='date'], select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

input[type='button'], button[type='submit'] {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
}

input[type='button']:hover, button[type='submit']:hover {
    background-color: #45a049;
}

/* Nutrition Value Chart */
#nutritionChart {
    max-width: 100%;
    margin-top: 30px;
}

/* Success/Error Messages */
.success {
    color: #4CAF50;
    background-color: #e8f5e9;
    padding: 10px;
    border: 1px solid #4CAF50;
    border-radius: 4px;
    margin-bottom: 20px;
}

.error {
    color: #D32F2F;
    background-color: #f8d7da;
    padding: 10px;
    border: 1px solid #D32F2F;
    border-radius: 4px;
    margin-bottom: 20px;
}

/* Ideal Conditions Section */
#ideal_conditions p {
    font-size: 16px;
    color: #333;
    margin-top: 10px;
}

/* Farmer Details Section */
#farmerDetails {
    margin-top: 20px;
    padding: 15px;
    background-color: #f0f0f0;
    border-radius: 4px;
}

#farmerDetails p {
    margin: 5px 0;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .container {
        padding: 15px;
    }

    input[type='text'], input[type='number'], input[type='date'], select {
        padding: 6px;
    }

    input[type='button'], button[type='submit'] {
        font-size: 14px;
    }

    .header h1 {
        font-size: 18px;
    }


    

</style>

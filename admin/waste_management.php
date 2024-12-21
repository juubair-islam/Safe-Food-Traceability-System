<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'safe_food_traceability';
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables and messages
$error_msg_batch = '';
$success_msg_batch = '';
$success_msg_submit = '';
$batch_id = '';
$batch_status = '';
$storage_condition = '';
$batch_date = '';
$crop_name = '';
$quantity = 0;
$returned_amount = 0;
$retailer_id = '';
$error_msg_retailer = '';
$success_msg_retailer = '';
$retailer_name = '';
$retailer_contact = '';
$retailer_location = '';

// Handle retailer search
if (isset($_POST['search_retailer'])) {
    $retailer_id = $_POST['retailer_id'];
    $retailer_query = "SELECT retailer_name, retailer_contact_number, shop_location FROM retailers WHERE retailer_id = ?";
    $stmt = $conn->prepare($retailer_query);
    $stmt->bind_param('i', $retailer_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($retailer_name, $retailer_contact, $retailer_location);
        $stmt->fetch();
        $success_msg_retailer = "Retailer data loaded successfully!";
    } else {
        $error_msg_retailer = "Retailer does not exist.";
    }
    $stmt->close();
}

// Handle batch search
if (isset($_POST['search_batch'])) {
    $batch_id = $_POST['batch_id'];
    $batch_query = "
        SELECT b.status, b.storage_condition, b.batch_date, c.name AS crop_name, c.quantity
        FROM batches b
        LEFT JOIN crops c ON b.batch_id = c.batch_id
        WHERE b.batch_id = ?";
    $stmt = $conn->prepare($batch_query);
    $stmt->bind_param('s', $batch_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($batch_status, $storage_condition, $batch_date, $crop_name, $quantity);
        $stmt->fetch();
        $success_msg_batch = "Batch data loaded successfully!";
    } else {
        $error_msg_batch = "Batch does not exist.";
    }
    $stmt->close();
}

// Handle form submission
if (isset($_POST['submit_waste'])) {
    $batch_id = $_POST['batch_id'];
    $returned_amount = $_POST['returned_amount'];
    $processing_date = date("Y-m-d");
    $retailer_id = $_POST['retailer_id'];

    // Add entry to waste_management table
    $waste_query = "INSERT INTO waste_management (batch_id, amount, processing_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($waste_query);
    $stmt->bind_param('sds', $batch_id, $returned_amount, $processing_date);
    if ($stmt->execute()) {
        // Update batches table to mark status as 'Damaged'
        $update_batch_query = "UPDATE batches SET status = 'Damaged', updated_at = NOW() WHERE batch_id = ?";
        $update_stmt = $conn->prepare($update_batch_query);
        $update_stmt->bind_param('s', $batch_id);
        if ($update_stmt->execute()) {
            $success_msg_submit = "Waste processing completed successfully!";
        } else {
            $error_msg_batch = "Failed to update batch status.";
        }
        $update_stmt->close();
    } else {
        $error_msg_batch = "Failed to add entry to waste management.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waste Management</title>
    <link rel="stylesheet" href="styles.css">
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






        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
        }

        header .header-title h1 {
            margin: 0;
            font-size: 24px;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        nav ul li {
            float: left;
        }

        nav ul li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        nav ul li a.active, nav ul li a:hover {
            background-color: #45a049;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-field {
            margin-bottom: 15px;
        }

        .form-field label {
            display: block;
            font-weight: bold;
        }

        .form-field input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-field button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-field button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }

        .footer-content {
            text-align: center;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
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
      </ul>
    </div>
  </div>

    
    <div class="container">
        <h2>Waste Management</h2>
        <form method="POST">
            <!-- Retailer Section -->
            <div class="form-field">
                <label for="retailer_id">Retailer ID</label>
                <input type="text" id="retailer_id" name="retailer_id" value="<?php echo $retailer_id; ?>" placeholder="Enter Retailer ID">
            </div>
            <div class="form-field">
                <button type="submit" name="search_retailer">Search Retailer</button>
            </div>
            <?php if ($error_msg_retailer): ?>
                <div class="error"><?php echo $error_msg_retailer; ?></div>
            <?php elseif ($success_msg_retailer): ?>
                <div class="success"><?php echo $success_msg_retailer; ?></div>
                <div class="result">
                    <p><strong>Retailer Name:</strong> <?php echo $retailer_name; ?></p>
                    <p><strong>Contact Number:</strong> <?php echo $retailer_contact; ?></p>
                    <p><strong>Shop Location:</strong> <?php echo $retailer_location; ?></p>
                </div>
            <?php endif; ?>

            <!-- Batch Section -->
            <div class="form-field">
                <label for="batch_id">Batch ID</label>
                <input type="text" id="batch_id" name="batch_id" value="<?php echo $batch_id; ?>" placeholder="Enter Batch ID">
            </div>
            <div class="form-field">
                <button type="submit" name="search_batch">Search Batch</button>
            </div>
            <?php if ($error_msg_batch): ?>
                <div class="error"><?php echo $error_msg_batch; ?></div>
            <?php elseif ($success_msg_batch): ?>
                <div class="success"><?php echo $success_msg_batch; ?></div>
                <div class="result">
                    <p><strong>Status:</strong> <?php echo $batch_status; ?></p>
                    <p><strong>Storage Condition:</strong> <?php echo $storage_condition; ?></p>
                    <p><strong>Batch Date:</strong> <?php echo $batch_date; ?></p>
                    <p><strong>Crop Name:</strong> <?php echo $crop_name; ?></p>
                    <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
                </div>
            <?php endif; ?>

            <!-- Waste Management Section -->
            <div class="form-field">
                <label for="returned_amount">Returned Amount</label>
                <input type="number" id="returned_amount" name="returned_amount" value="<?php echo $returned_amount; ?>" placeholder="Enter Returned Amount">
            </div>
            <div class="form-field">
                <button type="submit" name="submit_waste">Process Waste</button>
            </div>
            <?php if ($success_msg_submit): ?>
                <div class="success"><?php echo $success_msg_submit; ?></div>
            <?php endif; ?>
        </form>
    </div>

  <!-- Footer Section -->
  <div class="footer">
    &copy; 2024 Farm to Fork: Safe Food Traceability System. All rights reserved.
  </div>
</body>
</html>

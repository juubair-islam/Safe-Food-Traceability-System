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
    <header>
        <div class="header-title">
            <h1>Safe Food Traceability System</h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="crops.php">Crops</a></li>
                <li><a href="batches.php">Batches</a></li>
                <li><a href="waste.php" class="active">Waste Management</a></li>
            </ul>
        </nav>
    </header>
    
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

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Safe Food Traceability System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

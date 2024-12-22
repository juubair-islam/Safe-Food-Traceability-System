<?php
// config.php

$servername = "localhost";  // Database host (usually localhost)
$username = "root";         // Database username
$password = "";             // Database password (default is empty for XAMPP)
$dbname = "safe_food_traceability"; // Database name

// Create the connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize batch details
$batch = null;
$error_message = null;
$success_message = null;

// Check if a batch_id is entered and perform the necessary action
if (isset($_POST['batch_id']) && !empty($_POST['batch_id'])) {
    $batch_id = mysqli_real_escape_string($conn, $_POST['batch_id']);  // Prevent SQL Injection
    
    // Fetch batch details based on batch_id and join with crops table to get quantity
    $query = "SELECT b.batch_id, c.name AS crop_name, c.quantity, b.status
              FROM batches b
              JOIN crops c ON b.crop_id = c.crop_id
              WHERE b.batch_id = ?";  // Prepared statement

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $batch_id);  // Binding parameter
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $batch = mysqli_fetch_assoc($result);

    if (!$batch) {
        $error_message = "Batch ID not found!";
    }
    mysqli_stmt_close($stmt);
}

// Handle Add to Cold Storage action
if (isset($_POST['add_to_cold_storage']) && isset($batch)) {
    $update_query = "UPDATE batches SET status = 'Cold Storage', updated_at = CURRENT_TIMESTAMP WHERE batch_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 's', $batch_id);
    $update_result = mysqli_stmt_execute($update_stmt);

    if ($update_result) {
        $success_message = "Batch added to cold storage successfully.";
    } else {
        $error_message = "Failed to update batch status.";
    }
    mysqli_stmt_close($update_stmt);
}

// Handle Release from Cold Storage action
if (isset($_POST['release_from_cold_storage']) && isset($batch)) {
    $update_query = "UPDATE batches SET status = 'Released from Cold Storage', updated_at = CURRENT_TIMESTAMP WHERE batch_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 's', $batch_id);
    $update_result = mysqli_stmt_execute($update_stmt);

    if ($update_result) {
        $success_message = "Batch released from cold storage successfully.";
    } else {
        $error_message = "Failed to update batch status.";
    }
    mysqli_stmt_close($update_stmt);
}
?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cold Storage Management & Live Temperature and Humidity</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <
    <style>

body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .dashboard {
            background: #e8f5e9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            text-align: center;
        }

        .cold-storage-container {
            width: 100%;
            max-width: 800px;
            margin-bottom: 40px;
        }

        h2 {
            color: #2e7d32;
            font-size: 24px;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-weight: bold;
        }

        canvas {
            display: block;
            margin: 0 auto;
            width: 100%;
            max-width: 700px;
            height: 400px;
        }

        .content-container {
            padding: 30px;
        }

        .footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: absolute;
            width: 100%;
            bottom: 0;
        }

        .header {
            padding: 20px;
            background-color: #333;
            color: white;
            width: 100%;
        }

        .header .logo-container {
            display: flex;
            align-items: center;
        }

        .header .logo {
            width: 50px;
            height: auto;
            margin-right: 10px;
        }

        .header .project-name {
            font-size: 24px;
            font-weight: bold;
        }

        .header .role {
            font-size: 18px;
        }

        .top-nav {
            background-color: #333;
            color: white;
        }

        .top-nav .nav-links ul {
            padding: 0;
            margin: 0;
            list-style-type: none;
            display: flex;
        }

        .top-nav .nav-links ul li {
            margin-right: 20px;
        }

        .top-nav .nav-links ul li a {
            color: white;
            text-decoration: none;
        }

        .top-nav .nav-links ul li a.active {
            font-weight: bold;
        }

        .content-container h1 {
            font-size: 32px;
            font-weight: bold;
        }
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40;
        }
        .navbar .navbar-brand, .navbar .navbar-nav .nav-link {
            color: #fff;
        }
        .navbar .navbar-brand:hover, .navbar .navbar-nav .nav-link:hover {
            color: #d3d3d3;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        .content-container {
            margin-top: 30px;
        }
        .alert {
            margin-top: 20px;
        }
        .form-group input {
            width: 300px;
        }
        table {
            margin-top: 20px;
        }

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
      <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/admin_dashboard.php">Dashboard</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_users.php">Manage Users</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_farmer.php">Farmer Details</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_crop.php">Manage Batches</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_retailer.php">Retailer</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/waste_management.php">Waste Management</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/nutrition_crop.php">Crop Nutritionist</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/cold_storage.php"class="active">Cold Storage</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/login.php" class="logout-button">Logout</a></li>
      </ul>
    </div>
  </div>


<!-- Main Content -->
<div class="container content-container">
    <h1 class="text-center">Cold Storage Management</h1>

    <!-- Error and Success Messages -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Batch ID Form -->
    <form method="POST">
        <div class="form-group">
            <label for="batch_id">Enter Batch ID</label>
            <input type="text" class="form-control" id="batch_id" name="batch_id" required>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Fetch Batch Details</button>
    </form>

    <!-- Batch Details Table -->
    <?php if (isset($batch)): ?>
    <h3 class="mt-4">Batch Details</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop Name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo $batch['batch_id']; ?></td>
                <td><?php echo $batch['crop_name']; ?></td>
                <td><?php echo $batch['quantity']; ?></td>
                <td><?php echo $batch['status']; ?></td>
                <td>
                    <!-- Add to Cold Storage Button -->
                    <?php if ($batch['status'] != 'Cold Storage'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                            <button type="submit" name="add_to_cold_storage" class="btn btn-success">Add to Cold Storage</button>
                        </form>
                    <?php endif; ?>

                    <!-- Release from Cold Storage Button -->
                    <?php if ($batch['status'] == 'Cold Storage'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                            <button type="submit" name="release_from_cold_storage" class="btn btn-warning">Release from Cold Storage</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Batch Table for Displaying All Batches in Cold Storage -->
    <h3>All Batches in Cold Storage</h3>
    <table class="table table-bordered" id="coldStorageTable">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop Name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch batches in cold storage and join with crops table to get quantity
            $query = "SELECT b.batch_id, c.name AS crop_name, c.quantity, b.status
                      FROM batches b
                      JOIN crops c ON b.crop_id = c.crop_id
                      WHERE b.status = 'Cold Storage'";
            $result = mysqli_query($conn, $query);

            while ($batch = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$batch['batch_id']}</td>
                        <td>{$batch['crop_name']}</td>
                        <td>{$batch['quantity']}</td>
                        <td>{$batch['status']}</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='batch_id' value='{$batch['batch_id']}'>
                                <button type='submit' name='release_from_cold_storage' class='btn btn-warning'>Release from Cold Storage</button>
                            </form>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
    
</div>

    <!-- Live Temperature and Humidity Graph -->
    <h2>Live Temperature and Humidity Graph</h2>
    <canvas id="liveTempHumidityChart"></canvas>

  </div>

  <script>
    const ctx = document.getElementById('liveTempHumidityChart').getContext('2d');
    const timeLabels = [
        '00:00', '00:05', '00:10', '00:15', '00:20',
        '00:25', '00:30', '00:35', '00:40', '00:45'
    ];

    const liveChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: Array(10).fill(0),
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                },
                {
                    label: 'Humidity (%)',
                    data: Array(10).fill(0),
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Live Temperature and Humidity Graph' }
            },
            scales: {
                x: { type: 'category', title: { text: 'Time' } },
                y: { beginAtZero: true, title: { text: 'Value' } }
            }
        }
    });

    function generateRandomData() {
        const temperature = Math.random() * 15 + 20;
        const humidity = Math.random() * 50 + 30;
        return { temperature: temperature.toFixed(1), humidity: humidity.toFixed(1) };
    }

    function updateChart() {
        const { temperature, humidity } = generateRandomData();

        liveChart.data.datasets[0].data.shift();
        liveChart.data.datasets[0].data.push(temperature);

        liveChart.data.datasets[1].data.shift();
        liveChart.data.datasets[1].data.push(humidity);

        liveChart.update();
    }

    setInterval(updateChart, 1000);
  </script>






<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    // Initialize the data table for displaying batches
    $(document).ready(function() {
        $('#coldStorageTable').DataTable();
    });
</script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>

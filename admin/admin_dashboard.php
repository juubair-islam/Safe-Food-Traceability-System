<?php
// config.php

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "safe_food_traceability";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total farmer count
$query_farmers = "SELECT COUNT(*) AS total_farmers FROM farmers";
$total_farmers = $conn->query($query_farmers)->fetch_assoc()['total_farmers'];

// Fetch total retailer count
$query_retailers = "SELECT COUNT(*) AS total_retailers FROM retailers";
$total_retailers = $conn->query($query_retailers)->fetch_assoc()['total_retailers'];

// Fetch harvest district vs crops
$query_area_crops = "SELECT harvest_district, COUNT(crop_id) AS crop_count FROM crops GROUP BY harvest_district";
$area_crops_data = $conn->query($query_area_crops)->fetch_all(MYSQLI_ASSOC);

// Fetch crop distribution
$query_crop_distribution = "SELECT name AS crop_name, COUNT(*) AS crop_count FROM crops GROUP BY name";
$crop_distribution_data = $conn->query($query_crop_distribution)->fetch_all(MYSQLI_ASSOC);

// Fetch batch status
$query_batch_status = "SELECT status, COUNT(*) AS batch_count FROM batches WHERE status IN ('Storable', 'Non-Storable', 'Damaged', 'Cold storage', 'Released from cold storage') GROUP BY status";
$batch_status_data = $conn->query($query_batch_status)->fetch_all(MYSQLI_ASSOC);

// Fetch crop vs harvest area size
$query_crop_area_size = "SELECT name AS crop_name, SUM(harvest_area_size) AS total_area_size FROM crops GROUP BY name";
$crop_area_size_data = $conn->query($query_crop_area_size)->fetch_all(MYSQLI_ASSOC);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
            flex: 1;
        }

        .top-nav {
            background-color: #34495e;
            color: white;
            display: flex;
            justify-content: center;
            padding: 10px 0;
        }

        .top-nav .nav-links {
            display: flex;
            gap: 20px;
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
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .top-nav .nav-links ul li a:hover {
            background-color: #16a085;
            color: white;
        }

        .top-nav .nav-links ul li a.active {
            background-color: #2980b9;
            color: white;
        }

        .footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 5px 10px;
            font-size: 12px;
        }

        .container {
            margin: 20px;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            margin-right: 10px;
        }

        .form-group input, .form-group select {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #2ecc71;
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


        .custom-gap {
            margin-right: 20px;  /* Custom margin between columns */
        }

        .custom-row-gap {
            margin-top: 50px;  /* Custom gap between rows */
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
      <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/admin_dashboard.php"class="active">Dashboard</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_users.php">Manage Users</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_farmer.php">Farmer Details</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_crop.php">Manage Batches</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_retailer.php">Retailer</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/waste_management.php">Waste Management</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/nutrition_crop.php">Crop Nutritionist</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/cold_storage.php">Cold Storage</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/login.php" class="logout-button">Logout</a></li>
      </ul>
    </div>
  </div>

<!-- Dashboard Content -->
<div class="dashboard-content">
    <div class="summary-cards">
        <div class="card">
            <h3>Total Farmers</h3>
            <p><?php echo $total_farmers; ?></p>
        </div>
        <div class="card">
            <h3>Total Retailers</h3>
            <p><?php echo $total_retailers; ?></p>
        </div>
    </div>



    <div class="row custom-row-gap"> <!-- Added margin-top here -->
    <div class="col-md-6">
                <h3>Batch Status</h3>
                <canvas id="batchStatusChart"></canvas>
    </div>
    <div class="col-md-6">
                 <h3>Crop Distribution Across Districts</h3>
                <canvas id="areaCropsChart"></canvas>
    </div>
</div>

    

<div class="row mt-4"> <!-- Added margin-top here -->
<div class="col-md-6">
                 <h3>Crop Distribution (Pie Chart)</h3>
                <canvas id="cropDistributionChart"></canvas>
            </div>

            <div class="col-md-6">
                <h3>Crop vs Harvest Area Size</h3>
                <canvas id="cropAreaSizeLineChart"></canvas>
            </div>
</div>






        </div>
    </div>
</div>

  <!-- Footer Section -->
  <div class="footer">
    &copy; 2024 Farm to Fork: Safe Food Traceability System. All rights reserved.
  </div>


<script>
// Crop vs Harvest Area Size Line Chart
const cropAreaSizeLineCtx = document.getElementById('cropAreaSizeLineChart').getContext('2d');
const cropAreaSizeData = <?= json_encode($crop_area_size_data); ?>;
const cropAreaSizeChart = new Chart(cropAreaSizeLineCtx, {
    type: 'line',
    data: {
        labels: cropAreaSizeData.map(data => data.crop_name),
        datasets: [{
            label: 'Crop vs Harvest Area Size',
            data: cropAreaSizeData.map(data => data.total_area_size),
            backgroundColor: 'rgba(241, 196, 15, 0.5)',
            borderColor: 'rgba(241, 196, 15, 1)',
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: { title: { display: true, text: 'Crop Name' } },
            y: { title: { display: true, text: 'Harvest Area Size (in acres)' }, beginAtZero: true }
        }
    }
});

// Batch Status Bar Chart
const batchStatusCtx = document.getElementById('batchStatusChart').getContext('2d');
const batchStatusData = <?= json_encode($batch_status_data); ?>;
const batchStatusChart = new Chart(batchStatusCtx, {
    type: 'bar',
    data: {
        labels: batchStatusData.map(data => data.status),
        datasets: [{
            label: 'Batch Status',
            data: batchStatusData.map(data => data.batch_count),
            backgroundColor: 'rgba(46, 204, 113, 0.6)',
            borderColor: 'rgba(46, 204, 113, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: { title: { display: true, text: 'Batch Status' } },
            y: { beginAtZero: true }
        }
    }
});

// Area vs Crops Scattered Line Chart
const areaCropsCtx = document.getElementById('areaCropsChart').getContext('2d');
const areaCropsData = <?= json_encode($area_crops_data); ?>;
const areaCropsChart = new Chart(areaCropsCtx, {
    type: 'line',
    data: {
        labels: areaCropsData.map(data => data.harvest_district),
        datasets: [{
            label: 'Crops in District',
            data: areaCropsData.map(data => data.crop_count),
            backgroundColor: 'rgba(52, 152, 219, 0.6)',
            borderColor: 'rgba(52, 152, 219, 1)',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: { title: { display: true, text: 'Harvest District' } },
            y: { beginAtZero: true }
        }
    }
});

// Crop Distribution Pie Chart
const cropDistributionCtx = document.getElementById('cropDistributionChart').getContext('2d');
const cropDistributionData = <?= json_encode($crop_distribution_data); ?>;
const cropDistributionChart = new Chart(cropDistributionCtx, {
    type: 'pie',
    data: {
        labels: cropDistributionData.map(data => data.crop_name),
        datasets: [{
            label: 'Crop Distribution',
            data: cropDistributionData.map(data => data.crop_count),
            backgroundColor: [
                'rgba(46, 204, 113, 0.6)',
                'rgba(52, 152, 219, 0.6)',
                'rgba(241, 196, 15, 0.6)',
                'rgba(231, 76, 60, 0.6)',
                'rgba(155, 89, 182, 0.6)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

</script>

</body>
</html>

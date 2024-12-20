<?php
// config.php

// Database credentials
$servername = "localhost";  // Your database host (e.g., 'localhost' or an IP address)
$username = "root";         // Your database username
$password = "";             // Your database password (default is empty for XAMPP)
$dbname = "safe_food_traceability"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total farmer count
$query_farmers = "SELECT COUNT(*) AS total_farmers FROM farmers";
$result_farmers = $conn->query($query_farmers);
$total_farmers = $result_farmers->fetch_assoc()['total_farmers'];

// Fetch total retailer count
$query_retailers = "SELECT COUNT(*) AS total_retailers FROM retailers";
$result_retailers = $conn->query($query_retailers);
$total_retailers = $result_retailers->fetch_assoc()['total_retailers'];

// Fetch harvest district vs crops (scattered line chart data)
$query_area_crops = "SELECT harvest_district, COUNT(crop_id) AS crop_count FROM crops GROUP BY harvest_district";
$result_area_crops = $conn->query($query_area_crops);
$area_crops_data = [];
while ($row = $result_area_crops->fetch_assoc()) {
    $area_crops_data[] = $row;
}

// Fetch crop distribution (pie chart data)
$query_crop_distribution = "SELECT name AS crop_name, COUNT(*) AS crop_count FROM crops GROUP BY name";
$result_crop_distribution = $conn->query($query_crop_distribution);
$crop_distribution_data = [];
while ($row = $result_crop_distribution->fetch_assoc()) {
    $crop_distribution_data[] = $row;
}

// Fetch batch status (bar chart data) with updated status values
$query_batch_status = "SELECT status, COUNT(*) AS batch_count FROM batches WHERE status IN ('Storable', 'Non-Storable', 'Damaged', 'Cold storage', 'Released from cold storage') GROUP BY status";
$result_batch_status = $conn->query($query_batch_status);
$batch_status_data = [];
while ($row = $result_batch_status->fetch_assoc()) {
    $batch_status_data[] = $row;
}

// Fetch crop vs harvest area size (scattered graph data)
$query_crop_area_size = "SELECT name AS crop_name, harvest_area_size FROM crops";
$result_crop_area_size = $conn->query($query_crop_area_size);
$crop_area_size_data = [];
while ($row = $result_crop_area_size->fetch_assoc()) {
    $crop_area_size_data[] = $row;
}




$query_crop_area_size = "SELECT name AS crop_name, SUM(harvest_area_size) AS total_area_size FROM crops GROUP BY name";
$result_crop_area_size = $conn->query($query_crop_area_size);
$crop_area_size_data = [];
while ($row = $result_crop_area_size->fetch_assoc()) {
    $crop_area_size_data[] = $row;
}
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
            <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_user.php">Manage Users</a></li>
            <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_crop.php" >Manage Batches</a></li>
            <li><a href="http://manage_waste.php">Waste Management</a></li>
            <li><a href="generate_reports.php">Generate Reports</a></li>
            <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_farmer.php">Farmers</a></li>
            <li><a href="barcode_management.php">Barcodes</a></li>
            <li><a href="transport_management.php">Transport</a></li>
            <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_retailer.php">Retailers</a></li>
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
    <p>&copy; 2024 Farm to Fork. All rights reserved.</p>
</div>

<script>
// Crop vs Harvest Area Size Line Chart with Connecting Lines
const cropAreaSizeLineCtx = document.getElementById('cropAreaSizeLineChart').getContext('2d');
const cropAreaSizeData = <?= json_encode($crop_area_size_data); ?>; // The data you retrieved from the SQL query

// Grouping by crop name and summing the harvest area sizes
const groupedCropAreaSizeData = cropAreaSizeData.reduce((acc, data) => {
    if (acc[data.crop_name]) {
        acc[data.crop_name].y += parseFloat(data.harvest_area_size);  // Sum the area if the crop name is the same
    } else {
        acc[data.crop_name] = { x: data.crop_name, y: parseFloat(data.harvest_area_size) };
    }
    return acc;
}, {});

const groupedData = Object.values(groupedCropAreaSizeData);

// Crop vs Harvest Area Size Chart with connecting lines
const cropAreaSizeChart = new Chart(cropAreaSizeLineCtx, {
    type: 'line', // Changed from 'scatter' to 'line' for proper line chart behavior
    data: {
        labels: groupedData.map(data => data.x),
        datasets: [{
            label: 'Crop vs Harvest Area Size',
            data: groupedData.map(data => ({
                x: data.x,  // Crop name
                y: data.y   // Harvest area size (summed)
            })),
            backgroundColor: 'rgba(241, 196, 15, 1)',
            borderColor: 'rgba(241, 196, 15, 1)',
            borderWidth: 1,
            fill: false,
            showLine: true,  // Ensure the line is shown
            tension: 0.1 // Line smoothing (optional)
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                type: 'category',
                labels: groupedData.map(data => data.x),
                title: {
                    display: true,
                    text: 'Crop Name'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Harvest Area Size'
                }
            }
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
            backgroundColor: 'rgba(46, 204, 113, 0.5)',
            borderColor: 'rgba(46, 204, 113, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true
            }
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
            fill: false,
            borderColor: 'rgba(52, 152, 219, 1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true
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
                'rgba(155, 89, 182, 0.6)',
            ],
            borderColor: [
                'rgba(46, 204, 113, 1)',
                'rgba(52, 152, 219, 1)',
                'rgba(241, 196, 15, 1)',
                'rgba(231, 76, 60, 1)',
                'rgba(155, 89, 182, 1)',
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

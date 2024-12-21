<?php
// Database connection
$host = 'localhost';
$dbname = 'safe_food_traceability';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$success_message = '';
$error_message = '';

// Handle Approve and Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $batch_id = $_POST['batch_id'];
        $current_date = date('Y-m-d H:i:s');

        try {
            // Approve the batch and update certifications and processing date
            $stmt = $pdo->prepare("UPDATE batches 
                                   SET certifications = CONCAT(IFNULL(certifications, ''), 'Quality Check Passed'), 
                                       quality_check_status = 'Pass', 
                                       processing_date = ? 
                                   WHERE batch_id = ?");
            $stmt->execute([$current_date, $batch_id]);
            $success_message = "Batch $batch_id approved successfully!";
        } catch (PDOException $e) {
            $error_message = "An error occurred while approving batch $batch_id: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete'])) {
        $batch_id = $_POST['batch_id'];

        try {
            // Remove certifications and reset quality check status
            $stmt = $pdo->prepare("UPDATE batches 
                                   SET certifications = NULL, quality_check_status = 'Pending' 
                                   WHERE batch_id = ?");
            $stmt->execute([$batch_id]);
            $success_message = "Batch $batch_id certification deleted successfully!";
        } catch (PDOException $e) {
            $error_message = "An error occurred while deleting certification for batch $batch_id: " . $e->getMessage();
        }
    }
}

// Fetch certified batches with crop names
$certified_batches_query = "
    SELECT b.batch_id, c.name AS crop_name, b.certifications, b.quality_check_status, b.processing_date
    FROM batches b
    INNER JOIN crops c ON b.crop_id = c.crop_id
    WHERE b.certifications IS NOT NULL";
$certified_batches = $pdo->query($certified_batches_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending batches with crop names
$pending_batches_query = "
    SELECT b.batch_id, c.name AS crop_name, b.status, b.quality_check_status
    FROM batches b
    INNER JOIN crops c ON b.crop_id = c.crop_id
    WHERE b.certifications IS NULL OR b.certifications = ''";
$pending_batches = $pdo->query($pending_batches_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Crops and Batches</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Header Section */
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

        /* Top Navigation Bar */
        .top-nav {
            background-color: #34495e;
            color: white;
        }

        .top-nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
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
        .container {
            padding: 20px;
        }

        h1, h2 {
            color: #2c3e50;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #2c3e50;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        /* Form Buttons */
        button {
            padding: 8px 15px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #16a085;
        }

        /* Success and Error Messages */
        .success-message {
            color: green;
        }

        .error-message {
            color: red;
        }

        /* Footer Section */
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
    <ul>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_user.php">Manage Users</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_crop.php" class="active">Manage Batches</a></li>
        <li><a href="http://manage_waste.php">Waste Management</a></li>
        <li><a href="generate_reports.php">Generate Reports</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_farmer.php">Farmers</a></li>
        <li><a href="barcode_management.php">Barcodes</a></li>
        <li><a href="transport_management.php">Transport</a></li>
        <li><a href="../index.php" class="logout-button">Logout</a></li>
    </ul>
  </div>

  <div class="container">
    <h1>View Crops and Batches</h1>

    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Certified Batches Table -->
    <h2>Certified Batches</h2>
    <table>
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop Name</th>
                <th>Certifications</th>
                <th>Quality Check Status</th>
                <th>Processing Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($certified_batches as $batch): ?>
                <tr>
                    <td><?php echo $batch['batch_id']; ?></td>
                    <td><?php echo $batch['crop_name']; ?></td>
                    <td><?php echo $batch['certifications']; ?></td>
                    <td><?php echo $batch['quality_check_status']; ?></td>
                    <td><?php echo $batch['processing_date']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pending Batches Table -->
    <h2>Pending Batches</h2>
    <table>
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop Name</th>
                <th>Status</th>
                <th>Quality Check Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_batches as $batch): ?>
                <tr>
                    <td><?php echo $batch['batch_id']; ?></td>
                    <td><?php echo $batch['crop_name']; ?></td>
                    <td><?php echo $batch['status']; ?></td>
                    <td><?php echo $batch['quality_check_status']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                            <button type="submit" name="approve">Approve</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                            <button type="submit" name="delete">Delete Certification</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
  </div>

  <!-- Footer Section -->
  <div class="footer">
    <p>&copy; 2024 Safe Food Traceability System. All rights reserved.</p>
  </div>

</body>
</html>

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
    <title>Cold Storage</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <style>
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
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Safe Food Traceability System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="#">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Log Out</a>
            </li>
        </ul>
    </div>
</nav>

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

<!-- Footer -->
<div class="footer">
    <p>&copy; 2024 Safe Food Traceability System | All Rights Reserved</p>
</div>

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

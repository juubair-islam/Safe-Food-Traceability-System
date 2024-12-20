<?php
// Database connection
$host = 'localhost';
$dbname = 'safe_food_traceability';
$username = 'root';
$password = '';

try {
    // Create a PDO instance and connect to the database
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

        // Approve action: Update certification
        $approve_query = "UPDATE batches SET certifications = CONCAT(certifications, ', QS Passed'), quality_check_status = 'Pass' WHERE batch_id = ?";
        $stmt = $pdo->prepare($approve_query);
        $stmt->execute([$batch_id]);
        $success_message = "Batch $batch_id approved successfully!";
    } elseif (isset($_POST['delete'])) {
        $batch_id = $_POST['batch_id'];

        // Delete action: Remove certification
        $delete_query = "UPDATE batches SET certifications = NULL, quality_check_status = 'Pending' WHERE batch_id = ?";
        $stmt = $pdo->prepare($delete_query);
        $stmt->execute([$batch_id]);
        $success_message = "Batch $batch_id certification deleted successfully!";
    }
}

// Fetch all batches with certifications
$batches_query = "SELECT * FROM batches WHERE certifications IS NOT NULL";
$batches_result = $pdo->query($batches_query);
$batches = $batches_result->fetchAll(PDO::FETCH_ASSOC);

// Fetch all batches with pending certifications
$pending_query = "SELECT * FROM batches WHERE certifications IS NULL OR certifications = ''";
$pending_result = $pdo->query($pending_query);
$pending_batches = $pending_result->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Crops and Batches</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your CSS for better design -->
</head>
<body>

<h1>View Crops and Batches</h1>

<!-- Success/Error Messages -->
<?php if ($success_message): ?>
    <p class="success"><?php echo $success_message; ?></p>
<?php endif; ?>

<?php if ($error_message): ?>
    <p class="error"><?php echo $error_message; ?></p>
<?php endif; ?>

<!-- Table 1: Batch Data with Certifications -->
<h2>Batch Data with Certifications</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Batch ID</th>
            <th>Crop ID</th>
            <th>Certifications</th>
            <th>Status</th>
            <th>Quality Check Status</th>
            <th>Nutrition Value</th>
            <th>Batch Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($batches as $batch): ?>
            <tr>
                <td><?php echo $batch['batch_id']; ?></td>
                <td><?php echo $batch['crop_id']; ?></td>
                <td><?php echo $batch['certifications']; ?></td>
                <td><?php echo $batch['status']; ?></td>
                <td><?php echo $batch['quality_check_status']; ?></td>
                <td><?php echo $batch['nutrition_value']; ?></td>
                <td><?php echo $batch['batch_date']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Table 2: Pending Certifications -->
<h2>Pending Certifications</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Batch ID</th>
            <th>Crop ID</th>
            <th>Status</th>
            <th>Quality Check Status</th>
            <th>Batch Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pending_batches as $batch): ?>
            <tr>
                <td><?php echo $batch['batch_id']; ?></td>
                <td><?php echo $batch['crop_id']; ?></td>
                <td><?php echo $batch['status']; ?></td>
                <td><?php echo $batch['quality_check_status']; ?></td>
                <td><?php echo $batch['batch_date']; ?></td>
                <td>
                    <!-- Approve Button -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                        <button type="submit" name="approve">Approve</button>
                    </form>
                    <!-- Delete Button -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

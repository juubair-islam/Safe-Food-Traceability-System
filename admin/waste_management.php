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

$success_message = '';
$error_message = '';

// Fetch existing waste batches from the database
$waste_query = "SELECT wm.*, u.name AS retailer_name 
                FROM waste_management wm
                LEFT JOIN retailers r ON wm.retailer_id = r.retailer_id
                LEFT JOIN users u ON r.retailer_user_id = u.user_id";  // Join retailers with users to get retailer name
$waste_result = $pdo->query($waste_query);
$waste_batches = $waste_result->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for waste management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_id = $_POST['batch_id'];
    $retailer_id = $_POST['retailer_id'];
    $return_status = $_POST['return_status'];
    $return_date = $_POST['return_date'];
    $bag_type = $_POST['bag_type'];
    $revenue_from_return = $_POST['revenue_from_return'];

    try {
        // Insert into the waste_management table
        $insert_waste_query = "INSERT INTO waste_management (batch_id, retailer_id, return_status, return_date, bag_type, revenue_from_return) 
                               VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insert_waste_query);
        $stmt->execute([$batch_id, $retailer_id, $return_status, $return_date, $bag_type, $revenue_from_return]);

        $success_message = "Waste batch added successfully!";
    } catch (PDOException $e) {
        $error_message = "An error occurred while adding the waste batch: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waste Management</title>
    <link rel="stylesheet" href="styles.css"> <!-- Ensure you have a proper style.css linked -->
</head>
<body>
    <div class="container">
        <h2>Manage Waste Batches</h2>

        <!-- Display success or error message -->
        <?php if ($success_message) : ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message) : ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Add Waste Batch Form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="batch_id">Batch ID:</label>
                <input type="text" name="batch_id" id="batch_id" required>
            </div>
            <div class="form-group">
                <label for="retailer_id">Retailer:</label>
                <select name="retailer_id" id="retailer_id" required>
                    <option value="">Select Retailer</option>
                    <?php
                    // Fetch retailer options from the retailers table
                    $retailer_query = "SELECT retailer_id, retailer_user_id FROM retailers";
                    $retailer_result = $pdo->query($retailer_query);
                    $retailers = $retailer_result->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($retailers as $retailer) :
                        // Fetch retailer's name using retailer_user_id
                        $user_query = "SELECT name FROM users WHERE user_id = ?";
                        $user_stmt = $pdo->prepare($user_query);
                        $user_stmt->execute([$retailer['retailer_user_id']]);
                        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                        <option value="<?php echo $retailer['retailer_id']; ?>"><?php echo $user['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="return_status">Return Status:</label>
                <select name="return_status" id="return_status" required>
                    <option value="Returned">Returned</option>
                    <option value="Not Returned">Not Returned</option>
                </select>
            </div>
            <div class="form-group">
                <label for="return_date">Return Date:</label>
                <input type="date" name="return_date" id="return_date" required>
            </div>
            <div class="form-group">
                <label for="bag_type">Bag Type:</label>
                <input type="text" name="bag_type" id="bag_type" required>
            </div>
            <div class="form-group">
                <label for="revenue_from_return">Revenue From Return:</label>
                <input type="number" step="0.01" name="revenue_from_return" id="revenue_from_return" value="0.00" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Waste Batch</button>
        </form>

        <h3>Existing Waste Batches</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Retailer Name</th>
                    <th>Return Status</th>
                    <th>Return Date</th>
                    <th>Bag Type</th>
                    <th>Revenue From Return</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($waste_batches as $waste) : ?>
                    <tr>
                        <td><?php echo $waste['batch_id']; ?></td>
                        <td><?php echo $waste['retailer_name']; ?></td>
                        <td><?php echo $waste['return_status']; ?></td>
                        <td><?php echo $waste['return_date']; ?></td>
                        <td><?php echo $waste['bag_type']; ?></td>
                        <td><?php echo number_format($waste['revenue_from_return'], 2); ?></td>
                        <td>
                            <a href="edit_waste_batch.php?batch_id=<?php echo $waste['batch_id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_waste_batch.php?batch_id=<?php echo $waste['batch_id']; ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="script.js"></script> <!-- Ensure you have proper JS functionality if required -->
</body>
</html>

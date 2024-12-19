<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'safe_food_traceability';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch and display users
$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

// Fetch and display farmers
$sql_farmers = "SELECT * FROM farmers";
$result_farmers = $conn->query($sql_farmers);

// Fetch and display crops
$sql_crops = "SELECT * FROM crops";
$result_crops = $conn->query($sql_crops);

// Fetch and display batches
$sql_batches = "SELECT * FROM batches";
$result_batches = $conn->query($sql_batches);
?>

<h3>Users:</h3>
<?php while ($row = $result_users->fetch_assoc()) { ?>
    <p><?php echo $row['name']; ?> (<?php echo $row['role']; ?>)</p>
<?php } ?>

<h3>Farmers:</h3>
<?php while ($row = $result_farmers->fetch_assoc()) { ?>
    <p><?php echo $row['name']; ?> (<?php echo $row['district']; ?>)</p>
<?php } ?>

<h3>Crops:</h3>
<?php while ($row = $result_crops->fetch_assoc()) { ?>
    <p><?php echo $row['name']; ?> (<?php echo $row['variety']; ?>)</p>
<?php } ?>

<h3>Batches:</h3>
<?php while ($row = $result_batches->fetch_assoc()) { ?>
    <p><?php echo $row['status']; ?> (<?php echo $row['barcode']; ?>)</p>
<?php } ?>

<?php
$conn->close();
?>

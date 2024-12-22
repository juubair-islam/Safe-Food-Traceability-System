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


// Initialize variables
$batchData = [];
$message = '';
$errorMessage = '';

if (isset($_POST['search_batch'])) {
    $batch_id = $_POST['batch_id'];

    // Prepare SQL query to fetch batch data
    $sql = "SELECT b.batch_id, b.status, b.certifications, b.batch_date, b.nutrition_value, b.location, 
                   c.name AS crop_name, c.harvest_area, c.harvest_date, c.harvest_district 
            FROM batches b
            LEFT JOIN crops c ON b.batch_id = c.batch_id
            WHERE b.batch_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $batch_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $batchData = $result->fetch_assoc();
            $message = "Thanks For Choosing Us, Your Food Is Safe and Secure!";
        } else {
            $errorMessage = "Extremely Sorry, We can not find your batch in our database.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm To Fork - Safe Food Traceability System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
/* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: url('../images/abstract-pattern.png') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
    animation: fadeIn 1s ease-in-out;
    background-color: #e0e7ff; /* Light blue background */
}

header {
    background-color: #003366; /* Deep navy blue */
    color: white;
    text-align: center;
    padding: 20px 0;
    animation: slideIn 1s ease-in-out;
}

header img {
    width: 80px;
    margin-bottom: 10px;
}

.header-text {
    font-size: 36px;
    font-weight: bold;
}

.sub-title {
    font-size: 18px;
}

.login-btn {
    background-color: #fff;
    color: #003366; /* Deep navy blue */
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    position: absolute;
    top: 20px;
    right: 20px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.login-btn:hover {
    background-color: #c8e1f1; /* Light blue hover effect */
}

.search-container {
    margin: 20px auto;
    text-align: center;
    animation: fadeIn 2s ease-in-out;
}

.search-container input[type="text"] {
    padding: 12px;
    font-size: 18px;
    width: 250px;
    margin-right: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.search-container input[type="submit"] {
    padding: 12px 20px;
    font-size: 18px;
    background-color: #003366; /* Deep navy blue */
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.search-container input[type="submit"]:hover {
    background-color: #002244; /* Even darker navy on hover */
}

.batch-details {
    margin-top: 20px;
    border-collapse: collapse;
    width: 100%;
    animation: fadeIn 2s ease-in-out;
}

.batch-details th, .batch-details td {
    padding: 12px;
    border: 1px solid #ddd;
}

.batch-details th {
    background-color: #003366; /* Deep navy blue */
    color: white;
    text-align: center;
}

.batch-details td {
    text-align: center;
    color: #000000; /* Black text for table data */
}

.message {
    text-align: center;
    font-size: 20px;
    color: #003366; /* Deep navy blue */
    font-weight: bold;
    margin-top: 30px;
}

.error-message {
    text-align: center;
    font-size: 18px;
    color: red;
    font-weight: bold;
    margin-top: 20px;
}

footer {
    text-align: center;
    padding: 20px;
    background-color: #003366; /* Deep navy blue */
    color: white;
    position: fixed;
    bottom: 0;
    width: 100%;
    animation: slideIn 2s ease-in-out;
}

.about-us {
    text-align: center;
    margin-top: 50px;
    font-size: 24px;
    animation: fadeIn 3s ease-in-out;
}

.about-us h2 {
    color: #003366; /* Deep navy blue */
}

.about-us p {
    font-size: 18px;
    color: #333;
    line-height: 1.6;
    margin-top: 10px;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

@keyframes slideIn {
    0% {
        transform: translateY(-100%);
    }
    100% {
        transform: translateY(0);
    }
}


    </style>
</head>
<body>

<header>
    <img src="../images/logo.png" alt="Farm To Fork Logo">
    <div class="header-text">Farm To Fork</div>
    <div class="sub-title">Safe Food Traceability System</div>
    <a href="login.php" class="login-btn">Login</a>
</header>

<div class="search-container">
    <form method="POST" action="">
        <input type="text" name="batch_id" placeholder="Enter Batch ID" required>
        <input type="submit" name="search_batch" value="Search Batch">
    </form>
</div>

<?php if (!empty($batchData)) : ?>
    <div class="message"><?= $message ?></div>
    <table class="batch-details">
        <tr>
            <th>Product Name</th>
            <th>Batch ID</th>
            <th>Status</th>
            <th>Certification</th>
            <th>Harvest Area</th>
            <th>Harvest Date</th>
            <th>Nutrition Value</th>
        </tr>
        <tr>
            <td><?= $batchData['crop_name'] ?></td>
            <td><?= $batchData['batch_id'] ?></td>
            <td><?= $batchData['status'] ?></td>
            <td><?= $batchData['certifications'] ?></td>
            <td><?= $batchData['harvest_district'] ?> acres</td>
            <td><?= $batchData['harvest_date'] ?></td>
            <td><?= $batchData['nutrition_value'] ?> g</td>
        </tr>
    </table>
<?php elseif (isset($_POST['search_batch'])) : ?>
    <div class="error-message"><?= $errorMessage ?></div>
<?php endif; ?>

<div class="about-us">
    <h2>About Us</h2>
    <p>
        "Farm To Fork" is dedicated to ensuring the safety and traceability of your food. Through our system, we track each batch from farm to fork, ensuring transparency, quality, and safety for consumers. With every step of the food supply chain traceable, we aim to empower consumers with knowledge and peace of mind.
    </p>
</div>

<footer>
    <p>Farm To Fork &copy; 2024 | Safe Food Traceability System</p>
</footer>

</body>
</html>

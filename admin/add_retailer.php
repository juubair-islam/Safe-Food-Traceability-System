<?php



// Database configuration
$host = "localhost"; // Change to your host if it's different (e.g., an IP address or domain)
$username = "root"; // Your database username (e.g., "root")
$password = ""; // Your database password (e.g., "root" or your actual password)
$database = "safe_food_traceability"; // Your database name

// Create a connection to MySQL database
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


// Fetch the highest existing Retailer ID to generate the next one
$result = $mysqli->query("SELECT MAX(CAST(SUBSTRING(retailer_id, 3) AS UNSIGNED)) AS max_id FROM retailers");
$row = $result->fetch_assoc();
$next_id_number = $row['max_id'] + 1;  // Increment the last ID

// Format the new Retailer ID to always have 3 digits (e.g., R_001, R_002)
$next_retailer_id = 'R_' . str_pad($next_id_number, 3, '0', STR_PAD_LEFT);

// List of Bangladesh districts
$districts = [
    'Dhaka', 'Chittagong', 'Khulna', 'Rajshahi', 'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh',
    'Comilla', 'Narayanganj', 'Jashore', 'Dinajpur', 'Bogura', 'Tangail', 'Narsingdi', 'Kushtia',
    'Faridpur', 'Moulvibazar', 'Manikganj', 'Pabna', 'Cox\'s Bazar', 'Chapainawabganj', 'Chandpur'
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $retailer_name = $_POST['retailer_name'];
    $retailer_contact_number = $_POST['retailer_contact_number'];
    $retailer_email = $_POST['retailer_email'];
    $shop_location = $_POST['shop_location'];
    $district = $_POST['district'];

    // Prepare the insert query
    $query = "INSERT INTO retailers (retailer_id, retailer_name, retailer_contact_number, retailer_email, shop_location, district) 
              VALUES (?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $mysqli->prepare($query)) {
        // Bind parameters
        $stmt->bind_param("ssssss", $next_retailer_id, $retailer_name, $retailer_contact_number, $retailer_email, $shop_location, $district);

        // Execute the query
        if ($stmt->execute()) {
            echo "Retailer added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
}

// Close the database connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Retailer</title>
    <link rel="stylesheet" href="styles.css"> <!-- Your CSS file -->
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .container h1 {
            text-align: center;
            color: #4CAF50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }
        .form-group input[type="reset"] {
            background-color: #f44336;
            color: white;
        }
        .form-group input[type="reset"]:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Add Retailer</h1>
    <form action="add_retailer.php" method="POST">
        <div class="form-group">
            <label for="retailer_id">Retailer ID</label>
            <input type="text" id="retailer_id" name="retailer_id" value="<?php echo $next_retailer_id; ?>" readonly>
        </div>
        
        <div class="form-group">
            <label for="retailer_name">Retailer Name</label>
            <input type="text" id="retailer_name" name="retailer_name" required>
        </div>

        <div class="form-group">
            <label for="retailer_contact_number">Phone Number</label>
            <input type="text" id="retailer_contact_number" name="retailer_contact_number" required>
        </div>

        <div class="form-group">
            <label for="retailer_email">Email</label>
            <input type="email" id="retailer_email" name="retailer_email" required>
        </div>

        <div class="form-group">
            <label for="district">District</label>
            <select id="district" name="district" required>
                <option value="">Select District</option>
                <?php foreach ($districts as $district_name): ?>
                    <option value="<?php echo $district_name; ?>"><?php echo $district_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="shop_location">Shop Location</label>
            <input type="text" id="shop_location" name="shop_location" required>
        </div>

        <div class="form-group">
            <input type="submit" value="Add Retailer">
            <input type="reset" value="Reset">
        </div>
    </form>
</div>

</body>
</html>

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "safe_food_traceability"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Function to fetch district names from the farmers table
function getDistricts($conn) {
    $sql = "SELECT DISTINCT district FROM farmers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $districts = [];
        while ($row = $result->fetch_assoc()) {
            $districts[] = $row['district'];
        }
        return $districts;
    } else {
        return [];
    }
}

// Function to fetch crop details
function getCropDetails($conn, $crop_id) {
    $sql = "SELECT name, harvest_area, certifications FROM crops WHERE crop_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $crop_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Return the first row
    } else {
        return null; // Crop not found
    }
}

// Function to check if a farmer exists
function checkFarmerExistence($conn, $farmer_id) {
    $sql = "SELECT * FROM farmers WHERE farmer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

// Fetch districts for dropdown
$districts = getDistricts($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_crop'])) {
    $farmer_id = $_POST['farmer_id'];
    $crop_id = $_POST['crop_id'];

    // Check if farmer exists
    if (!checkFarmerExistence($conn, $farmer_id)) {
        echo "<script>alert('The Farmer info is Not In The Database');</script>";
        exit;
    }

    // Fetch crop details
    $cropDetails = getCropDetails($conn, $crop_id);
    if ($cropDetails) {
        // Display crop details
        echo "Crop Name: " . $cropDetails['name'] . "<br>";
        echo "Harvest Area: " . $cropDetails['harvest_area'] . "<br>";
        echo "Certifications: " . $cropDetails['certifications'] . "<br>";

        // Add code here to insert crop and batch info into the database
    } else {
        echo "Crop not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Crop</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
        
        /* Form Styling */
        form {
            padding: 20px;
            background-color: #fff;
            margin: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            padding: 10px 15px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        form button:hover {
            background-color: #3498db;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Add Crop</h1>
    </div>

    <form method="POST">
        <label for="district">District</label>
        <select name="district" id="district">
            <?php foreach ($districts as $district): ?>
                <option value="<?= htmlspecialchars($district) ?>"><?= htmlspecialchars($district) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="farmer_id">Farmer ID</label>
        <input type="number" name="farmer_id" id="farmer_id" required>

        <label for="crop_id">Crop</label>
        <select name="crop_id" id="crop_id" required>
            <?php
            // Fetch crops from the database
            $cropsResult = $conn->query("SELECT crop_id, name FROM crops");
            while ($row = $cropsResult->fetch_assoc()) {
                echo "<option value='{$row['crop_id']}'>" . htmlspecialchars($row['name']) . "</option>";
            }
            ?>
        </select>

        <button type="submit" name="add_crop">Add Crop</button>
    </form>

</body>
</html>

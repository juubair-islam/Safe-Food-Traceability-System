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

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // Plain password before hashing
    $region = $_POST['region'];
    $contact_number = $_POST['contact_number']; // Get the contact number from the form

    // Check if the email already exists in the database
    $email_check_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($email_check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email exists, show a warning message
        $error_message = "Error: The email address is already in use.";
    } else {
        // Email is unique, insert the new user (with plaintext password)
        $stmt = $conn->prepare("INSERT INTO users (role, name, email, password, region, registration_status, contact_number)
                                VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("ssssss", $role, $name, $email, $password, $region, $contact_number); // Bind plaintext password

        if ($stmt->execute()) {
            $last_user_id = $stmt->insert_id; // Get the auto-generated User ID
            $success_message = "New user added successfully. User ID: " . $last_user_id;
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}

// Handle approve action
if (isset($_GET['approve'])) {
    $user_id = $_GET['approve'];
    $sql = "UPDATE users SET registration_status = 'approved' WHERE user_id = $user_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "User approved successfully.";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE user_id = $user_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "User deleted successfully.";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Search functionality
$search = '';
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search = $_POST['search'];
    $sql = "SELECT * FROM users WHERE 
            (user_id LIKE '%$search%' OR name LIKE '%$search%' OR email LIKE '%$search%' OR region LIKE '%$search%' OR role LIKE '%$search%' OR contact_number LIKE '%$search%')
            AND registration_status = 'pending'";
} else {
    $sql = "SELECT * FROM users WHERE registration_status = 'pending'";
}

$result = $conn->query($sql);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>


/* Success Message Styling */
.success-message {
    background-color: #4CAF50;
    color: white;
    padding: 10px;
    margin: 20px 0;
    border-radius: 5px;
    font-weight: bold;
}

/* Error Message Styling */
.error-message {
    background-color: #f44336;
    color: white;
    padding: 10px;
    margin: 20px 0;
    border-radius: 5px;
    font-weight: bold;
}

/* Warning Message Styling */
.warning-message {
    background-color: #ff9800;
    color: white;
    padding: 10px;
    margin: 20px 0;
    border-radius: 5px;
    font-weight: bold;
}

/* Button Styling */
button, input[type="submit"] {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
    border: none; /* No border */
    padding: 10px 20px; /* Padding for the buttons */
    text-align: center;
    text-decoration: none; /* Remove underline from text */
    display: inline-block; /* Allow buttons to be inline */
    font-size: 16px; /* Text size */
    cursor: pointer; /* Change cursor to pointer on hover */
    border-radius: 5px; /* Rounded corners */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth transition */
}

/* Hover Effect for Buttons */
button:hover, input[type="submit"]:hover {
    background-color: #45a049; /* Darker green when hovered */
    transform: scale(1.05); /* Slightly enlarge the button */
}

/* Focus Effect for Buttons */
button:focus, input[type="submit"]:focus {
    outline: none; /* Remove the outline when focused */
    box-shadow: 0 0 8px rgba(0, 128, 0, 0.6); /* Add a green glow effect */
}

/* Search Button Styling */
input[type="submit"] {
    background-color: #2196F3; /* Blue background */
    padding: 8px 16px;
}

input[type="submit"]:hover {
    background-color: #0b7dda; /* Darker blue when hovered */
}

/* Add some space between buttons */
form input[type="submit"], form button {
    margin-top: 15px;
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
      <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/admin_dashboard.php">Dashboard</a></li>
        <li><a href="http://localhost:3000/Safe-Food-Traceability-System/admin/view_users.php"class="active">Manage Users</a></li>
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



<div class="container">
    <h2>Manage Users</h2>
    <p>Add a new user to the system or manage existing users.</p>

  <!-- Display Messages -->
  <?php if (isset($success_message)) { echo "<div class='success-message'>$success_message</div>"; } ?>
  <?php if (isset($error_message)) { echo "<div class='error-message'>$error_message</div>"; } ?>
  <?php if (isset($warning_message)) { echo "<div class='warning-message'>$warning_message</div>"; } ?>

    <!-- Add User Form -->
    <form method="POST" action="">
    <label for="role">Role:</label>
    <select name="role" required>
        <option value="nutritionist">Nutritionist</option>
        <option value="processing_unit_manager">Processing Unit Manager</option>
        <option value="government_officer">Government Officer</option>
        <option value="farmer">Farmer</option>
        <option value="retailer">Retailer</option>
    </select>

    <label for="name">Name:</label>
    <input type="text" name="name" required>

    <label for="email">Email:</label>
    <input type="email" name="email" required>

    <label for="password">Password:</label>
    <input type="password" name="password" required>

    <label for="region">Region:</label>
    <select name="region" required>
        <option value="Dhaka">Dhaka</option>
        <option value="Chittagong">Chittagong</option>
        <option value="Khulna">Khulna</option>
        <option value="Rajshahi">Rajshahi</option>
        <option value="Sylhet">Sylhet</option>
        <option value="Barisal">Barisal</option>
        <option value="Rangpur">Rangpur</option>
        <option value="Mymensingh">Mymensingh</option>
    </select>

    <label for="contact_number">Contact Number:</label>
    <input type="text" name="contact_number" required> <!-- New contact number field -->

    <input type="submit" name="add_user" value="Add User">
</form>


    <!-- Search Box -->
    <form method="POST" action="">
        <input type="text" name="search" placeholder="Search by ID, Name, Role, etc." value="<?php echo htmlspecialchars($search); ?>" />
        <input type="submit" value="Search">
    </form>

    <!-- Pending Users Table -->
    <h3>Pending Users</h3>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Role</th>
                <th>Name</th>
                <th>Email</th>
                <th>Region</th>
                <th>Registration Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) {
                while ($user = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['region']; ?></td>
                        <td><?php echo $user['registration_status']; ?></td>
                        <td>
                            <a href="?approve=<?php echo $user['user_id']; ?>" class="action-btn edit">Approve</a>
                            <a href="?delete=<?php echo $user['user_id']; ?>" class="action-btn delete">Delete</a>
                        </td>
                    </tr>
                <?php }
            } else {
                echo "<tr><td colspan='7'>No pending users found.</td></tr>";
            } ?>
        </tbody>
    </table>
</div>
</body>
</html>



<style>

/* styles.css */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #333;
}

form {
    margin: 20px 0;
}

form label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: bold;
}

form input,
form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

form button {
    background: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background: #0056b3;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th,
table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

table th {
    background: #007bff;
    color: white;
}

table tr:nth-child(even) {
    background: #f9f9f9;
}

.action-btn {
    display: inline-block;
    padding: 5px 10px;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
}

.action-btn.edit {
    background: #28a745;
}

.action-btn.delete {
    background: #dc3545;
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
  

</style>
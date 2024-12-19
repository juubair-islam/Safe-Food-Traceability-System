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

// Handle Delete or Restrict actions
if (isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == 'delete') {
        // Delete user
        $delete_sql = "DELETE FROM users WHERE user_id = '$user_id'";
        $conn->query($delete_sql);
    } elseif ($action == 'restrict') {
        // Restrict user (example: update role to 'restricted')
        $restrict_sql = "UPDATE users SET role = 'restricted' WHERE user_id = '$user_id'";
        $conn->query($restrict_sql);
    }
}

// Search functionality
$search = '';
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search = $_POST['search'];
    $sql = "SELECT * FROM users WHERE 
            (user_id LIKE '%$search%' OR name LIKE '%$search%' OR email LIKE '%$search%' OR region LIKE '%$search%' OR role LIKE '%$search%')";
} else {
    $sql = "SELECT * FROM users";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Users - Safe Food Traceability System</title>
  <link rel="stylesheet" href="../styles/style.css"> <!-- Link to your external CSS -->
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <style>
    /* Adding some basic styles for notification messages */
    .notification {
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      color: white;
      font-weight: bold;
    }
    .success-message {
      background-color: green;
    }
    .error-message {
      background-color: red;
    }

    /* Styling for buttons */
    button {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    .delete-button {
      background-color: red;
      transition: background-color 0.3s;
    }

    .delete-button:hover {
      background-color: darkred;
    }

    .restrict-button {
      background-color: orange;
      transition: background-color 0.3s;
    }

    .restrict-button:hover {
      background-color: darkorange;
    }

    /* Styling for search input and button */
    input[type="text"] {
      padding: 8px;
      font-size: 14px;
      width: 300px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    input[type="submit"] {
      padding: 8px 16px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #45a049;
    }

    /* DataTable customizations */
    #usersTable_length,
    #usersTable_filter {
      display: none;
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
        <li><a href="add_user.php">Manage Users</a></li>
        <li><a href="manage_batches.html">Manage Batches</a></li>
        <li><a href="manage_waste.php">Waste Management</a></li>
        <li><a href="generate_reports.php">Generate Reports</a></li>
        <li><a href="farmer_management.php">Farmers</a></li>
        <li><a href="barcode_management.php">Barcodes</a></li>
        <li><a href="transport_management.php">Transport</a></li>
        <li><a href="../index.php" class="logout-button">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>View Users</h2>
    <p>View and search users in the system.</p>

    <!-- Search Box -->
    <form method="POST" action="">
        <input type="text" name="search" placeholder="Search by ID, Name, Role, etc." value="<?php echo htmlspecialchars($search); ?>" />
        <input type="submit" value="Search">
    </form>

    <!-- Users Table -->
    <h3>Users</h3>
    <table id="usersTable">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Role</th>
                <th>Name</th>
                <th>Email</th>
                <th>Region</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['user_id'] . "</td>";
                    echo "<td>" . $row['role'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . $row['region'] . "</td>";
                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "<td>
                            <form method='POST' action='' style='display:inline;'>
                                <input type='hidden' name='user_id' value='" . $row['user_id'] . "'>
                                <button type='submit' name='action' value='delete' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</button>
                            </form>
                            <form method='POST' action='' style='display:inline;'>
                                <input type='hidden' name='user_id' value='" . $row['user_id'] . "'>
                                <button type='submit' name='action' value='restrict' class='restrict-button' onclick='return confirm(\"Are you sure you want to restrict this user?\")'>Restrict</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No users found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Footer Section -->
<div class="footer">
    &copy; 2024 Farm to Fork: Safe Food Traceability System. All rights reserved.
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "bPaginate": true,
            "bFilter": false, // Disable the search bar
            "bLengthChange": false, // Disable the entries dropdown
        });
    });
</script>

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

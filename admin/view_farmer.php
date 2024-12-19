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

// Fetch farmer data
$sql = "SELECT * FROM farmers";
$result = $conn->query($sql);

// Delete farmer action
if (isset($_GET['delete_farmer_id'])) {
    $farmer_id_to_delete = $_GET['delete_farmer_id'];
    $delete_sql = "DELETE FROM farmers WHERE farmer_id = $farmer_id_to_delete";
    if ($conn->query($delete_sql) === TRUE) {
        $success_message = "Farmer deleted successfully!";
    } else {
        $error_message = "Error deleting farmer: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Farmers - Safe Food Traceability System</title>
    <link rel="stylesheet" href="../styles/style.css"> <!-- Link to your external CSS -->
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css">
    <style>
        /* Add custom styles for the table and search box */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #4CAF50;
            padding: 10px;
            color: white;
            text-align: center;
        }

        .top-nav ul {
            background-color: #333;
            list-style-type: none;
            padding: 0;
        }

        .top-nav ul li {
            display: inline;
            margin-right: 10px;
        }

        .top-nav ul li a {
            color: white;
            text-decoration: none;
            padding: 8px;
            border-radius: 4px;
        }

        .top-nav ul li a:hover {
            background-color: #575757;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
        }

        .message {
            font-weight: bold;
            padding: 10px;
            margin-bottom: 10px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }

        .search-box {
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-end;
        }

        .search-box input {
            padding: 8px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 250px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .action-btns button {
            margin: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .view-btn {
            background-color: #4CAF50;
            color: white;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            position: fixed;
            width: 100%;
            bottom: 0;
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
    <h2>Farmers List</h2>
    
    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="message success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="message error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Search Box -->
    <div class="search-box">
        <input type="text" id="search" placeholder="Search farmers...">
    </div>

    <!-- Farmers Data Table -->
    <table id="farmers_table">
        <thead>
            <tr>
                <th>Farmer ID</th>
                <th>Name</th>
                <th>District</th>
                <th>Registration Date</th>
                <th>Contact Number</th>
                <th>NID Number</th>
                <th>Father's Name</th>
                <th>Mother's Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($farmer = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $farmer['farmer_id']; ?></td>
                    <td><?php echo $farmer['name']; ?></td>
                    <td><?php echo $farmer['district']; ?></td>
                    <td><?php echo $farmer['registration_date']; ?></td>
                    <td><?php echo $farmer['contact_number']; ?></td>
                    <td><?php echo $farmer['nid_number']; ?></td>
                    <td><?php echo $farmer['fathers_name']; ?></td>
                    <td><?php echo $farmer['mothers_name']; ?></td>
                    <td class="action-btns">
                        <a href="view_farmer_details.php?farmer_id=<?php echo $farmer['farmer_id']; ?>" class="view-btn">View</a>
                        <a href="?delete_farmer_id=<?php echo $farmer['farmer_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this farmer?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Footer Section -->
<div class="footer">
    &copy; 2024 Farm to Fork: Safe Food Traceability System. All rights reserved.
</div>

<!-- Include DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#farmers_table').DataTable();

        // Filter the table using the search box
        $('#search').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>

</body>
</html>

<?php $conn->close(); ?>



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

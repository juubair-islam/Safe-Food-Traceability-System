<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'safe_food_traceability';
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete, Restrict, and Unrestrict actions
if (isset($_POST['action'])) {
    $user_id = $_POST['user_id'];  // Get the user ID from the request
    $action = $_POST['action'];  // Get the action (delete, restrict, unrestrict)

    if ($action == 'delete') {
        // Delete user
        $delete_sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $user_id);  // Use 'i' for integer binding
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'restrict') {
        // Restrict user (set status to 'Inactive')
        $restrict_sql = "UPDATE users SET status = 'Inactive' WHERE user_id = ?";
        $stmt = $conn->prepare($restrict_sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'unrestrict') {
        // Unrestrict user (set status to 'Active')
        $unrestrict_sql = "UPDATE users SET status = 'Active' WHERE user_id = ?";
        $stmt = $conn->prepare($unrestrict_sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Search functionality (optional, if needed)
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
    <title>Manage Users</title>
    <script>
        function confirmAction(userId, action) {
            if (confirm("Are you sure you want to " + action + " this user?")) {
                var form = document.createElement("form");
                form.method = "POST";
                var input1 = document.createElement("input");
                input1.type = "hidden";
                input1.name = "user_id";
                input1.value = userId;
                var input2 = document.createElement("input");
                input2.type = "hidden";
                input2.name = "action";
                input2.value = action;
                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <style>
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

                /* Action Buttons */
            .delete-btn {
            background-color: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }



        .restrict-btn {
            background-color: #f39c12;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .restrict-btn:hover {
            background-color: #e67e22;
        }

        .unrestrict-btn {
            background-color: #27ae60;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .unrestrict-btn:hover {
            background-color: #2ecc71;
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


  /* Header Container */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    margin-top: 20px; /* Add some space from the top */
}



  /* Search Form */
.search-form {
    display: flex;
    align-items: center;
}

.search-bar {
    padding: 8px;
    width: 300px;
    margin-right: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.search-btn {
    padding: 8px 16px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.search-btn:hover {
    background-color: #2980b9;
}

/* Add User Button */
.add-user-btn {
    padding: 8px 16px;
    background-color: #27ae60;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    position: absolute;
    right: 20px;
}

.add-user-btn:hover {
    background-color: #2ecc71;
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
      </ul>
    </div>
  </div>


  <div class="header-container">
    <form method="POST" class="search-form">
        <input type="text" name="search" placeholder="Search users..." value="<?php echo $search; ?>" class="search-bar" />
        <button type="submit" class="search-btn">Search</button>
    </form>
   <!-- Add User button with link -->
   <a href="http://localhost:3000/Safe-Food-Traceability-System/admin/add_user.php" class="add-user-btn">Add User</a>
</div>


        <table class="crop-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <button class="delete-btn" onclick="confirmAction(<?php echo $row['user_id']; ?>, 'delete')">Delete</button>
                            <?php if ($row['status'] == 'Active'): ?>
                                <button class="restrict-btn"  onclick="confirmAction(<?php echo $row['user_id']; ?>, 'restrict')">Restrict</button>
                            <?php else: ?>
                                <button class="unrestrict-btn" onclick="confirmAction(<?php echo $row['user_id']; ?>, 'unrestrict')">Unrestrict</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <p>&copy; 2024 Safe Food Traceability System</p>
    </div>
</body>
</html>

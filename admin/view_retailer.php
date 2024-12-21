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
    $retailer_id = $_POST['retailer_id'];  // Get the retailer ID from the request
    $action = $_POST['action'];  // Get the action (delete, restrict, unrestrict)

    if ($action == 'delete') {
        // Delete retailer
        $delete_sql = "DELETE FROM retailers WHERE retailer_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $retailer_id);  // Use 'i' for integer binding
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'restrict') {
        // Restrict retailer (set status to 'Inactive')
        $restrict_sql = "UPDATE retailers SET status = 'Inactive' WHERE retailer_id = ?";
        $stmt = $conn->prepare($restrict_sql);
        $stmt->bind_param('i', $retailer_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == 'unrestrict') {
        // Unrestrict retailer (set status to 'Active')
        $unrestrict_sql = "UPDATE retailers SET status = 'Active' WHERE retailer_id = ?";
        $stmt = $conn->prepare($unrestrict_sql);
        $stmt->bind_param('i', $retailer_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Search functionality (optional, if needed)
$search = '';
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search = $_POST['search'];
    $sql = "SELECT retailer_id, retailer_name, retailer_contact_number, district, shop_location, status FROM retailers WHERE 
    (retailer_id LIKE '%$search%' OR retailer_name LIKE '%$search%' OR retailer_contact_number LIKE '%$search%' OR district LIKE '%$search%' OR shop_location LIKE '%$search%')";
} else {
    $sql = "SELECT retailer_id, retailer_name, retailer_contact_number, district, shop_location, status FROM retailers";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Retailers</title>
    <script>
        function confirmAction(retailerId, action) {
            if (confirm("Are you sure you want to " + action + " this retailer?")) {
                var form = document.createElement("form");
                form.method = "POST";
                var input1 = document.createElement("input");
                input1.type = "hidden";
                input1.name = "retailer_id";
                input1.value = retailerId;
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

        /* Table */
        .retailer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .retailer-table th, .retailer-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .retailer-table th {
            background-color: #2c3e50;
            color: white;
        }

        .retailer-table tr:hover {
            background-color: #f1f1f1;
        }

        .retailer-table td a {
            color: #3498db;
            text-decoration: none;
        }

        .retailer-table td a:hover {
            text-decoration: underline;
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

<!-- Main Content -->
<div class="container">
    <h2>Manage Retailers</h2>

    <form method="POST">
        <input type="text" name="search" placeholder="Search retailers..." value="<?php echo $search; ?>" class="search-bar" />
        <button type="submit">Search</button>
    </form>

    <table class="retailer-table">
        <thead>
            <tr>
                <th>Retailer ID</th>
                <th>Retailer Name</th>
                <th>Retailer Contact Name</th>
                <th>District</th>
                <th>Shop Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['retailer_id']; ?></td>
                    <td><?php echo $row['retailer_name']; ?></td>
                    <td><?php echo $row['retailer_contact_number']; ?></td>
                    <td><?php echo $row['district']; ?></td>
                    <td><?php echo $row['shop_location']; ?></td>
                    <td>
                        <button class="delete-btn" onclick="confirmAction(<?php echo $row['retailer_id']; ?>, 'delete')">Delete</button>
                        <?php
                            // Check if 'status' exists and is either 'Active' or 'Inactive'
                            if (isset($row['status']) && $row['status'] == 'Active') {
                                echo "<button class='restrict-btn' onclick='confirmAction(" . $row['retailer_id'] . ", \"restrict\")'>Restrict</button>";
                            } else {
                                echo "<button class='unrestrict-btn' onclick='confirmAction(" . $row['retailer_id'] . ", \"unrestrict\")'>Unrestrict</button>";
                            }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

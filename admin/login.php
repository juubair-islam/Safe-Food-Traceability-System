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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $input_password = $_POST['password'];

    // Check for admin credentials
    if ($role == 'admin' && $email == '2221134@iub.edu.bd' && $input_password == '12345') {
        // If the admin credentials match, redirect to the admin dashboard
        header("Location: http://localhost:3000/Safe-Food-Traceability-System/admin/admin_dashboard.php");
        exit();
    } else {
        // Query to check if the user exists in the 'users' table with the selected role
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a matching user was found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // For other roles, verify the password with hashed password
            if (password_verify($input_password, $user['password'])) {
                // Redirect to the appropriate page based on the role
                if ($role == 'nutritionist') {
                    header("Location: http://localhost:3000/Safe-Food-Traceability-System/admin/nutrition_crop.php");
                    exit();
                }
                // Add additional role redirection as needed
            } else {
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "No user found with this email for the selected role.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Safe Food Traceability System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h2 {
            color: #2c3e50;
        }

        /* Logo and Title Section */
        .logo-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-section img {
            width: 80px; /* Adjust logo size */
            margin-bottom: 10px;
            background: transparent;
        }

        .logo-section h1 {
            font-size: 28px;
            color: #2980b9;
        }

        .logo-section h3 {
            font-size: 22px;
            color: #16a085;
        }

        /* Login Form Styling */
        .login-container {
            background-color: White;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 100%;
            text-align: center;
        }

        .login-container input[type="email"],
        .login-container input[type="password"],
        .login-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .login-container select {
            cursor: pointer;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .login-container button:hover {
            background-color: #16a085;
        }

        /* Error message */
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Success message */
        .success-message {
            color: #2ecc71;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Logo and Title Section -->
    <div class="logo-section">
    <img src="../images/logo.png" alt="Logo" class="logo" />
        <h1>Safe Food Traceability System</h1>
        <h3>Farm To Fork</h3>
    </div>

    <!-- Login Form -->
    <div class="login-container">
        <h2>Login to Safe Food Traceability System</h2>

        <!-- Display Error or Success Messages -->
        <div id="message-container">
            <?php if (isset($error_message)) { echo '<div class="error-message">' . $error_message . '</div>'; } ?>
        </div>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="nutritionist">Nutritionist</option>
                <option value="admin">Admin</option>
            </select>

            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit" name="login">Login</button>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; 2024 Farm to Fork: Safe Food Traceability System. All rights reserved.
    </div>

</body>
</html>

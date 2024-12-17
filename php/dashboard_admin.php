<?php
session_start();
include 'database.php';

// Ensure the user is logged in and is an Admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="view_users.php">View Users</a>
            <a href="view_batches.php">View Batches</a>
        </nav>
    </header>
    <main>
        <section>
            <h2>Batch Status Overview</h2>
            <canvas id="batchStatusChart"></canvas>
        </section>
        <section>
            <h2>User Registrations</h2>
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically loaded -->
                </tbody>
            </table>
        </section>
    </main>
    <script src="../js/admin_dashboard.js"></script>
</body>
</html>

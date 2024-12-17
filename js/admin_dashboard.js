document.addEventListener("DOMContentLoaded", function () {
    // Fetch data for widgets
    fetch("php/get_dashboard_data.php")
        .then(response => response.json())
        .then(data => {
            document.getElementById("total-users").textContent = data.total_users;
            document.getElementById("total-batches").textContent = data.total_batches;
            document.getElementById("quality-checks").textContent = data.quality_checks;
        });

    // Initialize DataTable for users
    $("#usersTable").DataTable({
        ajax: "php/get_users.php",
        columns: [
            { data: "user_id" },
            { data: "name" },
            { data: "email" },
            { data: "role" },
            { data: "region" },
            {
                data: "user_id",
                render: function (data) {
                    return `
                        <button class="edit">Edit</button>
                        <button class="delete">Delete</button>
                    `;
                }
            }
        ]
    });
    document.addEventListener("DOMContentLoaded", function () {
        // Fetch data for widgets
        fetch("php/get_dashboard_data.php")
            .then(response => response.json())
            .then(data => {
                document.getElementById("total-users").textContent = data.total_users;
                document.getElementById("total-batches").textContent = data.total_batches;
                document.getElementById("flagged-batches").textContent = data.flagged_batches;
            });
    
        // Load User Activity Logs Table
        $("#activityTable").DataTable({
            ajax: "php/get_user_activity.php",
            columns: [
                { data: "activity_id" },
                { data: "name" },
                { data: "action" },
                { data: "timestamp" }
            ]
        });
    
        // Load Batch Data and Charts
        fetch("php/batch_status_data.php")
            .then(response => response.json())
            .then(data => {
                new Chart(document.getElementById("batchStatusChart"), {
                    type: "bar",
                    data: {
                        labels: ["Storable", "Non-Storable", "Damaged"],
                        datasets: [{
                            label: "Batches",
                            data: [data.storable, data.non_storable, data.damaged],
                            backgroundColor: ["#3CB371", "#FFD700", "#FF4500"]
                        }]
                    }
                });
            });
    
        // Load Waste Data and Charts
        fetch("php/get_waste_data.php")
            .then(response => response.json())
            .then(data => {
                new Chart(document.getElementById("wasteManagementChart"), {
                    type: "pie",
                    data: {
                        labels: ["Compost", "Biogas"],
                        datasets: [{
                            data: [data.compost, data.biogas],
                            backgroundColor: ["#3CB371", "#FFD700"]
                        }]
                    }
                });
            });
    });
    
});


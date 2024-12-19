// User Activity Chart (Bar Chart)
const ctx1 = document.getElementById('userActivityChart').getContext('2d');
const userActivityChart = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Admin', 'PU Manager', 'Nutritionist', 'Farmer'],
        datasets: [{
            label: 'User Activity',
            data: [120, 150, 180, 90],
            backgroundColor: '#3498db',
            borderColor: '#2980b9',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Traceability Status Chart (Pie Chart)
const ctx2 = document.getElementById('traceabilityStatusChart').getContext('2d');
const traceabilityStatusChart = new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: ['Tracked', 'Untracked', 'Flagged'],
        datasets: [{
            label: 'Traceability Status',
            data: [70, 20, 10],
            backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
            borderColor: '#ecf0f1',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

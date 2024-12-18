/* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
}

/* Header */
header {
    background-color: #333;
    color: white;
    padding: 20px 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

header .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

header h1 {
    font-size: 1.8rem;
    margin-left: 20px;
}

header nav a {
    color: white;
    text-decoration: none;
    padding: 0 20px;
    font-size: 1rem;
}

header nav a.active {
    font-weight: bold;
}

header nav a:hover {
    background-color: #555;
    border-radius: 5px;
}

/* Main Content */
main {
    padding: 20px 0;
}

/* Widgets Section */
.widgets {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.widget {
    background-color: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    text-align: center;
    flex: 1;
    max-width: 280px;
}

.widget h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.widget p {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.widget canvas {
    margin-top: 20px;
    width: 100% !important;
    height: 200px !important;
}

/* Charts Section */
.charts {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.chart-container {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    flex: 1;
    min-width: 400px;
    max-width: 500px;
    margin: 0 auto;
}

.chart-container h3 {
    font-size: 1.5rem;
    margin-bottom: 20px;
}

/* Small chart (Temperature & Humidity) */
.small-chart {
    max-width: 400px;
    margin: 0 auto;
    height: 300px;
}

/* Tables Section */
.table-section {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
}

.table-section h3 {
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-align: center;
}

/* Footer */
.sticky-footer {
    background-color: #333;
    color: white;
    padding: 15px 0;
    text-align: center;
}

.sticky-footer p {
    font-size: 1rem;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .widgets {
        flex-direction: column;
        align-items: center;
    }

    .chart-container {
        min-width: 300px;
        margin-bottom: 30px;
    }

    .small-chart {
        height: 250px;
    }

    .widget {
        max-width: 100%;
        margin-bottom: 20px;
    }
}

@media (max-width: 480px) {
    header h1 {
        font-size: 1.5rem;
    }

    header nav a {
        font-size: 0.9rem;
    }

    .chart-container {
        min-width: 250px;
        max-width: 400px;
    }

    .table-section {
        padding: 15px;
    }
}

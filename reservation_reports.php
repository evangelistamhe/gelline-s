<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is included

// Initialize variables for reports
$reservation_summary = [];
$table_usage = [];
$cancellation_report = [];
$revenue_report = [];

// Fetch Reservation Summary Data
$summary_query = "
    SELECT 
        COUNT(reservation_id) AS total_reservations,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_reservations,
        SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) AS canceled_reservations,
        SUM(guest_count) AS total_guest_count
    FROM reservations
";
$summary_result = $pdo->query($summary_query)->fetch(PDO::FETCH_ASSOC);

// Fetch Table Usage Data
$table_usage_query = "
    SELECT 
        table_number,
        COUNT(reservation_id) AS reservation_count,
        AVG(guest_count) AS avg_guest_count,
        SUM(total_price) AS total_revenue
    FROM reservations
    WHERE status = 'completed'
    GROUP BY table_number
";
$table_usage = $pdo->query($table_usage_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch Reservation Cancellation Data
$cancellation_query = "
    SELECT 
        COUNT(reservation_id) AS total_canceled,
        reservation_date,
        COUNT(CASE WHEN reservation_date - CURRENT_DATE <= 1 THEN 1 END) AS last_minute_cancellations
    FROM reservations
    WHERE status = 'canceled'
";
$cancellation_report = $pdo->query($cancellation_query)->fetch(PDO::FETCH_ASSOC);

// Fetch Revenue Data
$revenue_query = "
    SELECT 
        SUM(total_price) AS total_revenue,
        AVG(total_price) AS avg_revenue,
        MIN(total_price) AS min_revenue,
        MAX(total_price) AS max_revenue
    FROM reservations
    WHERE status = 'completed'
";
$revenue_report = $pdo->query($revenue_query)->fetch(PDO::FETCH_ASSOC);
?>

<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Initialize all variables with default values to avoid undefined errors
$summary_result = [
    'total_reservations' => 0,
    'completed_reservations' => 0,
    'cancelled_reservations' => 0,
    'total_guest_count' => 0
];
$revenue_report = [
    'total_revenue' => 0,
    'avg_revenue' => 0,
    'min_revenue' => 0,
    'max_revenue' => 0
];
$cancellation_report = [
    'total_cancelled' => 0,
    'last_minute_cancellations' => 0
];
$table_usage = [];

// Fetch Reservation Summary Data
$summary_query = "
    SELECT 
        COUNT(reservation_id) AS total_reservations,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_reservations,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_reservations,
        SUM(guest_count) AS total_guest_count
    FROM reservations
    WHERE user_id = :user_id
";
$summary_stmt = $pdo->prepare($summary_query);
$summary_stmt->execute(['user_id' => $user_id]);
$summary_data = $summary_stmt->fetch(PDO::FETCH_ASSOC);
if ($summary_data) {
    $summary_result = $summary_data;
}

// Fetch Revenue Data
$revenue_query = "
    SELECT 
        SUM(total_price) AS total_revenue,
        AVG(total_price) AS avg_revenue,
        MIN(total_price) AS min_revenue,
        MAX(total_price) AS max_revenue
    FROM reservations
    WHERE user_id = :user_id AND status = 'completed'
";
$revenue_stmt = $pdo->prepare($revenue_query);
$revenue_stmt->execute(['user_id' => $user_id]);
$revenue_data = $revenue_stmt->fetch(PDO::FETCH_ASSOC);
if ($revenue_data) {
    $revenue_report = $revenue_data;
}

// Fetch Cancellation Report Data
$cancellation_query = "
    SELECT 
        COUNT(reservation_id) AS total_cancelled,
        COUNT(CASE WHEN DATE(reservation_date) = CURRENT_DATE THEN 1 END) AS last_minute_cancellations
    FROM reservations
    WHERE user_id = :user_id AND status = 'cancelled'
";
$cancellation_stmt = $pdo->prepare($cancellation_query);
$cancellation_stmt->execute(['user_id' => $user_id]);
$cancellation_data = $cancellation_stmt->fetch(PDO::FETCH_ASSOC);
if ($cancellation_data) {
    $cancellation_report = $cancellation_data;
}

// Fetch Table Usage Data
$table_usage_query = "
    SELECT 
        table_number,
        COUNT(reservation_id) AS reservation_count,
        AVG(guest_count) AS avg_guest_count,
        SUM(total_price) AS total_revenue
    FROM reservations
    WHERE user_id = :user_id AND status = 'completed'
    GROUP BY table_number
";
$table_usage_stmt = $pdo->prepare($table_usage_query);
$table_usage_stmt->execute(['user_id' => $user_id]);
$table_usage_data = $table_usage_stmt->fetchAll(PDO::FETCH_ASSOC);
if ($table_usage_data) {
    $table_usage = $table_usage_data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Reports</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        body {
    font-family: 'Rubik', sans-serif;
    margin: 0;
    padding: 0;
    background: none;
}
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url(img/gellinesbg.png) repeat;
    background-size: 120px 120px;
    filter: grayscale(100%);
    opacity: 0.03;
    z-index: -1;
}
.home-container {
    background-color: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    width: 100%;
    height: 120px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    position: fixed;
    top: -5px;
    right: 0px;
    padding: 0 30px;
    z-index: 2;
}
.home-btn, .ordr-btn, .adre-btn, .lout-btn {
    font-family: 'Rubik', sans-serif;
    background-color: transparent;
    border: none;
    color: black;
    padding: 5px 20px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    letter-spacing: 2px;
}
.home-btn:hover, .ordr-btn:hover, .adre-btn:hover, .lout-btn:hover {
    background-color: rgba(0, 0, 0, 0.1);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.4);
}
.logo img {
    height: 120px;
    width: 130px;
    position: absolute;
    left: 0;
    top: 0px;
    z-index: 3;
    margin-left: 130px;
}
.dropdown {
    position: relative;
    display: inline-block;
}
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #ffffff;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}
.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}
.dropdown-content a:hover {
    background-color: rgba(0, 0, 0, 0.1);
}
.dropdown:hover .dropdown-content {
    display: block;
}
.dashboard-container {
    margin: 120px auto;
}
h1 {
    background-color: #ffdb44;
    color: black;
    padding: 20px;
    text-align: center;
    margin: 0;
    margin-bottom: 15px;
}
h2 {
    background-color: #ffdb44;
    color: black;
    padding: 20px;
    text-align: center;
    margin: 0;

}
h3 {
    background-color: rgba(249, 220, 46, 0.4);
    color: black;
    padding: 15px;
    text-align: center;
    margin: 0;
    
}
h4 {
    background-color: rgba(249, 220, 46, 0.1);
    color: black;
    padding: 15px;
    text-align: center;
    margin: 0;
}
.container {
    margin-top: 80px;
}
.container-wrapper {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.cancel-rep {
    width: 45%;
    display: inline-table;
    padding: 20px;
    max-width: 500px;
    max-height: 100px;
    margin-top: 20px;
    margin-left: 35px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}
.container-reports h4{
    text-align: center;
}


.trends-chart {

    width: 55%;
    display: inline-table;
    padding: 20px;
    max-width: 1000px;
    max-height: 1000px;
    margin-top: 20px;
    margin-left: 1.5%;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}


.use-report{
    margin-left: 2%;
    margin-right: 2%;
    margin-top: 30px;
    background-color: white;
    max-width: 1400px;
    width: 100%;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.use-report table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.use-report th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
.use-report th {
    background-color: rgba(249, 220, 46, 0.1);
    color: #333;
    font-weight: bold;
}
.use-report tr:nth-child(even) {
    background-color: rgba(249, 220, 46, 0.1);
}

.stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #ffdb44;
    color: #333;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.btn {
    background-color: #333;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.btn:hover {
    background-color: #555;
}
footer {
    background-color: black;
    color: white;
    padding: 15px;
    bottom: 0;
    width: 100%;
    box-sizing: border-box;
    font-size: 12px;
}
.container-reports, .rev-rep{
    display: inline-block;
    vertical-align: top;
    width: 20%;
    padding: 10px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    margin: 20px;
    text-align: center;
    height: auto;
}
.rev-chart {
    display: inline-block;
    vertical-align: top;
    width: 45%;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);

    margin: 20px;
    text-align: center;
}

.container-reports h4, .rev-rep h4, .rev-chart h4 {
    text-align: center;
    font-size: 15px;
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="ordr-btn" onclick="window.location.href = 'admin_dashboard.php';">Dashboard</button>
            
            <div class="dropdown">
                <button class="ordr-btn">Manage</button>
                <div class="dropdown-content">
                    <a href="admin_menu.php">Menu</a>
                    <a href="admin_tables.php">Tables</a>
                    <a href="admin_orders.php">Orders</a>
                    <a href="admin_reservations.php">Reservations</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="ordr-btn">Reports</button>
                <div class="dropdown-content">
                    <a href="admin_reports.php">Order</a>
                    <a href="reservation_reports.php">Reservation</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="ordr-btn">History</button>
                <div class="dropdown-content">
                    <a href="admin_order_historyy.php">Orders</a>
                    <a href="admin_reservation_historyy.php">Reservations</a>
                </div>
            </div>
            <button class="ordr-btn" onclick="window.location.href = 'audit_logs.php';">Audit Trail</button>
            <button class="ordr-btn" onclick="window.location.href = 'admin_login.php';">Log out</button>
        </div>

           
        </div>
    </header>

    <div class="container">
            <h1>Reservation Reports</h1>
            
            <div class="container-reports">
                <h2>Reservation Reports</h2>  
            <!-- Reservation Summary Report -->
            <div class="report-section">
                <h3>Total</h3>
                <h4><?php echo $summary_result['total_reservations']; ?></h4>
            </div>
            <div class="report-section">
                <h3>Completed</h3>
                <h4><?php echo $summary_result['completed_reservations']; ?></h4>
            </div>
            <div class="report-section">
                <h3>Cancelled</h3>
                <h4><?php echo $summary_result['cancelled_reservations']; ?></h4>
            </div>
            <div class="report-section">
                <h3>Total Guest Count</h3>
                <h4><?php echo $summary_result['total_guest_count']; ?></h4>
            </div>
        </div>

                <div class="rev-rep">
                    <h2>Revenue Reports</h2>
                    <!-- Revenue Report -->     
                    <div class="report-section">
                        <h3>Minimum</h3>
                        <h4>₱<?php echo number_format($revenue_report['min_revenue'], 2); ?></h4>
                    </div>
                    <div class="report-section">
                        <h3>Average</h3>
                        <h4>₱<?php echo number_format($revenue_report['avg_revenue'], 2); ?></h4>
                    </div>
                    <div class="report-section">
                        <h3>Maximum</h3>
                        <h4>₱<?php echo number_format($revenue_report['max_revenue'], 2); ?></h4>
                    </div>
                    <div class="report-section">
                        <h3>Total</h3>
                        <h4>₱<?php echo number_format($revenue_report['total_revenue'], 2); ?></h4>
                    </div>
                </div>

                <div class="rev-chart">
                    <!-- Revenue Trends Chart -->
                    <h2>Revenue Chart</h2>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

        <div class="trends-chart">
            <!-- Reservation Trends Chart -->
            <h2>Reservation Trends</h2>
            <div class="chart-container">
                <canvas id="reservationChart"></canvas>
            </div>
        </div>

        <div class="cancel-rep">
            <h2>Cancelled Reports</h2>
        <!-- Reservation Cancellation Report -->
            <div class="report-section">
                <h3>Total</h3>
                <h4><?php echo $cancellation_report['total_cancelled']; ?></h4>
            </div>
            <div class="report-section">
                <h3>Last Minute Cancellations (within 24 hours)</h3>
                <h4><?php echo $cancellation_report['last_minute_cancellations']; ?></h4>
            </div>
        </div>



        
    </div>
<div class="use-report">
    <!-- Table Usage Report -->
    <h2>Table Usage Report</h2>
    <table>
        <tr>
            <th>Table Number</th>
            <th>Reservation Count</th>
            <th>Average Guest Count</th>
            <th>Total Revenue</th>
        </tr>
        <?php foreach ($table_usage as $table): ?>
        <tr>
            <td><?php echo $table['table_number']; ?></td>
            <td><?php echo $table['reservation_count']; ?></td>
            <td><?php echo number_format($table['avg_guest_count'], 2); ?></td>
            <td>₱<?php echo number_format($table['total_revenue'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>

</div>
<br>
<script>
    // Reservation Trends Chart
var ctx = document.getElementById('reservationChart').getContext('2d');
var reservationChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Completed Reservations', 'Cancelled Reservations'],
        datasets: [{
            label: 'Reservation Count', 
            data: [<?php echo $summary_result['completed_reservations']; ?>, <?php echo $summary_result['cancelled_reservations']; ?>],
            backgroundColor: ['rgba(76, 187, 23, 0.4)', 'rgba(220, 53, 69, 0.4)'],
            borderColor: ['rgba(76, 187, 23, 1)', 'rgba(220, 53, 69, 1)'], // Include border color for cancelled
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#ddd'
                }
            },
            x: {
                grid: {
                    color: '#ddd'
                }
            }
        }
    }
});


    // Revenue Trends Chart
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Min Revenue', 'Avg Revenue', 'Max Revenue'],
            datasets: [{
                label: 'Revenue (₱)',
                data: [<?php echo $revenue_report['min_revenue']; ?>, <?php echo $revenue_report['avg_revenue']; ?>, <?php echo $revenue_report['max_revenue']; ?>],
                backgroundColor: 'rgba(0, 123, 255, 0.4)',
                borderColor: '#007bff',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    grid: {
                        color: '#ddd'
                    }
                },
                x: {
                    grid: {
                        color: '#ddd'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch sales data for the week (grouped by day)
$stmt = $pdo->prepare("
    SELECT DAYNAME(o.created_at) AS day, SUM(o.total_amount) AS total_sales 
    FROM orders o 
    WHERE WEEK(o.created_at) = WEEK(NOW()) 
    GROUP BY DAYNAME(o.created_at)
    ORDER BY FIELD(DAYNAME(o.created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$stmt->execute();
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total sales for the week
$total_sales_stmt = $pdo->prepare("
    SELECT SUM(total_amount) AS total_sales 
    FROM orders 
    WHERE WEEK(created_at) = WEEK(NOW())
");
$total_sales_stmt->execute();
$total_sales = $total_sales_stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];

$days = [];
$sales = [];
foreach ($sales_data as $data) {
    $days[] = $data['day'];
    $sales[] = $data['total_sales'];
}

// Fetch total number of orders
$total_orders_stmt = $pdo->prepare("
    SELECT COUNT(order_id) AS total_orders 
    FROM orders 
    WHERE WEEK(created_at) = WEEK(NOW())
");
$total_orders_stmt->execute();
$total_orders = $total_orders_stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Fetch cancelled orders count
$cancelled_orders_stmt = $pdo->prepare("
    SELECT COUNT(order_id) AS cancelled_orders 
    FROM orders 
    WHERE order_status = 'Cancelled' AND WEEK(created_at) = WEEK(NOW())
");
$cancelled_orders_stmt->execute();
$cancelled_orders = $cancelled_orders_stmt->fetch(PDO::FETCH_ASSOC)['cancelled_orders'];

// Fetch top 5 popular dishes
$popular_dishes_stmt = $pdo->prepare("
    SELECT m.dish_name, COUNT(oi.menu_id) AS total_orders 
    FROM order_items oi 
    JOIN menu m ON oi.menu_id = m.menu_id 
    JOIN orders o ON oi.order_id = o.order_id
    WHERE WEEK(o.created_at) = WEEK(NOW()) 
    GROUP BY oi.menu_id 
    ORDER BY total_orders DESC 
    LIMIT 5
");

$popular_dishes_stmt->execute();
$popular_dishes = $popular_dishes_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
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
.container-wrapper {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}
.container-weekly {
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
.container-weekly h4{
    text-align: center;
}
.container-daily {
    width: 55%;
    display: inline-table;
    padding: 20px;
    max-width: 1000px;
    max-height: 1000px;
    margin-top: 20px;
    margin-right: 35px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}


.top-section{
    margin-left: 2%;
    margin-right: 2%;
    margin-top: 30px;
    background-color: white;
    max-width: 1500px;
    width: 100%;
}
.top-section table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.top-section th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
.top-section th {
    background-color: rgba(249, 220, 46, 0.1);
    color: #333;
    font-weight: bold;
}
.top-section tr:nth-child(even) {
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
    </style>
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
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
    <div class="container-wrapper">
    <div class="container-weekly">
        <h2>Weekly Reports</h2>
        <div class="report-section">
            <h3>Total Sales</h3>
            <h4>₱ <?php echo number_format($total_sales, 2); ?></h4>
        </div>
        <div class="report-section">
            <h3>No. of Orders</h3>
            <h4><?php echo $total_orders; ?></h4>
        </div>
        <div class="report-section">
            <h3>Cancelled Orders</h3>
            <h4><?php echo $cancelled_orders; ?></h4>
        </div>
    </div>
    <div class="container-daily">
        <!-- Display the sales chart -->
        <div class="report-section">
            <h2>Daily Sales Report</h2>
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    <div class="top-section">
            <h2>Top 5 Popular Dishes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Dish Name</th>
                        <th>Total Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popular_dishes as $dish): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dish['dish_name']); ?></td>
                        <td><?php echo $dish['total_orders']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
        <script>
    // Ensure PHP data is passed as a JSON array
    const labels = <?php echo json_encode($days); ?>;
    const salesData = <?php echo json_encode($sales); ?>;

    // Configuration for Chart.js
    const data = {
        labels: labels,
        datasets: [{
            label: 'Total Sales (₱)',
            data: salesData,
            backgroundColor: 'rgba(76, 187, 23, 0.4)',
            borderColor: 'rgba(76, 187, 23, 1)',
            borderWidth: 1
        }]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    // Render the chart in the canvas
    var ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, config);
</script>
</body>
</html>

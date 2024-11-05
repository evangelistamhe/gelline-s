<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is established

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch all orders including user's first and last name
$recent_orders = $pdo->query("
    SELECT o.order_id, o.order_status, o.total_amount, o.order_date, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
")->fetchAll();

// Fetch total sales if available
$total_sales_result = $pdo->query("SELECT IFNULL(SUM(total_amount), 0) AS total_sales FROM orders")->fetch();
$total_sales = $total_sales_result ? $total_sales_result['total_sales'] : 0;
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

// Fetch cancelled orders count
$cancelled_orders_stmt = $pdo->prepare("
    SELECT COUNT(order_id) AS cancelled_orders 
    FROM orders 
    WHERE order_status = 'Cancelled' AND WEEK(created_at) = WEEK(NOW())
");
$cancelled_orders_stmt->execute();
$cancelled_orders = $cancelled_orders_stmt->fetch(PDO::FETCH_ASSOC)['cancelled_orders'];

// Fetch total number of orders
$total_orders_stmt = $pdo->prepare("
    SELECT COUNT(order_id) AS total_orders 
    FROM orders 
    WHERE WEEK(created_at) = WEEK(NOW())
");
$total_orders_stmt->execute();
$total_orders = $total_orders_stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Prepare data for Chart.js
$days = [];
$sales = [];
foreach ($sales_data as $data) {
    $days[] = $data['day'];
    $sales[] = $data['total_sales'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Sales Report</title>
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
            display: flex;
            justify-content: center; /* Centers horizontally */
            align-items: center;     /* Centers vertically (optional) */
      /* Full viewport height for vertical centering */
        }
        .container-wrapper {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .container-ord {
            margin: 0 auto;
            width: 100%;
            max-width: 1200px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            background-color: white;
            flex: 1;
            padding: 40px;
            display: inline-table;
            flex-direction: column;
            align-items: center;
        }

        h1 {

            background-color: #ffdb44;
            color: black;
            padding: 20px;
            text-align: center;
            margin-top: 120px;
        }
        .report-section {
            width: 100%;
            max-width: 1200px;
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .report-section h2 {
            font-size: 24px;
            margin-bottom: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #ffdb44;
            color: black;
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
<h1>Admin Reports</h1>
    <div class="dashboard-container">
        

        <div class="container-ord">
            <h2>Recent Orders</h2>
            <div class="stat">
                <h3>Total Sales: ₱ <?php echo number_format($total_sales, 2); ?></h3>
            </div>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th> <!-- Update this heading -->
                    <th>Order Status</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                </tr>
                <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td> <!-- Full name here -->
                        <td><?php echo $order['order_status']; ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>

    <script>
        // Labels and sales data from PHP
        const labels = <?php echo json_encode($days); ?>;
        const salesData = <?php echo json_encode($sales); ?>;

        // Chart.js configuration using your structure
        const data = {
            labels: labels,
            datasets: [{
                label: 'Total Sales (₱)',
                data: salesData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(255, 205, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 159, 64)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(54, 162, 235)',
                    'rgb(153, 102, 255)',
                    'rgb(201, 203, 207)'
                ],
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

        // Render the chart
        var ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, config);
    </script>
</body>
</html>

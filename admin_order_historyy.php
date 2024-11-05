<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch all delivered and cancelled orders
$orders = $pdo->query("
    SELECT o.order_id, o.order_status, o.total_amount, o.order_date, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_status IN ('delivered', 'cancelled')
    ORDER BY o.order_date DESC
")->fetchAll();

// Calculate total sales for delivered orders
$total_sales_result = $pdo->query("SELECT IFNULL(SUM(total_amount), 0) AS total_sales FROM orders WHERE order_status = 'delivered'")->fetch();
$total_sales = $total_sales_result ? $total_sales_result['total_sales'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
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
            background-color: white;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            padding: 20px;
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

        .download-btn {
            background-color: #ffdb44;
            color: black;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;

            cursor: pointer;
            margin: 20px auto;
            display: block;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .download-btn:hover {
            background-color: #e6c93b;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.4);
        }
        /* Dropdown Styling */
        .filter-dropdown {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            width: 100%;
            max-width: 500px;
        }
        .filter-select {
            padding: 10px;
            font-size: 16px;
            font-family: 'Rubik', sans-serif;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            background-color: white;
            cursor: pointer;
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

    <h1>Order History</h1>
    <div class="dashboard-container">
        <div class="container-wrapper">
                   <!-- Filter Dropdown -->
            <div class="filter-dropdown">
                <select class="filter-select" id="orderStatusFilter" onchange="filterOrders()">
                    <option value="all">All Orders</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <h3>Total Sales from Delivered Orders: ₱<?php echo number_format($total_sales, 2); ?></h3>



            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Order Status</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                </tr>
                <?php foreach ($orders as $order): ?>
                    <tr class="order-row" data-status="<?php echo strtolower($order['order_status']); ?>">
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td class="order-status <?php echo $order['order_status'] === 'delivered' ? 'delivered' : ''; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button class="download-btn" onclick="window.location.href = 'download_pdf_orders.php';">Download PDF</button>
        </div>
    </div>
    <br>
    <br>
    <script>
        function filterOrders() {
            const filterValue = document.getElementById("orderStatusFilter").value.toLowerCase();
            const rows = document.querySelectorAll(".order-row");

            rows.forEach(row => {
                const status = row.getAttribute("data-status");
                if (filterValue === "all" || status === filterValue) {
                    row.style.display = ""; // Show row
                } else {
                    row.style.display = "none"; // Hide row
                }
            });
        }
    </script>
</body>
</html>

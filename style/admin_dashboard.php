<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}


// Fetch data for recent orders including user's first and last name
$recent_orders = $pdo->query("
    SELECT o.order_id, o.order_status, o.total_amount, o.order_date, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC 
    LIMIT 5
")->fetchAll();

// Fetch total sales if available
$total_sales_result = $pdo->query("SELECT IFNULL(SUM(total_amount), 0) AS total_sales FROM orders")->fetch();
$total_sales = $total_sales_result ? $total_sales_result['total_sales'] : 0;


// Fetch data for recent reservations including user's first and last name
$recent_reservations = $pdo->query("
    SELECT r.reservation_id, r.status, r.date, r.time_in, r.time_out, t.table_number, u.first_name, u.last_name
    FROM reservations r 
    JOIN restaurant_tables t ON r.table_id = t.table_id
    JOIN users u ON r.user_id = u.user_id
    ORDER BY r.date DESC, r.time_in DESC 
    LIMIT 5
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="style/dashboard.css"> <!-- Link to external CSS -->
</head>
<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>

             <!-- Add Manage Reservations Link -->
            <div class="dropdown">
                <button class="ordr-btn">Manage Reservations</button>
                <div class="dropdown-content">
                    <a href="admin_reservations.php">Reservations</a>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="ordr-btn">Manage Orders</button>
                <div class="dropdown-content">
                    <a href="admin_orders.php">Orders</a>
                    <a href="admin_menu.php">Menu Settings</a>
                </div>
            </div>
            <button class="ordr-btn" onclick="window.location.href = 'admin_login.php';">Log out</button>
            <div class="dropdown">
    <button class="ordr-btn">Audit Trail</button>
    <div class="dropdown-content">
        <a href="audit_logs.php">Audit Logs</a> <!-- Link to the audit_logs.php -->
        <a href="update_logs.php">Update Logs</a> <!-- Link to update_logs.php (if available) -->
    </div>
</div>

           
        </div>
    </header>

    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <div class="container">
            <h2>Recent Orders</h2>
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


            <div class="stat">
                <h3>Total Sales:</h3>
                <p>₱<?php echo number_format($total_sales, 2); ?></p>
            </div>

            <!-- Recent Reservations Section -->
           <table>
    <tr>
        <th>Reservation ID</th>
        <th>Customer Name</th>
        <th>Table Number</th>
        <th>Status</th>
        <th>Date</th>
        <th>Time-In</th>
        <th>Time-Out</th>
    </tr>
    <?php foreach ($recent_reservations as $reservation): ?>
        <tr>
            <td><?php echo $reservation['reservation_id']; ?></td>
            <td><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></td> <!-- Full name here -->
            <td><?php echo $reservation['table_number']; ?></td>
            <td><?php echo $reservation['status']; ?></td>
            <td><?php echo $reservation['date']; ?></td>
            <td><?php echo $reservation['time_in']; ?></td>
            <td><?php echo $reservation['time_out']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

            </table>
        </div>
    </div>
</body>
</html>

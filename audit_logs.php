<?php
session_start();
require 'dbconnection.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch the audit logs from the database
$audit_logs = $pdo->query("
    SELECT at.audit_id, u.first_name, u.last_name, at.action, at.timestamp
    FROM audit_trail at
    JOIN users u ON at.user_id = u.user_id
    ORDER BY at.timestamp DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin</title>
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
.top-section{
    margin-top: 30px;
    background-color: white;
    max-width: 1460px;
    width: 100%;
}
.top-section table {
    margin-left: 2%;
    margin-right: 2%;
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
    background-color: rgba(249, 220, 46, 1);
    color: #333;
    font-weight: bold;
}
.top-section tr:nth-child(even) {
    background-color: rgba(249, 220, 46, 0.1);
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
        <h1>Audit Logs</h1>
        <div class="top-section">
            <table>
                <tr>
                    
                    <th>Username</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
                <?php foreach ($audit_logs as $log): ?>
                    <tr>
                        
                        <td><?php echo $log['first_name'] . ' ' . $log['last_name']; ?></td>
                        <td><?php echo ucfirst($log['action']); ?></td>
                        <td><?php echo $log['timestamp']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>

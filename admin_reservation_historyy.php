<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Query to get completed and cancelled reservations
$query = "
    SELECT 
        reservations.reservation_id, 
        users.first_name, 
        users.last_name, 
        reservations.table_number, 
        reservations.reservation_date, 
        reservations.time_in, 
        reservations.time_out, 
        reservations.guest_count, 
        reservations.created_at, 
        reservations.status, 
        reservations.total_price
    FROM 
        reservations
    JOIN 
        users 
    ON 
        reservations.user_id = users.user_id
    WHERE reservations.status IN ('completed', 'cancelled')
    ORDER BY reservations.reservation_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Reservations</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        /* General Styling */
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
            right: 0;
            padding: 0 30px;
            z-index: 2;
        }
        .home-btn, .ordr-btn, .download-btn {
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
        .ordr-btn:hover, .download-btn:hover {
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
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container-wrapper {
            display: flex;
            justify-content: center;
            flex-direction: column;
            width: 100%;
            max-width: 1200px;
            background-color: white;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        h1 {
            background-color: #ffdb44;
            color: black;
            padding: 20px;
            text-align: center;
            margin-top: 120px;
        }
        h3 {
            text-align: right;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #ffdb44;
            color: black;
        }
        .reservation-status {
            font-weight: bold;
            color: #ff6b6b; /* Red for Cancelled */
        }
        .reservation-status.completed {
            color: #28a745; /* Green for Completed */
        }
        .download-btn {
            background-color: #ffdb44;
            color: black;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 25px;
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

    <h1>Reservation History</h1>
    <div class="dashboard-container">
        <div class="container-wrapper">
            <!-- Filter Dropdown -->
            <div class="filter-dropdown">
                <select class="filter-select" id="reservationStatusFilter" onchange="filterReservations()">
                    <option value="all">All Reservations</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <button class="download-btn" onclick="window.location.href = 'reservation_pdf.php?status=' + document.getElementById('reservationStatusFilter').value;">Download PDF</button>

            <table>
                <tr>
                    <th>Reservation ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Table Number</th>
                    <th>Reservation Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Guest Count</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Total Price</th>
                </tr>
                <?php foreach ($reservations as $reservation): ?>
                    <tr class="reservation-row" data-status="<?php echo strtolower($reservation['status']); ?>">
                        <td><?php echo htmlspecialchars($reservation['reservation_id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['table_number']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['time_in']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['time_out']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['guest_count']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['created_at']); ?></td>
                        <td class="reservation-status <?php echo $reservation['status'] === 'completed' ? 'completed' : ''; ?>">
                            <?php echo ucfirst($reservation['status']); ?>
                        </td>
                        <td>₱<?php echo number_format($reservation['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <script>
        function filterReservations() {
            const filterValue = document.getElementById("reservationStatusFilter").value.toLowerCase();
            const rows = document.querySelectorAll(".reservation-row");

            rows.forEach(row => {
                const status = row.getAttribute("data-status");
                row.style.display = (filterValue === "all" || status === filterValue) ? "" : "none";
            });
        }
    </script>
</body>
</html>
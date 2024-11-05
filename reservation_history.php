<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all reservations for the logged-in user including first and last name
$reservations = $pdo->prepare("
    SELECT reservation_id, table_number, reservation_date, time_in, time_out, guest_count, status, total_price, users.first_name, users.last_name
    FROM reservations
    JOIN users ON reservations.user_id = users.user_id
    WHERE reservations.user_id = :user_id
    ORDER BY reservation_date DESC, time_in ASC
");
$reservations->execute(['user_id' => $user_id]);
$reservations = $reservations->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Reservation History</title>
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
        .image-button {
            display: inline-block;
            border: none;
            padding: 0;
            background: none;
            cursor: pointer;
            margin-left: 20px; /* Adjust this value to add space between icons */
        }

        .image-button img {
            width: 30px;
            height: auto;
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

        /* Dropdown CSS for Profile */
        .profile-dropdown {
            position: relative;
            display: inline-block;
            margin-right: 20px;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .profile-dropdown-content a {
            font-family: 'Rubik', sans-serif;
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .profile-dropdown-content a:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
         h1 {
            margin-top: 120px;
            background-color: #ffdb44;
            color: #333;
            padding: 20px;
            text-align: center;
            }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #ffdb44;
            color: #333;
        }
        .filter-dropdown {
            margin-left: 80%;
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
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
            <button class="home-btn" onclick="window.location.href = 'home.php';">Home</button>
            <button class="ordr-btn" onclick="window.location.href = 'about.php';">About Us</button>
          

            <div class="dropdown">
                <button class="ordr-btn">Menus</button>
                <div class="dropdown-content">
                    <a href="menu.php">Food Menu</a>
                </div>
            </div>
   
            <div class="dropdown profile-dropdown">
                <button class="image-button">
                    <img src="img/user.png" alt="Profile Icon">
                </button>
                <div class="dropdown-content profile-dropdown-content">
                    <a href="my_orders.php">Orders</a>
                    <a href="my_reservations.php">Reservations</a>
                    <a href="order_history.php">Order History</a>
                    <a href="reservation_history.php">Reservation History</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

           
            <a class="image-button" href="cart.php">
                <img src="img/cart.png" alt="Button">
            </a>
        </div>
    </header>  
      <h1>Your Reservation History</h1>
    <div class="dashboard-container">

    <div class="container">

        <!-- Filter Dropdown -->
        <div class="filter-dropdown">
            <select class="filter-select" id="statusFilter" onchange="filterReservations()">
                <option value="all">All Reservations</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Reservations Table -->
        <table>
            <tr>
                <th>Reservation ID</th>
                <th>Customer Name</th>
                <th>Table Number</th>
                <th>Reservation Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Guest Count</th>
                <th>Status</th>
                <th>Total Price</th>
            </tr>
            <?php if (count($reservations) > 0): ?>
                <?php foreach ($reservations as $reservation): ?>
                    <tr class="reservation-row" data-status="<?php echo strtolower($reservation['status']); ?>">
                        <td><?php echo htmlspecialchars($reservation['reservation_id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['table_number']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['time_in']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['time_out']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['guest_count']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($reservation['status'])); ?></td>
                        <td>₱<?php echo number_format($reservation['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No reservations found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

    <script>
        function filterReservations() {
            const filterValue = document.getElementById("statusFilter").value.toLowerCase();
            const rows = document.querySelectorAll(".reservation-row");

            rows.forEach(row => {
                const status = row.getAttribute("data-status");
                row.style.display = (filterValue === "all" || status === filterValue) ? "" : "none";
            });
        }
    </script>
</body>
</html>

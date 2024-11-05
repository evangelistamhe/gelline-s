<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is included

// Initialize variables to avoid undefined variable errors
$message = "";

// Check if the form has been submitted to manually update a table's status
if (isset($_POST['complete_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $table_number = $_POST['table_number'];

    try {
        // Update the reservation status to 'completed' and make the table available
        $pdo->beginTransaction(); // Start a transaction

        // Update the reservation status to 'completed'
        $update_reservation = "UPDATE reservations SET status = 'completed' WHERE reservation_id = ?";
        $stmt_reservation = $pdo->prepare($update_reservation);
        $stmt_reservation->execute([$reservation_id]);

        // Mark the table as available
        $update_table = "UPDATE restaurant_tables SET status = 'available' WHERE table_number = ?";
        $stmt_table = $pdo->prepare($update_table);
        $stmt_table->execute([$table_number]);

        $pdo->commit(); // Commit the transaction

        // Set the success message
        $message = "Reservation completed and Table $table_number is now available.";
    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback if any errors
        $message = "Failed to update the table status: " . $e->getMessage();
    }
}

// Query to get only active reservations (excluding those with status 'completed')
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
    WHERE reservations.status != 'completed'
";

try {
    $stmt = $pdo->prepare($query);  // Prepare the query
    $stmt->execute();  // Execute the query
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
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
.container {
    margin: 120px auto;
}
.container-pend, .container-comp {
    margin-left: 2%;
    background-color: white;
    text-align: center;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
    max-width: 96%;
}
h1 {
    background-color: #ffdb44;
    color: black;
    padding: 20px;
    text-align: center;
    margin: 0;
    margin-bottom: 15px;
    margin-top: 120px;
}
h2 {
    background-color: #ffdb44;
    color: black;
    padding: 20px;
    text-align: center;
    margin: 0;
    margin-bottom: 20px;
}
p {
    margin-bottom: 50px;
}
table {
    margin-left: 2%;
    
    width: 96%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background-color: rgba(249, 220, 46, 0.5);
    color: #333;
    font-weight: bold;
}
tr:nth-child(even) {
    background-color: rgba(249, 220, 46, 0.1);
}
li {
    list-style-type: none;
    margin: 0;
    padding: 0;
}

/* Style the select dropdown */
select {

    background-color: #ffffff;
    border: 1px solid #ddd;
    padding: 12px 14px;
    font-size: 16px;
    color: black;
    min-width: 100px;
    cursor: pointer;

    position: relative;
}

/* Add a custom arrow indicator */
select:after {
    content: '\25BC'; /* Downward arrow */
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}

/* Optional: Remove border-radius */
select {
    border-radius: 0;
}

/* Optional: Style for the dropdown options */
select option {
    padding: 10px;
    background-color: #ffffff;
    color: black;
    cursor: pointer;
}
button[name="update_status"] {
    background-color: #ffdb44; /* Set background color */
    color: black; /* Set text color */
    padding: 12px 20px; /* Add padding for size */
    border: none; /* Remove border */
    font-size: 15px; /* Set font size */
    font-weight: bold; /* Make the text bold */
    cursor: pointer; /* Change cursor to pointer */

    transition: background-color 0.3s ease; /* Smooth background color change */
}

button[name="update_status"]:hover {
    background-color: #ffcc00; /* Darken background color on hover */
}
.mark-completed-btn {
    background-color: #ffdb44; /* Set background color */
    color: black; /* Set text color */
    padding: 12px 20px; /* Add padding for size */
    border: none; /* Remove border */
    font-size: 15px; /* Set font size */
    font-weight: bold; /* Make the text bold */
    cursor: pointer; /* Change cursor to pointer */
    transition: background-color 0.3s ease; /* Smooth background color change */
}

.mark-completed-btn:hover {
    background-color: #ffcc00; /* Darken background color on hover */
}

</style>
    <script>
        // Function to display the message and hide it after 5 seconds
        function showMessage() {
            var messageDiv = document.getElementById("message");
            if (messageDiv) {
                messageDiv.style.display = "block"; // Show the message
                setTimeout(function() {
                    messageDiv.style.display = "none"; // Hide the message after 5 seconds
                }, 5000); // 5000 milliseconds = 5 seconds
            }
        }

        // Call the showMessage function when the page loads if a message is set
        window.onload = function() {
            <?php if (isset($_POST['complete_reservation'])): ?>
                showMessage(); // Call the function if the form was submitted
            <?php endif; ?>
        };
    </script>
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

    <h1>Manage Reservations</h1>

    <div class="container-comp">
    <h2>Pending Reservations</h2>
    <!-- Display message if available -->
    <?php if (isset($_POST['complete_reservation']) && $message !== ""): ?>
        <div id="message" class="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Table displaying the reservations -->
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
            <th>Update Status</th>
        </tr>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['reservation_id']; ?></td>
                <td><?php echo $row['first_name']; ?></td>
                <td><?php echo $row['last_name']; ?></td>
                <td><?php echo $row['table_number']; ?></td>
                <td><?php echo $row['reservation_date']; ?></td>
                <td><?php echo $row['time_in']; ?></td>
                <td><?php echo $row['time_out']; ?></td>
                <td><?php echo $row['guest_count']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                <td>
                    <?php if (strtolower($row['status']) != 'completed'): ?>
                        <form method="POST">
                            <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                            <input type="hidden" name="table_number" value="<?php echo $row['table_number']; ?>">
                            <button type="submit" name="complete_reservation" class="mark-completed-btn">Mark Completed</button>
                        </form>
                    <?php else: ?>
                        <button class="completed">Completed</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
</div>
<br>
</body>
</html>
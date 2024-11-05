<?php
require 'dbconnection.php'; // Include the database connection

// Check if all required POST parameters are set
if (isset($_POST['user_id']) && isset($_POST['reservation_date']) && isset($_POST['guest_count']) && isset($_POST['time_in']) && isset($_POST['time_out']) && isset($_POST['table_number']) && isset($_POST['total_price'])) {
    $user_id = $_POST['user_id'];
    $reservation_date = $_POST['reservation_date'];
    $guest_count = $_POST['guest_count'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $table_number = $_POST['table_number'];
    $total_price = $_POST['total_price'];

    // Insert reservation into the reservations table
    $sql = "INSERT INTO reservations (user_id, reservation_date, guest_count, table_number, time_in, time_out, total_price)
            VALUES (:user_id, :reservation_date, :guest_count, :table_number, :time_in, :time_out, :total_price)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':reservation_date', $reservation_date);
    $stmt->bindParam(':guest_count', $guest_count, PDO::PARAM_INT);
    $stmt->bindParam(':table_number', $table_number, PDO::PARAM_INT);
    $stmt->bindParam(':time_in', $time_in);
    $stmt->bindParam(':time_out', $time_out);
    $stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);

    // Execute the query
    if ($stmt->execute()) {
        $reservation_id = $pdo->lastInsertId(); // Get the newly created reservation ID
        ?>

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Details</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
         body {
            font-family: 'Rubik', sans-serif;
            margin: 0;


            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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
        /* Reservation container styling */
        .reservation-container {
            font-family: 'Rubik', sans-serif;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            border: 1px solid #ccc;
            text-align: center;
            position: relative;
        }

        .back-link {
            text-align: left;
            margin-bottom: 10px;
        }

        .back-link a {
            color: black;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }

        .reservation-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .reservation-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .reservation-container th, .reservation-container td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .reservation-container th {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        /* Confirm button styling */
        .confirm-btn {
            font-family: 'Rubik', sans-serif;
            width: 100%;
            background-color: #ffdb44;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .confirm-btn:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        img {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="reservation-container">
        <div><img src="img/gellineslogo.png" alt="gellineslogo"></div>

        <h1>Reservation Details</h1>
        <table>
            <tr>
                <th>Date</th>
                <td><?php echo htmlspecialchars($reservation_date); ?></td>
            </tr>
            <tr>
                <th>Guests</th>
                <td><?php echo htmlspecialchars($guest_count); ?></td>
            </tr>
            <tr>
                <th>Time In</th>
                <td><?php echo htmlspecialchars($time_in); ?></td>
            </tr>
            <tr>
                <th>Time Out</th>
                <td><?php echo htmlspecialchars($time_out); ?></td>
            </tr>
            <tr>
                <th>Table Number</th>
                <td><?php echo htmlspecialchars($table_number); ?></td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>₱<?php echo number_format($total_price, 2); ?></td>
            </tr>
        </table>

        <form action="respayment.php" method="GET">
            <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation_id); ?>">
            <button type="submit" class="confirm-btn">Proceed to Payment</button>
        </form>
    </div>
</body>
</html>


        <?php
    } else {
        echo "<h1>Error: Failed to make reservation.</h1>";
    }
} else {
    echo "<h1>Error: Missing reservation information.</h1>";
}
?>

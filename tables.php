<?php
require 'dbconnection.php'; // Include the database connection

if (isset($_POST['user_id']) && isset($_POST['reservation_date']) && isset($_POST['guests']) && isset($_POST['time'])) {
    $user_id = $_POST['user_id'];
    $reservation_date = $_POST['reservation_date'];
    $guest_count = $_POST['guests'];
    $reservation_time = $_POST['time'];

    // Calculate time out (2 hours after reservation time)
    $time_in = new DateTime($reservation_time);
    $time_out = clone $time_in;
    $time_out->modify('+2 hours');
    $formatted_time_out = $time_out->format('H:i');

    // Fetch all tables and check if they are occupied for the selected date and time
    $sql = "SELECT t.table_number, t.capacity, t.price_per_person, t.status, t.image, 
                   CASE
                       WHEN EXISTS (
                           SELECT 1 FROM reservations r
                           WHERE r.table_number = t.table_number
                           AND r.reservation_date = :reservation_date
                           AND (
                               (r.time_in <= :time_in AND r.time_out > :time_in) OR
                               (r.time_in < :time_out AND r.time_out >= :time_out) OR
                               (r.time_in >= :time_in AND r.time_out <= :time_out)
                           )
                           AND r.status = 'confirmed'
                       ) THEN 'occupied'
                       ELSE 'available'
                   END AS availability_status
            FROM restaurant_tables t
            WHERE t.capacity = :guests
            ORDER BY t.table_number ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':reservation_date', $reservation_date, PDO::PARAM_STR);
    $stmt->bindParam(':time_in', $reservation_time, PDO::PARAM_STR);
    $stmt->bindParam(':time_out', $formatted_time_out, PDO::PARAM_STR);
    $stmt->bindParam(':guests', $guest_count, PDO::PARAM_INT);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are available tables
    if (count($tables) === 0) {
        echo "No tables available for the selected date and time.";
        exit;
    }
} else {
    echo "Error: Missing reservation information.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Table</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
    /* General page styles */
    body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            margin-right: 3%;
            margin-left: 3%;
            background-size: cover;
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

          .home-btn,
          .myorders-btn,
          .ordr-btn,
          .lout-btn,
          .image-button {
              font-family: 'Rubik', sans-serif;
              background-color: transparent;
              border: none;
              color: black;
              padding: 5px 20px;
              font-size: 16px;
              cursor: pointer;
              transition: background-color 0.3s ease, box-shadow 0.3s ease;
              text-shadow: ;
              letter-spacing: 2px;
          }

          /* Semi-transparent yellow hover effect */
          .home-btn:hover,
          .myorders-btn:hover,
          .ordr-btn:hover,
          .lout-btn:hover,
          .image-button:hover {
              background-color: rgba(0, 0, 0, 0.1); /* Transparent yellow */
              box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.4); /* Elevation effect */
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

    .reservation-container{
        background-color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        max-height: 500px;
        height: 330px;
        width: 500px;
        border: 1px solid #ccc;
    }

    .table-container {
        flex: 2;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-option {
        width: 238px;
        background-color: #f9f9f9;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
    }

    .table-option img {
        max-width: 100%;
        border-radius: 5px;
        margin-top: 10px;
    }

    .table-available {
        background-color: #d4edda;
    }

    .table-occupied {
        background-color: #f8d7da;
        cursor: not-allowed;
    }

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
        margin-top: 20px;
    }

    .confirm-btn:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Container styling */
    .container {
        margin-top: 130px;
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }

    .reservation-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .reservation-container table, .reservation-container th, .reservation-container td {
        border: 1px solid #ccc;
    }

    .reservation-container th, .reservation-container td {
        padding: 10px;
        text-align: left;
    }
</style>


    <script>
        // Handle table selection
        function selectTable(tableNumber, totalPrice) {
            let confirmMessage = `You have selected Table ${tableNumber} with a total price of ₱${totalPrice}. Do you want to proceed?`;
            if (confirm(confirmMessage)) {
                document.getElementById('confirm-container').style.display = 'block';
                document.getElementById('selected_table_number').value = tableNumber;
                document.getElementById('selected_total_price').value = totalPrice;
            }
        }

        // Submit form when user confirms reservation
        function redirectToSubmit() {
            document.getElementById('reservation-form').submit();
        }
    </script>
</head>

<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>

            
            <button class="ordr-btn" onclick="window.location.href = 'login.php';">Logout</button>
        </div>
    </header>
    <div class="container">
        <div class="reservation-container">
            <a href="reserve.php" style="display: inline-block; margin-bottom: 10px; color: black; text-decoration: none; font-weight: bold;">&#8617; Back</a>

            <h1>Select a Table</h1>
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
                    <td><?php echo htmlspecialchars($reservation_time); ?></td>
                </tr>
                <tr>
                    <th>Time Out</th>
                    <td><?php echo htmlspecialchars($formatted_time_out); ?></td>
                </tr>
            </table>
            <div id="confirm-container" style="display: none;">
                <form id="reservation-form" method="POST" action="reserve_submit.php">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <input type="hidden" name="reservation_date" value="<?php echo htmlspecialchars($reservation_date); ?>">
                    <input type="hidden" name="guest_count" value="<?php echo htmlspecialchars($guest_count); ?>">
                    <input type="hidden" name="time_in" value="<?php echo htmlspecialchars($reservation_time); ?>">
                    <input type="hidden" name="time_out" value="<?php echo htmlspecialchars($formatted_time_out); ?>">
                    <input type="hidden" id="selected_table_number" name="table_number">
                    <input type="hidden" id="selected_total_price" name="total_price">
                    <button type="button" class="confirm-btn" onclick="redirectToSubmit()">Proceed to Reservation</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <?php foreach ($tables as $table): 
                $total_price = $table['capacity'] * $table['price_per_person'];
                $table_class = ($table['availability_status'] == 'available') ? 'table-available' : 'table-occupied';

                // If the table is occupied, show reservation details
                if ($table['availability_status'] == 'occupied') {
                    $reservation_info_stmt = $pdo->prepare("SELECT time_in, time_out, user_id FROM reservations WHERE table_number = ? AND status = 'confirmed'");
                    $reservation_info_stmt->execute([$table['table_number']]);
                    $reservation_info = $reservation_info_stmt->fetch(PDO::FETCH_ASSOC);
                    $time_in = $reservation_info['time_in'];
                    $time_out = $reservation_info['time_out'];
                }
            ?>
                <div class="table-option <?php echo $table_class; ?>" 
                     <?php if ($table['availability_status'] == 'available'): ?> 
                     onclick="selectTable(<?php echo htmlspecialchars($table['table_number']); ?>, <?php echo $total_price; ?>)" 
                     <?php endif; ?>>

                    <p>Table Number: <?php echo htmlspecialchars($table['table_number']); ?></p>
                    <p>Capacity: <?php echo htmlspecialchars($table['capacity']); ?></p>
                    <p>Total Price: ₱<?php echo number_format($total_price, 2); ?></p>

                    <?php if ($table['availability_status'] == 'occupied'): ?>
                        <h3>Reserved</h3>
                        <!-- <p>Time In: <?php echo htmlspecialchars($time_in); ?></p>
                        <p>Time Out: <?php echo htmlspecialchars($time_out); ?></p> -->
                    <?php endif; ?>

                    
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>

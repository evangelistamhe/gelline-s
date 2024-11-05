<?php
$order_id = $_GET['order_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">

    <!-- Auto redirect after 5 seconds -->
    <meta http-equiv="refresh" content="5;url=home.php">
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
        a {
            color: #ffdb44;
            text-decoration: underline;
            
        }

        a:hover {
            color: #e6c84e;
            
        }

    </style>
</head>
<body>
    <div class="reservation-container">
        <h1>Payment Successful</h1>
        <p>Thank you for your payment. Your order has been placed successfully.</p>
        <p>Your Order ID is: <b><span class="order-id"><?php echo htmlspecialchars($order_id); ?></span></b></p>
        <p>A receipt has been sent to your email.</p>
        <p>You will be redirected to the homepage in <span id="countdown">5</span> seconds.</p>
        <br>
        <hr>
        <p>If you do not want to wait, <a href="reservations.php" onclick="redirectNow()">click here to go now</a>.</p>

        <!-- JavaScript to display a countdown timer for redirection -->
       <script>
            let countdown = 5; // 5 seconds countdown
            const countdownSpan = document.getElementById('countdown');
            const interval = setInterval(() => {
                countdown--;
                countdownSpan.textContent = countdown;
                if (countdown === 0) {
                    clearInterval(interval);
                }
            }, 1000);
        </script>
    </div>
</body>
</html>

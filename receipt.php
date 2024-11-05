<?php
// Assuming you already have session and reservation_id set up
session_start();

if (!isset($_GET['reservation_id'])) {
    echo "Invalid access.";
    exit;
}

$reservation_id = htmlspecialchars($_GET['reservation_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Gelline's Sizzling and Restaurant</title>
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
     <script>
    // JavaScript Countdown for Auto-Redirect to home.php after 3 seconds
    let countdown = 5;
    function startCountdown() {
        let countdownElement = document.getElementById('countdown');
        let interval = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(interval);
                window.location.href = 'reservations.php';
            }
        }, 1000);
    }

    function redirectNow() {
        window.location.href = 'reservations.php';
    }

    window.onload = startCountdown;
</script>

</head>
<body>
    <div class="reservation-container">
    <h1>Payment Successful!</h1>
    <br>
    <p>Your reservation has been confirmed.</p>
    <p>You will be redirected to the homepage in <span id="countdown">5</span> seconds.</p>
    <br>
    <hr>
    <p>If you do not want to wait, <a href="reservations.php" onclick="redirectNow()">click here to go now</a>.</p>
    </div>
</body>
</html>

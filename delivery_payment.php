<?php
session_start();
require 'dbconnection.php';

// Fetch order details based on order_id
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo "Order ID is missing!";
    exit;
}

// Fetch the order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch the total amount
$total_amount = $order['total_amount'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            background-color: #f3f7fe;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .payment-container {
            background-color: white;
            padding: 20px;
            width: 350px;
            border-radius: 15px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile {
            margin-top: 15px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #ccc;
            display: inline-block;
            font-size: 32px;
            color: white;
            line-height: 70px;
            margin-bottom: 10px;
        }

        .g-container {
            background-color: #0052CC;
        }

        h2 {
            font-size: 18px;
            margin: 5px 0;
            color: #fff;
            background-color: #1d75e9;
            padding: 10px;
            border-radius: 10px 10px 0 0;
        }

        .header {
            background-color: #0047a9;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .pay-with {
            background-color: #e9f0fb;
            padding: 10px 15px;
            margin-top: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #ddd;
        }

        .pay-with span {
            font-size: 14px;
            color: #333;
        }

        .pay-with .pay-method {
            color: #2d84e3;
            font-weight: bold;
        }

        .amount-section {
            margin-top: 20px;
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .amount-section p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .amount-section .total-amount {
            font-size: 16px;
            font-weight: bold;
        }

        .total-section {
            background-color: #f3f7fe;
            padding: 10px;
            border-radius: 8px;
            text-align: left;
            margin-top: 15px;
        }

        .total-section p {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }

        .total-section .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .pay-button {
            margin-top: 20px;
            padding: 15px;
            background-color: #1d75e9;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
        }

        .pay-button:hover {
            background-color: #155bb5;
        }

        .note {
            font-size: 12px;
            margin-top: 10px;
            color: #555;
        }

        .pay-check {
            width: 20px;
            height: 20px;
            background-color: #1d75e9;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }

        .pay-check::after {
            content: "";
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            display: inline-block;
            position: absolute;
            left: 6px;
            top: 3px;
            transform: rotate(45deg);
        }

     .restaurant-title {
    margin-top: 10px;
    font-size: 18px;
    font-weight: bold;
    color: #fff; /* Change text color to white for better contrast */
    background-color: #0052cc; /* Add background color */
    padding: 10px; /* Add some padding around the text */
    border-radius: 0 0 10px 10px; /* Optional: Add rounded corners at the bottom */
}

    </style>
</head>
<body>
    <div class="payment-container">
        
        <div class="header">Payment</div>
        <div class="g-container">
        <!-- Profile placeholder -->
        <div class="profile">G</div>

        <div class ="restaurant-title"> Gelline's Sizzling and Restaurant</div>
        </div>
        <!-- Payment method section -->
        <div class="pay-with">
            <span>PAY WITH</span>
            <div style="display: flex; align-items: center;">
                <span class="pay-method">GCash</span>
                <div class="pay-check" style="margin-left: 10px;"></div>
            </div>
        </div>

        <!-- Amount Details -->
        <div class="amount-section">
            <p>Amount Due</p>
            <p class="total-amount">PHP <?= number_format($total_amount, 2); ?></p>
        </div>

        <!-- Total Section -->
        <div class="total-section">
            <p>Total Amount</p>
            <p class="total-amount">PHP <?= number_format($total_amount, 2); ?></p>
        </div>

        <!-- Pay Button -->
        <form method="post" action="delivery_payment_success.php">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id); ?>">
            <button type="submit" class="pay-button">Pay PHP <?= number_format($total_amount, 2); ?></button>
        </form>

        <!-- Note -->
        <p class="note">Please review to ensure the details are correct before you proceed.</p>
    </div>
</body>
</html>
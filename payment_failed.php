<?php
session_start();
require 'dbconnection.php';

// Ensure that the 'order_id' is set in the URL
if (!isset($_GET['order_id'])) {
    echo "Order ID is missing!";
    exit;
}

$order_id = $_GET['order_id'];

// Update the payment status to 'Failed' in the payments table
$stmt = $pdo->prepare("UPDATE payments SET payment_status = 'Failed' WHERE order_id = ?");
$stmt->execute([$order_id]);

// Display a failed message to the user
echo "<h1>Payment Failed</h1>";
echo "<p>Unfortunately, your payment was not successful. Please try again.</p>";

// Optionally, redirect to the cart or checkout page
header('refresh:5;url=cart.php');
exit;
?>

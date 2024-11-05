<?php
session_start();
require 'dbconnection.php';

// Ensure that the 'order_id' is set in the URL
if (!isset($_GET['order_id'])) {
    echo "Order ID is missing!";
    exit;
}

$order_id = $_GET['order_id'];

// Update the payment status to 'Paid' in the payments table
$stmt = $pdo->prepare("UPDATE payments SET payment_status = 'Paid' WHERE order_id = ?");
$stmt->execute([$order_id]);

// Optionally, update the order status to 'Completed' if needed
$stmt = $pdo->prepare("UPDATE orders SET order_status = 'Pending' WHERE order_id = ?");
$stmt->execute([$order_id]);

// Display a success message to the user
echo "<h1>Order successfully placed!</h1>";
echo "<p>Your payment was received. Thank you for your order!</p>";

// Optionally, redirect to a different page after a delay
header('refresh:5;url=home.php');
exit;
?>

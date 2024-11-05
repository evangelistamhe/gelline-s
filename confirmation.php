<?php
session_start();
require 'dbconnection.php'; // Include your database connection

if (!isset($_GET['reservation_id'])) {
    echo "Invalid access.";
    exit;
}

$reservation_id = $_GET['reservation_id'];

// Fetch the reservation and payment details
$stmt = $pdo->prepare("
    SELECT r.*, t.table_number, t.capacity, t.price_per_person, p.amount, p.respayment_method, p.paid_at
    FROM reservations r
    JOIN restaurant_tables t ON r.table_id = t.table_id
    JOIN respayments p ON r.reservation_id = p.reservation_id
    WHERE r.reservation_id = ?
");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    echo "Reservation or payment not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Gelline's Sizzling and Restaurant</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="confirmation-container">
        <h2>Reservation and Payment Confirmation</h2>

        <!-- Display reservation details -->
        <p>Reservation ID: <?= htmlspecialchars($reservation_id); ?></p>
        <p>Table: <?= htmlspecialchars($reservation['table_number']); ?> (Capacity: <?= htmlspecialchars($reservation['capacity']); ?>)</p>
        <p>Number of Guests: <?= htmlspecialchars($reservation['guests']); ?></p>
        <p>Time In: <?= htmlspecialchars($reservation['time_in']); ?></p>
        <p>Time Out: <?= htmlspecialchars($reservation['time_out']); ?></p>
        <p>Total Amount: ₱<?= number_format($reservation['amount'], 2); ?></p>
        <p>Payment Method: <?= htmlspecialchars($reservation['respayment_method']); ?></p>
        <p>Paid At: <?= htmlspecialchars($reservation['paid_at']); ?></p>

        <p>Thank you for your payment! We look forward to serving you.</p>
    </div>
</body>
</html>

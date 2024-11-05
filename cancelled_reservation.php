<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$reservation_id = $_GET['id'] ?? null;

if ($reservation_id) {
    // Update the reservation status to 'cancelled'
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = :reservation_id AND user_id = :user_id");
    $stmt->execute(['reservation_id' => $reservation_id, 'user_id' => $user_id]);

    // Redirect back to the reservations page
    header('Location: my_reservations.php');
    exit;
} else {
    // Redirect back if no reservation ID is provided
    header('Location: my_reservations.php');
    exit;
}
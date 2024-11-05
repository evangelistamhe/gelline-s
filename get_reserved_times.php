<?php
require 'dbconnection.php'; // Include your database connection

if (isset($_POST['date'])) {
    $date = $_POST['date'];

    // Query the database to get the time_in and time_out for the selected date
    $stmt = $pdo->prepare("SELECT time_in, time_out FROM reservations WHERE date = ?");
    $stmt->execute([$date]);
    $reserved_times = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch the reserved time ranges

    // Return the reserved times as a JSON object
    echo json_encode($reserved_times);
} else {
    echo json_encode(['error' => 'Date not provided']);
}
?>

<?php
require 'dbconnection.php';

if (!isset($_GET['date']) || !isset($_GET['time_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Date or time not provided']);
    exit;
}

$date = $_GET['date'];
$time_in = $_GET['time_in'];
$time_out = date('H:i', strtotime($time_in) + 7200); // Add 2 hours for time_out

// Fetch reserved tables that are currently occupied during the selected time
$stmt = $pdo->prepare("
    SELECT table_id 
    FROM reservations 
    WHERE date = ? AND NOT (
        time_out <= ? OR time_in >= ?
    )
");
$stmt->execute([$date, $time_in, $time_out]);

$stmt->execute([$date, $time_out, $time_in, $time_in, $time_out]);

$reserved_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Return reserved table IDs in JSON format
echo json_encode(['reserved_tables' => $reserved_tables]);

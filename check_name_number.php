<?php
require 'dbconnection.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $mobile_number = trim($_POST['mobile_number']);

    // Check if the first and last name combination exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE first_name = ? AND last_name = ?");
    $stmt->execute([$first_name, $last_name]);
    $name_exists = $stmt->fetchColumn() > 0;

    // Check if the mobile number exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE mobile_number = ?");
    $stmt->execute([$mobile_number]);
    $number_exists = $stmt->fetchColumn() > 0;

    // Return appropriate responses
    if ($name_exists) {
        echo 'name_exists';
    } elseif ($number_exists) {
        echo 'number_exists';
    } else {
        echo 'not_exists'; // Neither name nor number exists
    }
}
?>

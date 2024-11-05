<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barangay = $_POST['barangay'] ?? null;
    $street = $_POST['street'] ?? null;
    $city = $_POST['city'] ?? null;

    $user_id = $_SESSION['user_id'];

    if ($barangay && $street && $city) {
        // Insert the new address into the user's profile
        $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, user_brgy, user_street, user_city) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $barangay, $street, $city]);

        // After adding the address, redirect the user back to home.php
        header('Location: home.php');
        exit;
    } else {
        // If there are missing fields, redirect back to home.php with an error (You can customize this)
        header('Location: home.php?error=missing_fields');
        exit;
    }
}


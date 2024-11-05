<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_brgy = trim($_POST['user_brgy']);
    $user_street = trim($_POST['user_street']);
    $user_city = trim($_POST['user_city']);

    if (!empty($user_brgy) && !empty($user_street) && !empty($user_city)) {
        // Insert the new address into the user_addresses table
        $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, user_brgy, user_street, user_city) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_brgy, $user_street, $user_city]);

        // Redirect back to profile.php after the update
        header('Location: profile.php?message=address_added');
        exit;
    } else {
        // If any field is empty, redirect with an error message
        header('Location: profile.php?error=empty_address');
        exit;
    }
} else {
    // Redirect back if accessed via a method other than POST
    header('Location: profile.php');
    exit;
}

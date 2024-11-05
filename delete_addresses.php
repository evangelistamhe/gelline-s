<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is established

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedIds'])) {
    // Get the selected address IDs from the form
    $selectedIds = explode(',', $_POST['selectedIds']);

    if (!empty($selectedIds)) {
        // Prepare placeholders for the selected address IDs
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));

        // Prepare the SQL query to delete the selected addresses
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE address_id IN ($placeholders) AND user_id = ?");

        // Execute the query by merging the selected IDs and user ID
        $stmt->execute(array_merge($selectedIds, [$user_id]));

        // Redirect back to the profile page with a message
        header('Location: profile.php?message=address_deleted');
        exit;
    } else {
        // If no addresses are selected, redirect back with an error message
        header('Location: profile.php?message=no_addresses_selected');
        exit;
    }
} else {
    // If the request is invalid, redirect back with an error message
    header('Location: profile.php?message=invalid_request');
    exit;
}

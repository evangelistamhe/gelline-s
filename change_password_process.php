<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user session exists
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch the user's current hashed password from the database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify if the entered current password matches the one in the database
    if (password_verify($current_password, $user['password'])) {
        // Store the new password temporarily and redirect to the security questions page
        $_SESSION['new_password'] = $new_password;
        header('Location: security_questions.php'); // Redirect to security questions page
        exit();
    } else {
        // Current password is incorrect, show an error message
        echo "<script>alert('Incorrect current password. Please try again.'); window.location.href = 'profile.php';</script>";
    }
}
?>

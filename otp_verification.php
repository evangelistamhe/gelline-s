<?php
session_start();

require 'dbconnection.php'; // Ensure the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php'; // Load Composer's autoloader for PHPMailer

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp_code'];

    // Check if entered OTP matches the one stored in the session
    if ($entered_otp == $_SESSION['otp_code']) {
        // OTP is correct, update the password in the database
        $new_password_hashed = password_hash($_SESSION['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$new_password_hashed, $_SESSION['user_id']]);

        // Clear session values after successful password update
        unset($_SESSION['otp_code'], $_SESSION['new_password']);

        echo "<script>alert('Password changed successfully!'); window.location.href = 'profile.php';</script>";
    } else {
        echo "<script>alert('Incorrect OTP. Please try again.');</script>";
    }
} else {
    // Generate OTP
    $otp_code = random_int(100000, 999999);
    $_SESSION['otp_code'] = $otp_code;

    // Get the user's email from the database
    $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_email = $stmt->fetchColumn();

    // Send OTP to user's email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use your SMTP provider
        $mail->SMTPAuth = true;
        $mail->Username = 'gelssizzlingresto@gmail.com'; // Your Gmail address
        $mail->Password = 'xhdh mqrv axlo xsnm'; // Your Gmail password or app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email content
        $mail->setFrom('gelssizzlingresto@gmail.com', 'Gelline\'s Restaurant');
        $mail->addAddress($user_email); // Recipient's email
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code For Change Password';
        $mail->Body = "Your OTP code is: <strong>$otp_code</strong>";

        $mail->send();
        echo "<script>alert('OTP has been sent to your email.');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send OTP. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="style/otp_verification.css">
    <title>OTP Verification</title>
</head>
<body>
    <div class="otp">
        <h2>Enter OTP</h2>
        <form method="post" action="">
            <label for="otp_code">OTP Code:</label>
            <input type="text" name="otp_code" id="otp_code" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>

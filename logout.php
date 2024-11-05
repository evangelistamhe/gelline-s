<?php
session_start();
require 'dbconnection.php'; // Include your database connection

// Check if the user confirmed the logout action
if (isset($_POST['confirm_logout'])) {
    if ($_POST['confirm_logout'] == 'yes') {
        // User confirmed the logout

        // Log the logout action in the audit_trail table
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $action = 'logout';
            $log_stmt = $pdo->prepare("INSERT INTO audit_trail (user_id, action) VALUES (?, ?)");
            $log_stmt->execute([$user_id, $action]);
        }

        // Destroy the session and redirect to login
        session_destroy();
        header('Location: login.php');
        exit;
    } else {
        // User canceled the logout, redirect to home
        header('Location: home.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <script>
        // JavaScript to confirm the logout
        function confirmLogout() {
            let confirmation = confirm("Are you sure you want to log out?");
            if (confirmation) {
                // Submit the form with 'yes' as the value
                document.getElementById('logout-form').confirm_logout.value = 'yes';
            } else {
                // Submit the form with 'no' as the value
                document.getElementById('logout-form').confirm_logout.value = 'no';
            }
            document.getElementById('logout-form').submit();
        }
    </script>
</head>
<body>

<form id="logout-form" method="post" action="logout.php">
    <input type="hidden" name="confirm_logout" value="">
</form>

<script>
    // Trigger the confirmation on page load
    confirmLogout();
</script>

</body>
</html>

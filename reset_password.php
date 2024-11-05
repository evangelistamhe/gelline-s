<?php
session_start();
require 'dbconnection.php'; // Database connection

$message = ""; // Feedback message

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Set timezone to avoid expiration time mismatch (Optional)
    $pdo->exec("SET time_zone = '+00:00'");

    // Query to validate token and check expiration
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE password_reset_token = ? 
        AND password_reset_expires > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Token is valid
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $new_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $message = "Passwords do not match.";
            } elseif (strlen($new_password) < 8) {
                $message = "Password must be at least 8 characters.";
            } else {
               $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);


                // Update the password and clear the token
                $stmt = $pdo->prepare(
    "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE user_id = ?"
);
$stmt->execute([$hashed_password, $user['user_id']]);


                echo "
                <script>
                    if (confirm('Password reset successfully! Do you want to sign in now?')) {
                        window.location.href = 'login.php';
                    }
                </script>";
            }
        }
    } else {
        // Invalid or expired token
        $message = "Invalid or expired token.";
    }
} else {
    // No token found in the URL
    $message = "Invalid request. No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url(img/gellinesbg.png) repeat;
            background-size: 120px 120px;
            filter: grayscale(100%);
            opacity: 0.03;
            z-index: -1;
        }
        .container {
            width: 100%;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="password"],
        input[type="text"] {
            width: 90%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Rubik', sans-serif;
        }

        .show-password-container {
            display: flex;
            align-items: center;
            justify-content: start;
            margin-bottom: 15px;
            width: 100%;
        }

        .show-password-container input[type="checkbox"] {
            margin-right: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #ffdb44;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        button:hover {
            background-color: #e2c43b;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        a {
            color: #ffdb44;
            text-decoration: underline;
        }

        a:hover {
            color: #e2c43b;
        }

        p {
            margin-top: 10px;
            color: #555;
        }

        .message {
            margin-top: 10px;
            color: red;
        }
        label input[type="checkbox"] {
            accent-color: #ffdb44;
            cursor: pointer;
        }

    </style>
    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            confirmPassword.type = type;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>
        <form method="POST" action="">
            <input type="password" name="password" id="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <div class="show-password-container">
                <input type="checkbox" onclick="togglePasswordVisibility()">
                <label>Show Password</label>
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>

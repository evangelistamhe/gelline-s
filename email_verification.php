<?php
session_start();
require 'dbconnection.php'; // Database connection

if (isset($_GET['email'])) {
    $email = $_GET['email'];
} else {
    die("Invalid request.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entered_code = $_POST['verification_code'];
    $email = $_POST['email'];

    // Check if the code matches the one in the database
    $stmt = $pdo->prepare("SELECT verification_code FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $entered_code) {
        // Mark the email as verified
        $update_stmt = $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE email = ?");
        $update_stmt->execute([$email]);

        echo "<script>
                alert('Email successfully verified!');
                window.location.href = 'login.php'; // Redirect to login
              </script>";
    } else {
        echo "<p style='color:red;'>Invalid verification code. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
        .home-container {
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
            width: 100%;
            height: 120px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: -5px;
            right: 0px;
            padding: 0 30px;
            z-index: 2;
          }

          .logo img {
              height: 120px;
              width: 130px;
              
              left: 0;
              top: 0px;
              z-index: 3;
              margin-left: 50%;
              margin-right: 50%;
          }

        .verification-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            margin-left: 20%;
            margin-right: 20%;
        }

        .form-input {
            width: 95%;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            font-weight: bold;
            width: 100%;
            color: black;
            background-color: #ffdb44;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #e6c84e;
        }
    </style>
</head>
<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
        </div>
    </header>
    <div class="verification-container">
        <h1>Email Verification</h1>
        <p>Please enter the verification code sent to your email.</p>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="text" name="verification_code" class="form-input" 
                   placeholder="Enter Verification Code" required maxlength="6">
            <button type="submit" class="btn">Verify Email</button>
        </form>
    </div>
</body>
</html>

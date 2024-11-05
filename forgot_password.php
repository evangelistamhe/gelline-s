<?php
session_start();
require 'dbconnection.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php'; // Load PHPMailer

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = ""; // Variable to store the message

// Check if we're in the second step
if (isset($_SESSION['step'])) {
    $step = $_SESSION['step'];
} else {
    $step = 1;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($step === 1 && isset($_POST['email'])) {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } else {
            // Check if the user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Save the user's selected questions and hashed answers in session
                $_SESSION['email'] = $email;
                $_SESSION['security_question1'] = $user['security_question1'];
                $_SESSION['security_question2'] = $user['security_question2'];
                $_SESSION['security_answer1'] = $user['security_answer1']; // Hashed answer 1
                $_SESSION['security_answer2'] = $user['security_answer2']; // Hashed answer 2

                // Move to step 2
                $_SESSION['step'] = 2;
                $step = 2;
            } else {
                $message = "No account found with this email.";
            }
        }
    } elseif ($step === 2 && isset($_POST['security_answer1'], $_POST['security_answer2'])) {
        // Validate the security answers
        $security_answer1 = trim($_POST['security_answer1']);
        $security_answer2 = trim($_POST['security_answer2']);

        // Use password_verify to compare input with hashed answers
        if (password_verify($security_answer1, $_SESSION['security_answer1']) && password_verify($security_answer2, $_SESSION['security_answer2'])) {
            // Correct answers, proceed to send the password reset email
            $email = $_SESSION['email'];

            try {
                // Generate secure token and expiration time
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

                // Save the token and expiration in the database
                $stmt = $pdo->prepare("UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?");
                $stmt->execute([$token, $expires, $email]);

                // Create reset link
                $reset_link = "http://localhost/gels/reset_password.php?token=" . $token;

                // PHPMailer setup
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'gelssizzlingresto@gmail.com'; // Your email
                $mail->Password = 'xhdh mqrv axlo xsnm'; // Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('gelssizzlingresto@gmail.com', 'Gelline\'s Restaurant');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <p>Click the link below to reset your password. This link will expire in 1 hour:</p>
                    <p><a href='$reset_link'>Reset Password</a></p>";

                // Send email
                if ($mail->send()) {
                    $message = "A password reset link has been sent to your email. Please check your inbox.";
                    echo "<script>
                        alert('$message');
                        window.location.href = 'login.php';
                    </script>";
                    exit(); // Ensure no further code is executed after redirect
                } else {
                    $message = "Email could not be sent. Please try again later.";
                }
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Incorrect answers to the security questions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        input {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin: 10px auto;
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

        p {
            color: #555;
            margin-top: 10px;
        }
        label {
            font-size: 16px;
        }
    </style>
    <script>
        window.onload = function() {
            const message = "<?php echo $message; ?>";
            if (message) {
                alert(message);
            }

            // Initialize steps: hide step 2 initially
            document.getElementById('step2').style.display = 'none';

            // Handle step navigation
            document.getElementById('nextButton').onclick = function() {
                const email = document.getElementById('email').value;
                
                if (email) {
                    // For demonstration purposes, move to Step 2
                    // You can add email validation logic here if needed
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2').style.display = 'block';
                } else {
                    alert('Please enter your email address.');
                }
            };

            // Handle back button to return to Step 1
            document.getElementById('backButton').onclick = function() {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step1').style.display = 'block';
            };
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST" action="">

            <!-- Step 1: Enter Email -->
            <div id="step1">
                <input type="email" name="email" id="email" placeholder="Enter your email address" required>
                <button type="button" id="nextButton">Next</button>
            </div>

            <!-- Step 2: Answer Security Questions -->
            <div id="step2">
                <label><?php echo htmlspecialchars($_SESSION['security_question1'] ?? 'What is your pet\'s name?'); ?></label>
                <input type="text" name="security_answer1" placeholder="Security Answer 1" required><br>

                <label><?php echo htmlspecialchars($_SESSION['security_question2'] ?? 'What is your mother\'s maiden name?'); ?></label>
                <input type="text" name="security_answer2" placeholder="Security Answer 2" required><br>
                
                
                <button type="submit">Send Link</button>
                <button type="button" id="backButton">Back</button>
            </div>

        </form>
    </div>
</body>
</html>

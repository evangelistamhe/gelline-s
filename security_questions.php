<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection

$user_id = $_SESSION['user_id'];

// Fetch security questions from the database
$stmt = $pdo->prepare("SELECT security_question1, security_question2 FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer1 = $_POST['answer1'];
    $answer2 = $_POST['answer2'];

    // Verify the answers (hash comparison with database stored values)
    $stmt = $pdo->prepare("SELECT security_answer1, security_answer2 FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $answers = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($answer1, $answers['security_answer1']) && password_verify($answer2, $answers['security_answer2'])) {
        // If answers match, send OTP and redirect to the OTP verification page
        $otp_code = random_int(100000, 999999);
        $_SESSION['otp_code'] = $otp_code;

        // Send OTP to user's email
        $email_stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
        $email_stmt->execute([$user_id]);
        $user_email = $email_stmt->fetchColumn();

        mail($user_email, "Your OTP Code", "Your OTP code is: $otp_code");

        header('Location: otp_verification.php');
        exit();
    } else {
        echo "<script>alert('Incorrect answers to the security questions. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Questions</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap">
    <link rel="stylesheet" href="style/security_questions.css"> <!-- Link to your existing CSS file for consistency -->
</head>
<body>
    <div class="security-container">
        <h2>Answer Security Questions</h2>
        <form method="post" action="">
            <label><?php echo htmlspecialchars($user['security_question1']); ?></label>
            <input type="text" name="answer1" required>

            <label><?php echo htmlspecialchars($user['security_question2']); ?></label>
            <input type="text" name="answer2" required>

            <button type="submit">Submit Answers</button>
        </form>
    </div>
</body>
</html>

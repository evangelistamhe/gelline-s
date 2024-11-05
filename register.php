<?php
session_start();
require 'dbconnection.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/vendor/autoload.php'; // Load Composer's autoloader

ob_start(); // Start output buffering

// Fetch security questions from the database
$stmt = $pdo->query("SELECT * FROM security_questions");
$security_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// For pages where login is required (like placing an order), use this logic.
// But for public pages like Home or About Us, you don't need this redirect.


// Assuming you have a session variable 'logged_in' that tracks if the user is logged in
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If the user is not logged in, disable the Place Order button
  //  echo '<button class="disabled" onclick="alert(\'You must be logged in to place an order.\');">Place Order</button>';
// } else {
    // If the user is logged in, allow them to place an order
  //  echo '<button onclick="window.location.href = \'order.php\';">Place Order</button>';
// }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $first_name = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
    $last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mobile_number = filter_var(trim($_POST['mobile_number']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Sanitize and store security questions and answers
    $security_question1 = filter_var(trim($_POST['security_question1']), FILTER_SANITIZE_STRING);
    $security_answer1 = filter_var(trim($_POST['security_answer1']), FILTER_SANITIZE_STRING);
    $security_question2 = filter_var(trim($_POST['security_question2']), FILTER_SANITIZE_STRING);
    $security_answer2 = filter_var(trim($_POST['security_answer2']), FILTER_SANITIZE_STRING);

    $errors = [];

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!preg_match('/^\d{11}$/', $mobile_number)) {
        $errors[] = "Mobile number must be exactly 11 digits.";
    }
    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must be at least 8 characters long and contain at least one number.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_answer1 = password_hash($security_answer1, PASSWORD_DEFAULT); // Hash the security answer 1
            $hashed_answer2 = password_hash($security_answer2, PASSWORD_DEFAULT); // Hash the security answer 2
            $verification_code = random_int(100000, 999999);

            $stmt = $pdo->prepare("INSERT INTO users 
                (first_name, last_name, email, password, mobile_number, verification_code, security_question1, security_answer1, security_question2, security_answer2) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $hashed_password, $mobile_number, $verification_code, $security_question1, $hashed_answer1, $security_question2, $hashed_answer2]);

            // Send email for verification
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gelssizzlingresto@gmail.com'; // Update with your actual email
            $mail->Password = 'xhdh mqrv axlo xsnm'; // Use app-specific password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('gelssizzlingresto@gmail.com', 'Gelline\'s Restaurant');
            $mail->addAddress($email, "$first_name $last_name");
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = "<p>Your verification code is: <b style='font-size: 30px;'>$verification_code</b></p>";

            $mail->send();

            ob_end_clean(); // Clear any output before redirecting
            header("Location: email_verification.php?email=" . urlencode($email));
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        // Display errors
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">


      <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            padding: 0;
            background: none;
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


/* Add some custom styles for the eye icon */
        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        
.form-group {
    margin-bottom: 20px;
}



select, input[type="text"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    padding: 10px 20px;
    font-size: 16px;
    margin: 10px 5px;
    border: none;
    background-color: #ffdb44;
    color: #000;
    cursor: pointer;
    border-radius: 4px;
}

button:hover {
    background-color: #e6c84e;
}

.button-container {
    text-align: center;
}

button.prev {
    background-color: #ffdb44;
}

button.prev:hover {
    background-color: #e6c84e;
}


        .step {
    display: none;
}

.step.active {
    display: block;
}

        .home-container {
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
            width: 100%;
            height: 120px;
            
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: fixed;
            top: -5px;
            right: 0px;
            padding: 0 30px;
            z-index: 2;
          }

          .home-btn,
          .ordr-btn,
          .lout-btn,
          .image-button {
              font-family: 'Rubik', sans-serif;
              background-color: transparent;
              border: none;
              color: black;
              padding: 5px 20px;
              font-size: 16px;
              cursor: pointer;
              transition: background-color 0.3s ease, box-shadow 0.3s ease;
              text-shadow: ;
              letter-spacing: 2px;
          }

          /* Semi-transparent yellow hover effect */
          .home-btn:hover,
          .ordr-btn:hover,
          .lout-btn:hover,
          .image-button:hover {
              background-color: rgba(0, 0, 0, 0.1); /* Transparent yellow */
              box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.4); /* Elevation effect */
          }

          .logo img {
              height: 120px;
              width: 130px;
              position: absolute;
              left: 0;
              top: 0px;
              z-index: 3;
              margin-left: 130px;
          }

          .image-button {
              display: inline-block;
              border: none;
              padding: 0;
              background: none;
              cursor: pointer;
          }

          .image-button img {
              width: 30px;
              height: auto;
          }

          /* Dropdown CSS */
          .dropdown {
              position: relative;
              display: inline-block;
          }

          .dropdown-content {
              display: none;
              position: absolute;
              background-color: white;
              min-width: 160px;
              box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
              z-index: 1;
          }

          .dropdown-content a {
            font-family: 'Rubik', sans-serif;
              color: black;
              padding: 12px 16px;
              text-decoration: none;
              display: block;
          }

          .dropdown-content a:hover {
            color: black;
              background-color: rgba(0, 0, 0, 0.1);
          }

          .dropdown:hover .dropdown-content {
              display: block;
          }

        .register-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
            border-radius: 15px;
            width: 600px;
            height: 440px;
            padding: 20px;
            margin: 150px auto;
            box-shadow: 0px 4px 200px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 1px solid #ccc;
        }

        h1 {
            margin-bottom: 20px;
        }

        h3 {
            margin-bottom: 20px;
            color: #333;
        }

        /* Style for input fields */
         .form-input {
            border-radius: 5px !important;
            height: auto !important;
            width: 100% !important;
            max-width: 560px !important;
            margin-bottom: 15px !important;
            outline: none !important;
            border: 1px solid #ccc !important;
            padding: 10px !important;
            font-size: 16px !important;
        }

        .form-input::placeholder {
            font-family: 'Rubik', sans-serif;
            font-size: 16px;
        }
        .form-inputs {
            border-radius: 5px;
            height: 20px;
            width: 90%; /* Make the input take the full width of the container */
            margin-bottom: 5px;
            max-width: 580px;
            outline: none;
            border: 1px solid #ccc;
            padding: 0;
            font-size: 16px;
        }

        .form-inputs::placeholder {
            font-family: 'Rubik', sans-serif;
            font-size: 16px;
        }
        
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .register-container button {
            margin-top: 15px;
            background-color: #ffdb44;
            color: black;
            font-weight: bold;
            font-size: 16px;
            height: auto;
            width: 90%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 15px auto;
        }

        button:hover {
            background-color: #e6c84e;
        }

        .clear-button {
            margin: 10px auto;
            background-color: #ffdb44; /* Red for clear button */
            color: black;
        }

        /* Style for checkbox */
        label input[type="checkbox"] {
            accent-color: #ffdb44;
            cursor: pointer;
        }

        label {
            align-self: flex-start;
            margin: 10px 0;
            width: 100%;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .button-container {
            display: flex;
            justify-content: space-around; /* Fixes spacing */
            width: 100%; /* Ensures buttons take up full width */
            margin: 15px auto;
        }

        .button-container button {
            flex: 1; /* Allow buttons to grow evenly */
            margin: 0 10px; /* Spacing between buttons */
        }

        a {
            color: #ffdb44;
            text-decoration: underline;
            
        }

        a:hover {
            color: #e6c84e;
            
        }

        /* Style the checkbox */
        label input[type="checkbox"] {
            accent-color: #ffdb44; /* For supported browsers */
            cursor: pointer;
        }
    </style>
    <script>


    // Step 1 Validation
function validateStep1() {
    // Get user inputs and trim any extra spaces
    var firstName = document.getElementById('first_name').value.trim();
    var lastName = document.getElementById('last_name').value.trim();
    var mobileNumber = document.getElementById('mobile_number').value.trim();

    console.log("Validating Step 1...");

    // Check if any fields are empty
    if (firstName === '' || lastName === '' || mobileNumber === '') {
        alert('Please fill out all personal information fields.');
        return; // Stop the function execution
    }

    // Validate the mobile number format (exactly 11 digits)
    if (!/^\d{11}$/.test(mobileNumber)) {
        alert('Mobile number must be exactly 11 digits.');
        return; // Stop the function execution
    }

    // Confirmation before proceeding with AJAX call
    var confirmDetails = confirm(`Are you sure these details are correct?\n\nFirst Name: ${firstName}\nLast Name: ${lastName}\nMobile Number: ${mobileNumber}`);

    // Check if the user confirmed their details
    if (confirmDetails) {
        console.log("User confirmed details. Proceeding with AJAX call.");

        // Create a new XMLHttpRequest object
        var xhr = new XMLHttpRequest();
        
        // Open a new asynchronous POST request
        xhr.open('POST', 'check_name_number.php', true);
        
        // Set the correct content type for form data
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // Define the callback function to be executed when the response is ready
        xhr.onreadystatechange = function () {
            // Check if the request is complete and the response is ready
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Log the response from the server
                console.log("AJAX Response:", xhr.responseText);
                
                var response = xhr.responseText;
                
                // Handle different responses from the server
                if (response === 'name_exists') {
                    alert('An account with the same name already exists. Please use a different name.');
                } else if (response === 'number_exists') {
                    alert('An account with the same mobile number already exists. Please use a different mobile number.');
                } else {
                    // If no conflicts are found, proceed to the next step
                    showStep(2); // Function that moves the form to the next step
                }
            }
        };

        // Send the form data to the server
        var data = 'first_name=' + encodeURIComponent(firstName) + '&last_name=' + encodeURIComponent(lastName) + '&mobile_number=' + encodeURIComponent(mobileNumber);
        xhr.send(data);

    } else {
        // If user canceled the confirmation
        alert("Please review and correct your information if needed.");
    }
}



    // Step 2 Validation
function validateStep2() {
    var securityQuestion1 = document.getElementById('security_question1').value;
    var securityAnswer1 = document.getElementById('security_answer1').value.trim();
    var securityQuestion2 = document.getElementById('security_question2').value;
    var securityAnswer2 = document.getElementById('security_answer2').value.trim();

    // Check if the user selected identical questions
    if (securityQuestion1 === securityQuestion2) {
        alert('Please choose two different security questions.');
        return; // Stop here if the questions are identical
    }

    // Check if any of the questions or answers are empty
    if (securityQuestion1 === '' || securityAnswer1 === '' || securityQuestion2 === '' || securityAnswer2 === '') {
        alert('Please select and answer both security questions.');
    } else {
        // Show an alert with the selected questions and answers
        alert("Reminder: Please remember your security questions and answers for password recovery.\n\n" +
            "Security Question 1: " + securityQuestion1 + "\n" +
            "Answer to Security Question 1: " + securityAnswer1 + "\n\n" +
            "Security Question 2: " + securityQuestion2 + "\n" +
            "Answer to Security Question 2: " + securityAnswer2);

        showStep(3); // Proceed to the next step
    }
}


    // Form validation before final submission
   function showStep(step) {
    var steps = document.getElementsByClassName('step');
    for (var i = 0; i < steps.length; i++) {
        steps[i].classList.remove('active');
    }
    document.getElementById('step' + step).classList.add('active');
}

    // Toggle password visibility for both fields
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');
        const icon = document.getElementById('toggleIcon');

        if (passwordField.type === "password") {
            passwordField.type = "text";
            confirmPasswordField.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = "password";
            confirmPasswordField.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

// Password validation with additional complexity rules
function validatePassword(password) {
    const passwordCriteria = [
        { regex: /[A-Z]/, message: 'at least one uppercase letter (A-Z)' },
        { regex: /[a-z]/, message: 'at least one lowercase letter (a-z)' },
        { regex: /\d/, message: 'at least one number (0-9)' },
        { regex: /[^A-Za-z0-9]/, message: 'at least one special character (e.g., !, @, #, $)' },
        { regex: /.{8,}/, message: 'at least 8 characters long' }
    ];

    let errors = [];
    passwordCriteria.forEach((criterion) => {
        if (!criterion.regex.test(password)) {
            errors.push(criterion.message);
        }
    });

    return errors;
}

// Final form validation
// Final form validation
        function validateForm() {
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value.trim();
            var confirmPassword = document.getElementById('confirm_password').value.trim();

            // Email validation
            if (email === '' || password === '' || confirmPassword === '') {
                alert('Please fill out all login information fields.');
                return false;
            } else if (!validateEmail(email)) {
                alert('Please enter a valid email address.');
                return false;
            }

            // Password validation
            const passwordErrors = validatePassword(password);
            if (passwordErrors.length > 0) {
                alert('Your password must meet the following criteria:\n' + passwordErrors.join('\n'));
                return false;
            }

            // Confirm password validation
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return false;
            }

            // Final confirmation before submission
            var confirmDetails = confirm("Are you sure you want to submit the form with the entered details?");
            if (!confirmDetails) {
                return false; // Prevent form submission if user cancels
            }

            return true; // All validations passed
        }

        // Email format validation
        function validateEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Disable form submission until all fields are valid
        document.getElementById('registrationForm').onsubmit = function(event) {
            if (!validateForm()) {
                event.preventDefault(); // Prevent form submission if validation fails
            }
        };
</script>

</head>
<body>
   <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="home-btn" onclick="window.location.href = 'home2.php';">Home</button>
            <button class="ordr-btn" onclick="window.location.href = 'about2.php';">About Us</button>
          

            <div class="dropdown">
                <button class="ordr-btn">Menus</button>
                <div class="dropdown-content">
                    <a href="menu2.php">Food Menu</a>
                    
                </div>
            </div>
            
        </div>
    </header>

    <div class="register-container">
        <h2>Sign up</h2>
        <form method="post" action="register.php" id="registrationForm">
            <!-- Step 1: Personal Information -->
            <div class="step active" id="step1">
                <h3>Personal Information</h3>
                <input type="text" name="first_name" class="form-input" id="first_name" required placeholder="First Name">
                <input type="text" name="last_name" class="form-input" id="last_name" required placeholder="Last Name">
                <input type="text" name="mobile_number" class="form-input" id="mobile_number" maxlength="11" required placeholder="Mobile Number">
                <div class="button-container">

                <br>
                <br>
                    <button type="button" class="btn next" onclick="validateStep1()">Next</button>
                </div>
                <label>Already have an account?</label>
                <a href="login.php">Sign In</a>
            </div>

            <!-- Step 2: Security Questions -->
            <div class="step" id="step2">
                <div class="form-group">
                    <label for="security_question1">Security Question 1</label>
                    <select name="security_question1" id="security_question1" required>
                        <option value="">--Select a Security Question--</option>
                        <?php foreach ($security_questions as $question): ?>
                            <option value="<?php echo htmlspecialchars($question['question']); ?>">
                                <?php echo htmlspecialchars($question['question']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="security_answer1" id="security_answer1" class="form-inputs" required placeholder="Answer to Security Question 1">
                </div>

                <div class="form-group">
                    <label for="security_question2">Security Question 2</label>
                    <select name="security_question2" id="security_question2" required>
                        <option value="">--Select a Security Question--</option>
                        <?php foreach ($security_questions as $question): ?>
                            <option value="<?php echo htmlspecialchars($question['question']); ?>">
                                <?php echo htmlspecialchars($question['question']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="security_answer2" id="security_answer2" class="form-inputs" required placeholder="Answer to Security Question 2">
                </div>

                <div class="button-container">
                    <button type="button" class="btn prev" onclick="showStep(1)">Back</button>
                    <button type="button" class="btn next" onclick="validateStep2()">Next</button>
                </div>
            </div>

            <!-- Step 3: Login Information -->
            <div class="step" id="step3">
                <h3>Login Information</h3>
                <input type="email" name="email" id="email" class="form-input" required placeholder="Email Address">
                <input type="password" name="password" id="password" class="form-input" minlength="8" required placeholder="Password">
                <input type="password" name="confirm_password" id="confirm_password" class="form-input" minlength="8" required placeholder="Confirm Password"><br>
                <label><input type="checkbox" onclick="togglePasswordVisibility()"> Show Password</label><br>

                <div class="button-container">
                    
                    <button type="button" class="btn prev" onclick="showStep(2)">Back</button>
                    <button type="submit" class="btn confirm" name="register">Register Now</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
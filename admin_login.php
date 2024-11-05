<?php
session_start();
require 'dbconnection.php'; // Database connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch the admin user from the database
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch();

    // Verify the password directly without hashing
    if ($admin && $password === $admin['password']) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php'); // Redirect to admin panel
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
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

        .login-container {
            border: black ;
            background-color: white;
            border-radius: 15px;
            width: 600px;
            height: 400px;
            margin: 150px auto;
            box-shadow: 0px 4px 200px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid #ccc;
        }

        .login-content {
            text-align: center;
            width: 100%;
        }

        /* Style for email and password inputs */
        .form-input {
            border-radius: 5px;
            height: 40px;
            width: 90%;
            outline: none; /* Removes the input field outline */
            border: 1px solid #ccc; /* Optionally, add a subtle border */
            padding: 0 10px; /* Adds some padding for better UX */
        }

        .form-input::placeholder {
            font-family: 'Rubik', sans-serif;
            font-size: 16px; /* Adjust placeholder font size if needed */
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            margin-bottom: 30px;
        }

        .login-content button {
            margin-bottom: 15px;
            background-color: #ffdb44;
            color: black;
            font-weight: bold;
            font-size: 16px; /* Adjust font size */
            height: 40px;
            width: 93.5%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #e6c84e;
        }

        /* Style the links to match the button color */
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

        #error-message {
            display: none;
            background-color: #ff9999;
            color: black;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 25px;
            margin: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 90%;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-container">
            <div class="login-content">
                <h1>Admin Login</h1>
                <?php if (!empty($error)): ?>
                    <div id="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="email" name="email" class="form-input" required placeholder="E-mail Address"><br>
                    <input type="password" name="password" id="password" class="form-input" required placeholder="Password"><br>
                    <label><input type="checkbox" onclick="togglePasswordVisibility()"> Show Password</label><br>
                    <button type="submit">Login Now</button>
                </form>
            </div>
        </div>
    </div>
        <script>
        // Function to toggle the password visibility
        function togglePasswordVisibility() {
            const passField = document.getElementById('password');
            passField.type = passField.type === 'password' ? 'text' : 'password';
        }

        // If the error message exists, display it
        const errorMessage = "<?php echo $error; ?>";
        if (errorMessage) {
            document.getElementById('error-message').style.display = 'block';
        }
    </script>
</body>
</html>

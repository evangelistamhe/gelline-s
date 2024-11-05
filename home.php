<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Database connection
$connection = new mysqli('localhost', 'root', '', 'gellines_restaurant');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch user data based on the session user_id
$user_id = $_SESSION['user_id'];
$query = "SELECT first_name FROM users WHERE user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if a user was found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $user = ['first_name' => 'Guest']; // Fallback for guest
}

$stmt->close();
$connection->close();

// Display warning message if set
if (!isset($_SESSION['warning_shown'])) {
    echo "<script>alert('Gelline\'s Restaurant delivers only in Santa Maria, Bulacan.');</script>";
    $_SESSION['warning_shown'] = true; // Prevent the message from showing again
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="style/home.css">
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
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
          .myorders-btn,
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
          .myorders-btn:hover,
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
              background-color: rgba(0, 0, 0, 0.1);
          }

          .dropdown:hover .dropdown-content {
              display: block;
          }
          
           /* Ensure no margin between welcome and gallery */
          .welcome-container {
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
              background-color: white;
              width: 90%;
              margin: 130px auto 5px; /* Remove bottom margin */
              padding: 0px 0; /* Optional padding */
              border: 1px solid #ccc;
              border-radius: 10px;
          }

          .welcome-content {
              text-align: center;
          }

          .gallery-container {
              text-align: center;
              background-color: white;
              width: 90%;
              margin: 0 auto; /* Directly under the welcome container */
              padding: 0px; /* No padding needed */
          }

          .gallery-grid {
              display: flex;
              justify-content: center;
              gap: 25px;
              flex-wrap: wrap;
          }

          .gallery-grid img {
              width: 230px;
              height: auto;
              border-radius: 8px;
              box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
              transition: transform 0.3s ease;
          }

          .gallery-grid img:hover {
              transform: scale(1.1);
          }

          .intro-button {
            margin: 10px;
          }

          .intro-container {
            width: 750px;
            height: auto;
            padding: 20px;
            border-radius: 10px;
            color: white;
            position: absolute;
            bottom: 70px; /* Adjust distance from the bottom */
            left: 90px;   /* Adjust distance from the left */
            z-index: 3;
          }

          .intro-container h2 {
            margin-left: 8px;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: -3px;
            color: black;
            letter-spacing: 2px;
            
          }

          .intro-container p {
            padding-left: 7px;
            font-size: 14px;
            line-height: 1.5;
            color: black;
            letter-spacing: 1px;
            
          }

          .feature-container {
            display: flex;
            gap: 10px; /* Adjust the space between the buttons */
            position: absolute;
            bottom: 45px;
            left: 115px;
            z-index: 2;
          }

          .feature-btn {
            background-color: #ffdb44;
            
            color: black;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 10px;
            width: 150px;
            z-index: 2;
            font-family: 'Rubik', sans-serif; /* Apply Rubik font */
          }

          .feature-btn:hover {
            background-color: #e2c43b;
          }

          .text {
            font-size: 18px;
            color: black;
            opacity: 1;
          }
                  /* Add spacing between the icons in the header */
.image-button {
    display: inline-block;
    border: none;
    padding: 0;
    background: none;
    cursor: pointer;
    margin-left: 20px; /* Adjust this value to add space between icons */
}

.image-button img {
    width: 30px;
    height: auto;
}

/* Dropdown CSS for Profile */
.profile-dropdown {
    position: relative;
    display: inline-block;
    margin-right: 20px;
}

.profile-dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

.profile-dropdown-content a {
    font-family: 'Rubik', sans-serif;
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.profile-dropdown-content a:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

.profile-dropdown:hover .profile-dropdown-content {
    display: block;
}

          footer {
            background-color: black;
            color: white;
            padding: 15px;
            bottom: 0;
            width:  100%;
            box-sizing: border-box; /* Ensures padding doesn't cause overflow */
          }
          footer .footer-content {
            font-size: 13px;
              padding-left: 50px; /* Adjust this value to increase the left padding */
          }
      </style>
</head>
<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="home-btn" onclick="window.location.href = 'home.php';">Home</button>
            <button class="ordr-btn" onclick="window.location.href = 'about.php';">About Us</button>
          

            <div class="dropdown">
                <button class="ordr-btn">Menus</button>
                <div class="dropdown-content">
                    <a href="menu.php">Food Menu</a>
                </div>
            </div>
   
            <div class="dropdown profile-dropdown">
                <button class="image-button">
                    <img src="img/user.png" alt="Profile Icon">
                </button>
                <div class="dropdown-content profile-dropdown-content">
                    <a href="my_orders.php">Orders</a>
                    <a href="my_reservations.php">Reservations</a>
                    <a href="order_history.php">Order History</a>
                    <a href="reservation_history.php">Reservation History</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

           
            <a class="image-button" href="cart.php">
                <img src="img/cart.png" alt="Button">
            </a>
        </div>
    </header>

    <div class="welcome-container">
        <div class="welcome-content">
            <center>
                <h3>Hello <u><?php echo htmlspecialchars($user['first_name']); ?></u>, Welcome to Gelline's Sizzling and Restaurant</h3>
            </center>
        </div>
    </div>
    <center><h1>Popular Dishes</h1></center>
    <div class="gallery-container">
        <div class="gallery-grid">
            <img src="img/menu/kawali.jpg" alt="Dish 1">
            <img src="img/menu/liempo.jpg" alt="Dish 2">
            <img src="img/menu/pata.jpg" alt="Dish 3">
            <img src="img/menu/bulaklak.jpg" alt="Dish 4">
        </div>
    </div>

    <div class="intro-button">
        <div class="intro-container">
            <h2>Treat Yourself to Sizzling Flavors</h2>
            <p>Sizzle Your Senses, Taste the Freshness! 🍴🔥 Discover the Freshest Local Ingredients in Every Bite—Order or Reserve Now!</p>
        </div>

        <div class="feature-container">
            <button class="feature-btn" onclick="window.location.href = 'menu.php';">Go to Menu</button>
            <button class="feature-btn" onclick="window.location.href = 'reservations.php';">Go to Reservation</button>
        </div>
    </div>
     <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <footer>
        <div class="footer-content">
            <h2>Contact Us</h2>
            <p>Do you have any questions, or would you like to make a reservation? Feel free to contact us at:</p>
            <ul>
                <li>Email: gelssizzlingresto@gmail.com</li>
                      <li>Phone: +63 923 653 3181</li>
                      <li>Address: Dulong bayan, Santa Maria, Philippines</li>
            </ul>
            <p>We look forward to serving you at Gelline's Sizzling and Restaurant!</p>
        </div>
    </footer>
</body>
</html>

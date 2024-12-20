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
            z-index: 4;
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
              background-color: white;
              width: 90%;
              margin: 130px auto 50px; /* Remove bottom margin */
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
              margin: 150px auto; /* Directly under the welcome container */
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
            margin-bottom: 0px;
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
            font-weight: bold;
            color: black;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            
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
<script>
        // Pass login status from PHP to JavaScript
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        // Function to check login status and alert the user
        function checkLoginForReservation(event) {
            if (!isLoggedIn) {
                event.preventDefault(); // Prevent the button action
                alert('Please log in first to reserve a table.');
                // Optionally, you could redirect to the login page here
                // window.location.href = 'login.php';
            }
        }
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
            <button class="ordr-btn" onclick="window.location.href = 'login.php';">Sign In</button>
        </div>
    </header>

    <div class="gallery-container">
        <center><h1>Popular Dishes</h1></center>
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
            <button class="feature-btn" onclick="window.location.href = 'menu2.php';">Go to Menu</button>
            <!-- Attach the checkLoginForReservation function to the "Reserve Now" button -->
            <button class="feature-btn" onclick="checkLoginForReservation(event)">Go to Reservation</button>
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
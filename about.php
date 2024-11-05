<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Gelline's Sizzling and Restaurant</title>
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
              background-color: rgba(0, 0, 0, 0.1);
          }

          .dropdown:hover .dropdown-content {
              display: block;
          }

          .about-container {
          margin-top: 110px auto;
          width: 100%;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: flex-start;
          padding: 20px;
          gap: 50px; /* Gap between text and image sections */
          z-index: 1;
          margin-left: -50px;
        }

        /* About text container */
        .con-container-content {
          margin-top: 80px;
          font-family: 'Rubik', sans-serif;
          font-weight: 510;
          width: 50%;
          height: auto;
          text-align: justify;
          z-index: 1;
        }

        .con-container-content img {
          width: 50%;
          margin: 0 auto;
          display: block;
        }

        .con-container-content p {
            font-size: 16px;
            color: black;
            line-height: 1.5;
            letter-spacing: 1px;
            padding: 20px; /* Add padding around the text */
            border-radius: 10px; /* Rounded corners */
            max-width: 700px; /* Optional: Limit the width of the paragraph */
            margin: 0 auto; /* Center the text block */
        }
        .coon-container-content {
          margin-top: 10px;
          font-family: 'Rubik', sans-serif;
          font-weight: 510;
          width: 70%;
          height: auto;
          text-align: justify;
          z-index: 1;
          margin-left: 180px;
        }
        .coon-container-content h2, p {
            font-size: 16px;
            color: black;
            line-height: 1.5;
            letter-spacing: 1px;
            padding-left: 20px; /* Add padding around the text */
            border-radius: 10px; /* Rounded corners */
             /* Optional: Limit the width of the paragraph */
            margin: 10px auto; /* Center the text block */
        }


        /* Image container */
        .img-container {
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          gap: 20px;
          margin-top: 120px; /* Move the image container down */
        }

        .img-container img {
          width: 300px;
          height: auto;
          border-radius: 10px;
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
          .footer-content p{
            font-size: 13px;
            color: white;
          }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
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

    <div class="about-container">
    <div class="con-container-content">
      <img src="img/gellineslogo.png" alt="gellineslogo">
      <p>Gelline's Sizzling and Restaurant, situated in the heart of Santa Maria, Bulacan, 
        is a beloved culinary destination founded by Geraldine, a master in the kitchen. 
        It has become a central hub for local dining, captivating guests with delicious food and 
        a welcoming atmosphere. Geraldine envisioned Gelline's as a place where people could enjoy 
        Filipino cuisine and feel a sense of belonging. The restaurant has stayed true to its 
        commitment to excellence, creativity, and hospitality.
      </p>
    </div>

    <div class="img-container">
      <img src="img/pic1.jpg" alt="gellinespic">
      <img src="img/pic2.jpg" alt="gellinespic1">
    </div>
  </div><br>
  <div class="coon-container-content">
    <h2>Our Specialties</h2>
    <p>We are known for our wide range of sizzling plates, from steaks to seafood, all served hot and fresh. Our signature dish, the Sizzling Sisig, has been a favorite for years. We also cater to special dietary needs, offering vegetarian and gluten-free options.</p>
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

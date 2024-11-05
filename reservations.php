<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Us - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Rubik', sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
            color: #333;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
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

        /* Section Title */
        h1 {
            margin-top: 120px;
            font-size: 48px;
            font-weight: bold;
            text-align: left;
            text-indent: 5%;
            margin-bottom: 30px;
            background-color: #ffdb44;
            color: black;
            padding: 20px;
        }

        /* Info Sections (Dine In and FICO To-Go) */
        .info-sections {
            display: flex;
            justify-content: space-between;
            padding-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
        }
        .info-sections img {
            max-width: 400px;
        }

        .info-sections div {
            width: 45%;
        }

        .info-sections h3 {
            margin-top: 100px;
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .info-sections p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
        }

        .info-sections strong {
            font-weight: bold;
        }

        /* Buttons for Reservation and FAQ */
        .button-section {
            text-align: center;
            margin-top: 40px;
        }

        .button-section button {
            padding: 12px 30px;
            background-color: transparent;
            border: 2px solid #333;
            font-size: 18px;
            cursor: pointer;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }

        .button-section button:hover {
            background-color: #ffdb44;
            color: #fff;
        }

        /* Map and Contact Info */
        .map-contact-sections {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .map-section {
            width: 55%;
        }

        .contact-section {
            width: 40%;
        }

        /* Map Placeholder */
        .map-placeholder img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        /* Contact and Address */
        .contact-section h3 {
            font-size: 24px;
            color: black;

        }

        .contact-section p {
            font-size: 18px;
            color: #666;
            line-height: 1.8;
        }

        .contact-section a {
            color: #a89c53;
            text-decoration: none;
            font-size: 18px;
        }

        .contact-section a:hover {
            text-decoration: underline;
        }

        

        /* Footer Links */
        footer {
            padding: 40px 0;
            text-align: center;
            font-size: 16px;
            color: #666;
            background-color: #f9f9f9;
        }

        footer a {
            color: #a89c53;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="home-container">
            <button class="home-btn" onclick="window.location.href = 'home.php';">Home</button>
            
            <button class="ordr-btn" onclick="window.location.href = 'login.php';">Logout</button>
        </div>
    </header>
<h1>Visit Us</h1>
    <div class="container">
        <!-- Title Section -->
        

        <!-- Info Sections (Dine In and FICO To-Go) -->
        <div class="info-sections">
            <div><img src="img/gellineslogo.png" alt="gellineslogo"></div>
            <div>
                <h3>DINE IN</h3>
                <p><strong>BY RESERVATIONS ONLY</strong></p>
                <p>Mon–Wed: 8am–10pm (Last order 10pm)</p>
                <p>Thu–Fri: 8am–10pm (Last order 10pm)</p>
                <p>Thu–Sun: 8m–10pm (Last order 10pm)</p>
                <p>Sat–Sun: 8am–10pm (Last order 10pm)</p>
            </div>

            <div>
                <h3>GELLINES DELIVERY</h3>
                <p>Mon–Wed: 10am-10pm</p>
                <p>Thu–Sun: 11am–10pm</p>
            </div>
        </div>

        <!-- Reserve a Table and FAQ Buttons -->
        <div class="button-section">
            <button onclick="window.location.href='reserve.php'">RESERVE A TABLE</button>
            <button onclick="window.location.href='faq.php'">FAQ</button>
        </div>

        <!-- Map and Contact Information -->
<div class="map-contact-sections">
    <div class="map-section">
        <!-- Replacing the placeholder with an actual image -->
        <img src="img/loc.png" alt="Location Map" style="width:100%; height:auto; border-radius: 8px;">
    </div>

            <div class="contact-section">
                <h3>CONTACT</h3>
                <p>(63) 923 653 3181</p>
                <p>Line operating hours:<br>Weekdays: 8amm–11.30pm<br>Weekends: 8am–12am</p>

                <h3>ADDRESS</h3>
                <p>RXF9+952, Norzagaray Road, Guyong, Santa Maria, 3022 Bulacan</p>
            </div>
        </div>
    </div>
    <br>
    <br>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Gelline's Sizzling and Restaurant. All rights reserved.</p>
    </footer>

</body>
</html>

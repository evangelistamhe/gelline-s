<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
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

        img {
            max-width: 200px;
            max-height: 200px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }

        header a {
            color: #000; /* Black text for links */
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
        }

        /* Logo */
        .logo {
            font-size: 36px;
            font-weight: bold;
            color: #000; /* Black logo text */
        }

        /* FAQ Title */
        .faq-title {
            background-color: #ffdb44;
            font-size: 72px;
            font-weight: bold;
            margin-top: 10px;
            color: #000; /* Black title text */
            text-align: center;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        /* FAQ Sections */
        .faq-sections {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .faq-section {
            width: 45%;
            text-align: left;
        }

        .faq-section h3 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000; /* Black section headers */
        }

        .faq-section details {
            margin-bottom: 15px;
        }

        .faq-section summary {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
            color: #000; /* Black text for summary */
            list-style: none;
        }

        .faq-section p {
            color: #000; /* Black text for paragraphs */
            font-size: 16px;
            line-height: 1.6;
        }

        /* Reservation and Social Media Buttons */
        .header-buttons {
            display: flex;
            align-items: center;
        }

        .header-buttons a {
            color: #000; /* Black text */
            font-size: 18px;
            font-weight: bold;
            padding: 12px 24px;
            border: 2px solid black;
            border-radius: 6px;
            margin-left: 15px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .header-buttons a:hover {
            background-color: #ffdb44;
            color: white; /* Yellow background and black text on hover */
        }

        /* Bottom Pattern (similar to the image) */
        .bottom-pattern {
            background-image: url('img/pattern.png'); /* Use your pattern image */
            height: 80px;
            background-repeat: repeat-x;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <header>
        <div><img src="img/gellineslogoo.png" alt="gellineslogo"></div>
       <nav class="header-buttons">
            <a href="reservations.php">Reservations</a>
        </nav>
    </header>
<h1 class="faq-title">FAQs</h1>
    <div class="container">
        

        <!-- FAQ Sections -->
        <div class="faq-sections">
            <!-- Left Section -->
            <div class="faq-section">
                <h3>GENERAL</h3>
                <details>
                    <summary>Are you a pet-friendly establishment?</summary>
                    <p>Yes, we welcome pets in our outdoor dining areas.</p>
                </details>
                <details>
                    <summary>Where is the car park nearest to the restaurant?</summary>
                    <p>Parking is available at East Coast Park Carpark F2.</p>
                </details>
                <details>
                    <summary>Can I bring my own cake?</summary>
                    <p>Yes, you are welcome to bring your own cake for special occasions.</p>
                </details>
                <details>
                    <summary>Can I bring my own wine?</summary>
                    <p>Yes, we allow BYO wine with a corkage fee.</p>
                </details>
                <details>
                    <summary>Do you have sharing menus for large groups?</summary>
                    <p>Yes, we offer sharing menus for parties of 8 or more.</p>
                </details>
                <details>
                    <summary>Can you accommodate allergies?</summary>
                    <p>Please let us know of any allergies, and we will do our best to accommodate.</p>
                </details>
            </div>

            <!-- Right Section -->
            <div class="faq-section">
                <h3>RESERVATION</h3>
                <details>
                    <summary>Do you accept walk-ins?</summary>
                    <p>Reservations are preferred, but walk-ins are accepted depending on availability.</p>
                </details>
                <details>
                    <summary>What is your cancellation policy?</summary>
                    <p>Cancellations must be made 24 hours in advance.</p>
                </details>
                <details>
                    <summary>Do you accommodate big groups?</summary>
                    <p>Yes, we can accommodate large groups with advanced notice.</p>
                </details>
                <details>
                    <summary>How frequently does the menu change?</summary>
                    <p>Our menu changes seasonally and we introduce new dishes frequently.</p>
                </details>
            </div>
        </div>

        <!-- Bottom Pattern -->
        <div class="bottom-pattern"></div>
    </div>

</body>
</html>

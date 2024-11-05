<?php
session_start(); // Start the session to access user_id
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit;
}

$user_id = $_SESSION['user_id']; // Fetch user_id from session

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Your Table</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;

            background-size: cover;
            background-position: center;

            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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

        .reservation-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow:  0 0 15px rgba(0, 0, 0, 0.3);
        }

        .logo {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
            color: white;
        }

        .info-text {
            font-size: 14px;
            margin-bottom: 20px;
            color: white;
        }

        .reservation-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }
        .reservation-container input {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }

        .search-btn {
            background-color: #a89c53;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .reservation-container input,
        .reservation-container select {
            text-align: center;
            font-family: 'Rubik', sans-serif;
        }
        .reservation-container input::placeholder,
        .reservation-container select {
            text-align: center;
            font-family: 'Rubik', sans-serif;
        }
        .search-btn{
            background-color: #ffdb44;
            color: black;
            font-family: 'Rubik', sans-serif;

        }
        .search-btn:hover {

            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .time-selection-container {
            text-align: center;
            width: 300px;
        }

        .time-selection-container h2 {
            font-size: 18px;
            font-weight: normal;
            margin-bottom: 20px;
        }

        .time-slot {
            display: block;
            background-color: #a89c53; /* Gold background for selected style */
            color: white;
            padding: 15px 0;
            margin: 10px 0;
            cursor: pointer;
            font-size: 18px;
            border: 1px solid transparent;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .time-slot:hover {
            background-color: #ffffff;
            color: #a89c53; /* Change to gold when hovered */
            border: 1px solid #a89c53;
        }

        .time-slot.selected {
            background-color: #a89c53;
            color: white;
            border: 1px solid #a89c53;
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

        hr {
            width: 100%;
            max-width: 500px;
        }

    </style>
</head>

<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="home-btn" onclick="window.location.href = 'reservations.php';">Reservations</button>
            
            <button class="ordr-btn" onclick="window.location.href = 'login.php';">Logout</button>
        </div>
    </header>
    <div class="reservation-container">
        <h3>Select Date & Time</h3>
        <!-- Single form (removed nested form issue) -->
        <form action="tables.php" method="POST">
            
            <!-- Hidden User ID Field -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

            <!-- Date Picker -->
            <input type="text" id="date-picker" name="reservation_date" placeholder="YYYY-MM-DD" required>
            <hr>
            <!-- Guest Count -->
            <select name="guests" id="guest-count" required>
                <option value="" disabled selected>Select Guests</option>
                <option value="2">2 Guests</option>
                <option value="4">4 Guests</option>
                <option value="5">5 Guests</option>
                <option value="6">6 Guests</option>
                <option value="8">8 Guests</option>
            </select>
            <hr>
            <!-- Time Dropdown -->
            <select name="time" id="time-select" required>
                <option value="" disabled selected>Select Time</option>
                <option value="08:00">08:00</option>
                <option value="10:00">10:00</option>
                <option value="12:00">12:00</option>
                <option value="14:00">14:00</option>
                <option value="16:00">16:00</option>
                <option value="18:00">18:00</option>
                <option value="20:00">20:00</option>
                <option value="22:00">22:00</option>
            </select>
            <hr>
            <br>
            <!-- Submit Button -->
            <button type="submit" class="search-btn">Search Availability</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Date Picker Initialization
        flatpickr("#date-picker", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                let today = new Date();
                let selectedDate = new Date(dateStr);

                // Disable past time slots if today's date is selected
                let timeSelect = document.getElementById('time-select');
                let options = timeSelect.options;

                if (selectedDate.toDateString() === today.toDateString()) {
                    let currentHour = today.getHours();

                    for (let i = 0; i < options.length; i++) {
                        let timeValue = parseInt(options[i].value.split(":")[0]);

                        // Disable time slots that have already passed
                        if (timeValue <= currentHour) {
                            options[i].disabled = true;
                        } else {
                            options[i].disabled = false;
                        }
                    }
                } else {
                    // Enable all time slots for future dates
                    for (let i = 0; i < options.length; i++) {
                        options[i].disabled = false;
                    }
                }
            }
        });

        
    </script>
</body>

</html>

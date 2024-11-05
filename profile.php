<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is established

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];



// Update user profile information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $mobile_number = trim($_POST['mobile_number']);

    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, mobile_number = ? WHERE user_id = ?");
    $stmt->execute([$first_name, $last_name, $mobile_number, $user_id]);

    header('Location: profile.php?message=profile_updated');
    exit;
}

// Update user email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
    $stmt->execute([$email, $user_id]);

    header('Location: profile.php?message=email_updated');
    exit;
}

// Change user password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Fetch current password from the database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the current password
    if (password_verify($current_password, $user['password'])) {
        // Update to the new password
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$new_password_hashed, $user_id]);

        header('Location: profile.php?message=password_updated');
        exit;
    } else {
        header('Location: profile.php?message=password_incorrect');
        exit;
    }
}

// Update GCash number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_gcash'])) {
    $gcash_number = trim($_POST['gcash_number']);

    $stmt = $pdo->prepare("UPDATE users SET gcash_number = ? WHERE user_id = ?");
    $stmt->execute([$gcash_number, $user_id]);

    header('Location: profile.php?message=gcash_updated');
    exit;
}

// Handle adding a new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $user_street = trim($_POST['user_street']);
    $user_brgy = trim($_POST['user_brgy']);
    $user_city = trim($_POST['user_city']);

    if (!empty($user_street) && !empty($user_brgy) && !empty($user_city)) {
        $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, user_street, user_brgy, user_city) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $user_street, $user_brgy, $user_city])) {
            header('Location: profile.php?message=address_added');
            exit;
        } else {
            echo "<p style='color: red;'>Failed to add address. Please try again.</p>";
        }
    } else {
        echo "<p style='color: red;'>All fields are required.</p>";
    }
}

// Handle address deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_address_id'])) {
    $address_id = $_POST['delete_address_id'];
    $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE address_id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);

    header('Location: profile.php?message=address_deleted');
    exit;
}

// Fetch user information (without address fields)
$stmt = $pdo->prepare("SELECT first_name, last_name, email, mobile_number, gcash_number FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all addresses from the `user_addresses` table
$stmt = $pdo->prepare("SELECT address_id, user_brgy, user_street, user_city FROM user_addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch orders with grouped dishes for the user
$query = "
    SELECT o.order_id, o.order_status, o.total_amount, o.created_at AS order_date,
           GROUP_CONCAT(CONCAT(oi.dish_name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS dishes
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    AND o.order_status IN ('Delivered', 'Cancelled') -- Only history orders
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style/profile.css">
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

        /* Container for the input field and the Show Password button */
        .password-container {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .password-container input {
            width: 100%;
            padding-right: 25px; /* Space for the smaller Show Password button */
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            padding: 5px 10px;
            background-color: #f0c23b; /* Yellow color */
            color: black;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
        }

        .toggle-password:hover {
            background-color: #e0ae35;
        }

        .toggle-password:focus {
            outline: none;
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
            z-index: 102;
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
            text-shadow: none;
            letter-spacing: 2px;
        }

        .home-btn:hover,
        .ordr-btn:hover,
        .lout-btn:hover,
        .image-button:hover {
            background-color: rgba(0, 0, 0, 0.1);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.4);
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
            margin-left: 20px; /* Add space between icons */
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

        .your-profile {
            max-width: 550px;
            margin: 140px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .your-profile h1,
        .your-profile h2 {
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #333;
        }

        .your-profile label {
            font-size: 14px;
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .your-profile input[type="text"],
        .your-profile input[type="email"],
        .your-profile input[type="password"],
        .your-profile textarea {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-sizing: border-box;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .your-profile input:focus,
        .your-profile textarea:focus {
            outline: none;
            border-color: #666;
            background-color: #fff;
        }

        .your-profile button {
            width: 100%;
            padding: 12px 0;
            background-color: #ffdb44;
            color: black;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;

        }

        .your-profile button:hover {
            background-color: #e2c43b;
        }

        .your-profile table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .your-profile table th,
        .your-profile table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .your-profile table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .your-profile ul {
            list-style: none;
            padding: 0;
        }

        .your-profile ul li {
            margin-bottom: 10px;
        }

        .your-profile ul li form {
            display: inline-block;
            margin-left: 10px;
        }

        footer {
            background-color: black;
            color: white;
            padding: 15px;
            bottom: 0;
            width: 100%;
            box-sizing: border-box;
        }

        footer .footer-content {
            font-size: 13px;
            padding-left: 50px;
        }

        /* Modal styling */
        /* Full-screen overlay for background (semi-transparent black) */
        .modal-overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.2); /* Semi-transparent black background */
            z-index: 99; /* Sits below the modal but above other content */
        }

        /* Modal content with floating effect */
        #addAddressModal,
        #viewAddressesModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 100; /* Above the overlay */
            width: 700px;
            border: 1px solid #ddd;
            border-radius: 10px;
            animation: fadeIn 0.3s ease; /* Optional fade-in animation */
        }

        /* Fade-in animation for the modal */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%); /* Start slightly above */
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%); /* End at centered position */
            }
        }


        /* Address Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        #viewAddressesModal button {
            margin-left: 5px;
            width: 32%;
            padding: 12px;
            background-color: #ffdb44;
            color: black;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        #addAddressModal button {
            margin-left: 3px;
            width: 49%;
            padding: 12px;
            background-color: #ffdb44;
            color: black;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .your-profile button {
            margin-top: 0px;
            width: 100%;
            padding: 12px;
            background-color: #ffdb44;
            color: black;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .your-profile button:hover,
        #addAddressModal button:hover,
        #viewAddressesModal button:hover {
            background-color: #e2c43b;
        }
        
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

        .button-modal {
            text-align: center; /* Center-align the buttons inside the parent */
        }

        .button-modal button {
            display: inline-block;
            margin: 10px 5px; /* Add some margin between buttons */
            width: 47.8%; /* Prevent buttons from taking the full width */
            padding: 10px 20px;
            background-color: #ffdb44;
            color: black;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button-modal button:hover {
            background-color: #e2c43b;
        }
        .order-container {
            margin-bottom: 30px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #ffdb44;
            color: black;
        }

        .order-status {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .order-total {
            font-weight: bold;
        }
            label input[type="checkbox"] {
            accent-color: #ffdb44; /* For supported browsers */
            cursor: pointer;
        }
</style>
    <script>
// Validation function for profile form
function validateProfileForm() {
    var firstName = document.getElementsByName('first_name')[0].value.trim();
    var lastName = document.getElementsByName('last_name')[0].value.trim();
    var mobileNumber = document.getElementsByName('mobile_number')[0].value.trim();

    // Check if any fields are empty
    if (firstName === '' || lastName === '' || mobileNumber === '') {
        alert('Please fill out all profile fields.');
        return false;
    }

    // Check if first name and last name are different
    if (firstName.toLowerCase() === lastName.toLowerCase()) {
        alert('First name and last name cannot be the same.');
        return false;
    }

    // Check if mobile number is exactly 11 digits
    if (!/^\d{11}$/.test(mobileNumber)) {
        alert('Mobile number must be exactly 11 digits.');
        return false;
    }

    // Ask for confirmation before submitting
    var confirmDetails = confirm("Are you sure you want to save the changes?");
    if (!confirmDetails) {
        // Cancel the form submission if the user selects 'Cancel'
        return false;
    }

    return true; // Proceed with form submission if validation passes
}

function togglePasswordVisibility() {
        // Get the password fields
        const currentPasswordField = document.getElementById('current_password');
        const newPasswordField = document.getElementById('new_password');

        // Toggle the type attribute
        if (currentPasswordField.type === "password") {
            currentPasswordField.type = "text";
            newPasswordField.type = "text";
        } else {
            currentPasswordField.type = "password";
            newPasswordField.type = "password";
        }
    }



        // Function to show the Add Address modal and hide the View Addresses modal if active
function showAddAddressModal() {
    document.getElementById('addAddressModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block'; // Show overlay
}

// Show the View Addresses modal and overlay
function showViewAddressesModal() {
    document.getElementById('viewAddressesModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block'; // Show overlay
}

// Close the Add Address modal and overlay
function closeAddAddressModal() {
    document.getElementById('addAddressModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none'; // Hide overlay
}

// Close the View Addresses modal and overlay
function closeViewAddressesModal() {
    document.getElementById('viewAddressesModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none'; // Hide overlay
}
window.onclick = function(event) {
    const modalOverlay = document.getElementById('modalOverlay');
    const addAddressModal = document.getElementById('addAddressModal');
    const viewAddressesModal = document.getElementById('viewAddressesModal');
    if (event.target === modalOverlay) {
        addAddressModal.style.display = 'none';
        viewAddressesModal.style.display = 'none';
        modalOverlay.style.display = 'none';
    }
};


        // Select all checkboxes
        function selectAllAddresses() {
            var checkboxes = document.querySelectorAll('.address-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }

        // Unselect all checkboxes
        function deselectAllAddresses() {
            var checkboxes = document.querySelectorAll('.address-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }

        // Handle delete selected addresses
        function deleteSelectedAddresses() {
            var selectedIds = [];
            var checkboxes = document.querySelectorAll('.address-checkbox');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedIds.push(checkbox.value);
                }
            });

            if (selectedIds.length > 0) {
                if (confirm('Are you sure you want to delete the selected addresses?')) {
                    var form = document.getElementById('deleteAddressesForm');
                    document.getElementById('selectedIds').value = selectedIds.join(',');
                    form.submit();
                }
            } else {
                alert('Please select at least one address to delete.');
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
<div class="your-profile">

        <?php if (isset($_GET['message'])): ?>
    <p id="notification-message" style="background-color: #c8f7c5; /* Light green for all messages */
       color: black; padding: 10px; border-radius: 5px; text-align: center;">
        <?php
        if ($_GET['message'] === 'profile_updated') {
            echo 'Profile updated successfully!';
        } elseif ($_GET['message'] === 'email_updated') {
            echo 'Email updated successfully!';
        } elseif ($_GET['message'] === 'password_updated') {
            echo 'Password changed successfully!';
        } elseif ($_GET['message'] === 'password_incorrect') {
            echo 'Incorrect current password!';
        } elseif ($_GET['message'] === 'gcash_updated') {
            echo 'GCash number updated successfully!';
        } elseif ($_GET['message'] === 'address_deleted') {
            echo 'Address deleted successfully!';
        } elseif ($_GET['message'] === 'address_added') {
            echo 'Address added successfully!';
        }
        ?>
    </p>

    <script>
        // Auto-hide the message after 1 second (1000 milliseconds)
        setTimeout(function() {
            var notificationMessage = document.getElementById('notification-message');
            if (notificationMessage) {
                notificationMessage.style.display = 'none';
            }
        }, 1000);
    </script>
<?php endif; ?>



        
        <h2>My Profile</h2>
        <form method="post" action="" onsubmit="return validateProfileForm();">
            <label>First Name:</label><br>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required><br>

            <label>Mobile Number:</label><br>
            <input type="text" name="mobile_number" value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required><br>

            <button type="submit" name="update_profile">Save Profile</button>
        </form>

        <h2>Email</h2>
        <form method="post" action="">
            <label>Email:</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly><br>
        </form>

      <h2>Password</h2>
<form method="post" action="change_password_process.php">
    <div class="input-container">
        <label>Current Password:</label><br>
        <div class="password-container">
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <label>New Password:</label><br>
        <div class="password-container">
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <label><input type="checkbox" onclick="togglePasswordVisibility()"> Show Password</label><br>
    </div>
    <button type="submit" name="change_password">Change Password</button>
</form>




        <h2>My Payments</h2>
        <form method="post" action="">
            <label>GCash Number:</label><br>
            <input type="text" name="gcash_number" value="<?php echo htmlspecialchars($user['gcash_number'] ?? ''); ?>" required><br>

            <button type="submit" name="update_gcash">Save Number</button>
        </form>

       


        <h2>Delivery Addresses</h2>
        <!-- Buttons for "Add New Address" and "View My Addresses" -->
        <div class="button-modal">
        <button onclick="showViewAddressesModal()">View Address</button>
        <button onclick="showAddAddressModal()">Add Address</button>
        </div>
        <div class="modal-overlay" id="modalOverlay"></div>
        <!-- Modal for adding a new address -->
        <div id="addAddressModal">
            <div>
                <h2>Add New Address</h2>
                <form method="post" action="update_address.php">
                    <label>Street:</label><br>
                    <input type="text" name="user_street" required><br>

                    <label>Barangay:</label><br>
                    <input type="text" name="user_brgy" required><br>

                    <label>City:</label><br>
                    <input type="text" name="user_city" required><br>

                    
                    <button type="button" onclick="closeAddAddressModal()">Cancel</button>
                    <button type="submit">Add Address</button>
                </form>
            </div>
        </div>

        <!-- Modal for viewing and deleting addresses -->
        <div id="viewAddressesModal">
            <div>
                <h2>My Addresses</h2>
                <form id="deleteAddressesForm" method="post" action="delete_addresses.php">
                    <input type="hidden" name="selectedIds" id="selectedIds">
                    <table>
                        <tr>
                            <th>Select</th>
                            <th>Street</th>
                            <th>Barangay</th>
                            <th>City</th>
                        </tr>
                        <?php foreach ($addresses as $address): ?>
                            <tr>
                                <td><input type="checkbox" class="address-checkbox" value="<?php echo $address['address_id']; ?>"></td>
                                
                                <td><?php echo htmlspecialchars($address['user_street']); ?></td>
                                <td><?php echo htmlspecialchars($address['user_brgy']); ?></td>
                                <td><?php echo htmlspecialchars($address['user_city']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <!-- Buttons to select all or delete selected addresses -->
                    <button type="button" onclick="closeViewAddressesModal()">Close</button>
                    <button type="button" onclick="deleteSelectedAddresses()">Delete Selected</button>
                    <button type="button" onclick="selectAllAddresses()">Select All</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Hide modal when clicking outside of it
        window.onclick = function(event) {
            var addAddressModal = document.getElementById('addAddressModal');
            var viewAddressesModal = document.getElementById('viewAddressesModal');
            if (event.target == addAddressModal) {
                hideAddAddressModal();
            }
            if (event.target == viewAddressesModal) {
                hideViewAddressesModal();
            }
        }
    </script>

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

<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is established

// Initialize error and success messages
$error_message = '';
$success_message = '';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's saved addresses from the user_addresses table
$stmt = $pdo->prepare("SELECT address_id, user_brgy, user_street, user_city FROM user_addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's personal details
$stmt = $pdo->prepare("SELECT first_name, last_name, email, mobile_number, gcash_number FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch items from the user's cart
$stmt = $pdo->prepare("
    SELECT ci.cart_item_id, m.menu_id, m.dish_name, ci.quantity, m.price, (ci.quantity * m.price) AS total_price, ci.special_instructions
    FROM cart_items ci
    JOIN menu m ON ci.menu_id = m.menu_id
    WHERE ci.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate the total amount from the cart items
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['total_price'];
}

// Handle adding a new address separately from placing an order
if (isset($_POST['add_address'])) {
    $user_brgy = trim($_POST['user_brgy']);
    $user_street = trim($_POST['user_street']);
    $user_city = trim($_POST['user_city']);

    if (empty($user_brgy) || empty($user_street) || empty($user_city)) {
        $error_message = 'Please fill in all fields for the new address.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, user_brgy, user_street, user_city) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $user_brgy, $user_street, $user_city]);
            $success_message = "Address added successfully!";
            header('Location: checkout.php');
            exit;
        } catch (PDOException $e) {
            $error_message = 'Error adding the new address: ' . $e->getMessage();
        }
    }
}

// Handle order placement and initiate PayMongo payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? null;
    $landmark = $_POST['landmark'] ?? '';
    $note_to_rider = $_POST['note_to_rider'] ?? '';
    $gcash_number = $_POST['gcash_number'] ?? $user['gcash_number'];
    $selected_address_id = $_POST['delivery_address'] ?? null;

    // Retrieve the selected address details
    if ($selected_address_id) {
        $stmt = $pdo->prepare("SELECT user_brgy, user_street, user_city FROM user_addresses WHERE address_id = ?");
        $stmt->execute([$selected_address_id]);
        $selected_address = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the selected address exists
        if (!$selected_address) {
            $error_message = "Selected address not found.";
        }
    } else {
        $error_message = 'Please select a delivery address.';
    }

    // Validate total amount and payment method
    if ($total_amount > 0 && $selected_address && $payment_method) {
        try {
            $pdo->beginTransaction();

            // Insert the order into the orders table using the selected address data
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, user_brgy, user_street, user_city, landmark, note_to_rider, total_amount, order_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
            ");
            $stmt->execute([
                $user_id,
                $selected_address['user_brgy'], // Use the chosen address details
                $selected_address['user_street'],
                $selected_address['user_city'],
                $landmark,
                $note_to_rider,
                $total_amount
            ]);

            $order_id = $pdo->lastInsertId();

            // Continue with moving cart items to order_items and payment processing
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, menu_id, dish_name, quantity, price, total_price, special_instructions) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order_id,
                    $item['menu_id'],
                    $item['dish_name'], 
                    $item['quantity'],
                    $item['price'], 
                    $item['total_price'],
                    $item['special_instructions']
                ]);
            }

            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $stmt = $pdo->prepare("
                INSERT INTO payments (order_id, payment_method, payment_status, amount) 
                VALUES (?, ?, 'Pending', ?)
            ");
            $stmt->execute([$order_id, $payment_method, $total_amount]);

            if ($payment_method === 'gcash' && !empty($gcash_number)) {
                $stmt = $pdo->prepare("UPDATE users SET gcash_number = ? WHERE user_id = ?");
                $stmt->execute([$gcash_number, $user_id]);
            }

            $pdo->commit();

            // PayMongo API Integration (redirect to payment link)
            $paymongo_url = "https://api.paymongo.com/v1/links";
            $secret_key = "sk_test_hoB2uYDekp9jLfnAVmsU9P2M";

            $data = [
                'data' => [
                    'attributes' => [
                        'amount' => $total_amount * 100,
                        'description' => "Order payment for user ID $user_id",
                        'remarks' => "Order ID: $order_id",
                        'redirect' => [
                            'success' => "http://yourwebsite.com/success.php",
                            'failed' => "http://yourwebsite.com/failed.php"
                        ]
                    ]
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $paymongo_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode("$secret_key:")
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $response_data = json_decode($response, true);
            if (isset($response_data['data']['attributes']['checkout_url'])) {
                $payment_link = $response_data['data']['attributes']['checkout_url'];
                header("Location: $payment_link");
                exit;
            } else {
                $error_message = "Failed to create payment link. Please try again.";
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'Error placing the order: ' . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all required fields and ensure the cart is not empty.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <style> 
/* style/checkout.css */

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
    margin-left: 20px;
}

.image-button img {
    width: 30px;
    height: auto;
}

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

.container {
    margin-top: 140px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 40%;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

h1, h2 {
    margin-bottom: 20px;
    font-size: 24px;
    letter-spacing: 1px;
    color: #333;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

table th, table td {
    padding: 15px;
    text-align: center;
    border: 1px solid #ddd;
    font-size: 16px;
}
.order-container {
    width: 100%;
    max-width: 550px;
}
.order-container p {
    margin-left: 55%;
}
.order-container button[type=submit] {
    width: 50%;
    margin-left: 50%;
}

table th {
    background-color: #ffdb44;
    color: black;
    letter-spacing: 1px;
    text-transform: uppercase;
}

table td {
    font-size: 14px;
    color: #555;
    background-color: white;
}

input[type="text"] {
    width: 99.5%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
    margin-bottom: 15px;
    font-size: 16px;
}

textarea {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
    margin-bottom: 15px;
    font-size: 16px;
}

input[type="radio"] {
    margin-right: 10px;
}

.container button {
    background-color: #ffdb44;
    color: black;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
    margin: 10px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    background-image: linear-gradient(145deg, #ffdb44, #e2c43b);
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

.container button:hover {
    background-color: #e2c43b;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.gcash-field, #addAddressForm {
    display: none;
}

footer {
    background-color: black;
    color: white;
    padding: 15px;
    bottom: 0;
    width: 100%;
    box-sizing: border-box;
}

.footer-content h2 {
    color: white;
}

.footer-content {
    font-size: 13px;
    padding-left: 50px;
}

.custom-radio {
    display: inline-block;
    margin-bottom: 10px;
    position: relative;
    cursor: pointer;
    padding-left: 6px;
}

.custom-radio input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    width: 0;
    height: 0;
}

.custom-radio-image {
    display: flex;
    align-items: center;
    background: #f9f9f9;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: background-color 0.2s ease;
}

.custom-radio-image img {
    width: 24px;
    height: 24px;
    margin-right: 10px;
}

.custom-radio input:checked + .custom-radio-image {
    background-color: #ffdb44;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Ensure the modal is centered */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    background-color: rgba(0, 0, 0, 0.4); /* Black with opacity */
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: auto;
}

/* Modal Content/Box */
.modal-content {
    margin-top: 130px;
    margin-left: auto;
    margin-right: auto;
    background-color: #fefefe; 
    padding: 15px;
    border-radius: 10px; /* Rounded corners */
    border: 1px solid #888;
    width: 100%;
    max-width: 700px;
    position: relative;
}


        .modal-content input[type="text"] {
            width: 100%;
            padding: 12px 20px;
            margin-bottom: 0px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Rubik', sans-serif;
        }

        .modal-content button {
            width: 100%;
            height: auto;
            background-color: #ffdb44;
            color: black;
            padding: 15px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Rubik', sans-serif;
            font-weight: bold;
        }

        .modal-content button:hover {
            background-color: #e2c43b;
        }

        /* Enhance the look of the form */
        .modal-content form {
            display: flex;
            flex-direction: column;
        }

        .modal-content form input,
        .modal-content form button {
            margin-bottom: 15px;
        }
        .footer-content h2 {
            text-align: left;
        }
    </style>
    

    <script>
        // Show the modal
        function showAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'block';
        }

        // Close the modal when "Close" button or outside of modal is clicked
        function closeAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'none';
        }

        // Close modal when clicking outside the modal content
        window.onclick = function (event) {
            const modal = document.getElementById('addAddressModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function toggleGcashNumber() {
            const gcashField = document.getElementById('gcashNumberField');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            gcashField.style.display = paymentMethod === 'gcash' ? 'block' : 'none';
        }

function validateForm() {
    // Validate address selection
    const addressSelected = document.querySelector('input[name="delivery_address"]:checked');
    if (!addressSelected) {
        alert('Please select a delivery address.');
        return false;
    }

    // Validate payment method
    const paymentMethodSelected = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethodSelected) {
        alert('Please select a payment method.');
        return false;
    }

    // Validate GCash number if GCash is selected
    const gcashField = document.getElementById('gcash_number');
    const paymentMethod = paymentMethodSelected.value;
    if (paymentMethod === 'gcash') {
        const gcashNumber = gcashField.value.trim();
        
        // Check if GCash number starts with "09" and is exactly 11 digits long
        const gcashNumberRegex = /^09\d{9}$/;
        if (!gcashNumberRegex.test(gcashNumber)) {
            alert('Please enter a valid GCash number that starts with "09" and is exactly 11 digits.');
            return false;
        }
    }

    // Check if cart is empty
    const totalAmount = <?php echo $total_amount; ?>;
    if (totalAmount <= 0) {
        alert('Your cart is empty.');
        return false;
    }

    // If Cash on Delivery is selected, alert and redirect to home.php
    if (paymentMethod === 'cash_on_delivery') {
        alert('Your order has been placed successfully!');
        window.location.href = 'home.php';
        return false; // Prevent form submission
    }

    // If everything is valid and GCash is selected, proceed to form submission
    return true;
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

    <div class="container">
        <h1>Checkout</h1>

        <!-- Error and success messages -->
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <p style="color: green;"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <!-- Order Placement Form -->
        <form method="post" id="orderForm" onsubmit="return validateForm();">
            <input type="hidden" name="place_order" value="1">

            <h2>Personal Details</h2>
            <p>Name: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Mobile Number: <?php echo htmlspecialchars($user['mobile_number']); ?></p>

            <h2>Delivery Address</h2>

            <label class="custom-radio">
                <input type="radio" name="address_option" value="new" onclick="showAddAddressModal()">
                <span class="custom-radio-image">
                    <img src="img/add.png" alt="Add New Address">
                    Add New Address
                </span>
            </label>

            <label class="custom-radio">
                <input type="radio" name="address_option" value="existing" checked>
                <span class="custom-radio-image">
                    <img src="img/list.png" alt="Use Existing Address">
                    Use Existing Address
                </span>
            </label>

            <div id="existingAddresses" style="margin-top: 10px;">
                <?php foreach ($addresses as $address): ?>
                    <label class="custom-radio">
                        <input type="radio" name="delivery_address" value="<?php echo $address['address_id']; ?>" required>
                        <span class="custom-radio-image">
                            <img src="img/home.png" alt="Address Icon">
                            <?php echo htmlspecialchars($address['user_street']) . ', ' . 
                                       htmlspecialchars($address['user_brgy']) . ', ' . 
                                       htmlspecialchars($address['user_city']); ?>
                        </span>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <h2>Landmark & Note to Rider</h2>
            <label>Landmark:</label><br>
            <input type="text" name="landmark"><br>

            <label>Note to Rider:</label><br>
            <textarea name="note_to_rider" placeholder="Any special instructions for the rider?"></textarea><br>

            <h2>Payment Method</h2>
            <input type="radio" id="gcash" name="payment_method" value="gcash" onclick="toggleGcashNumber()" required>
            <label for="gcash">Gcash</label><br>
            <input type="radio" id="cod" name="payment_method" value="cash_on_delivery" onclick="toggleGcashNumber()" required>
            <label for="cod">Cash on Delivery</label><br>

            <div id="gcashNumberField" style="display:none;">
                <label for="gcash_number">GCash Number:</label><br>
                <input type="text" id="gcash_number" name="gcash_number" 
                       value="<?php echo htmlspecialchars($user['gcash_number'] ?? ''); ?>" 
                       placeholder="Enter GCash number"><br>
            </div>
            <div class="order-container">
            <h2>Order Summary</h2>
            <table>
                <tr>
                    <th>Dish</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['dish_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>₱<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p><strong>Total Amount: ₱<?php echo number_format($total_amount, 2); ?></strong></p>

            <!-- Order Placement Button -->
            <button type="submit">Place Order</button>
        </div>
        </form>
    </div>

    <!-- The Modal for Adding Address -->
    <div id="addAddressModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddAddressModal()">&times;</span>
            <h2>Add New Address</h2>
            <form method="post">
                <label for="user_street">Street:</label><br>
                <input type="text" id="user_street" name="user_street" required><br>

                <label for="user_brgy">Barangay:</label><br>
                <input type="text" id="user_brgy" name="user_brgy" required><br>

                <label for="user_city">City:</label><br>
                <input type="text" id="user_city" name="user_city" required><br>

                <button type="submit" name="add_address">Add Address</button>
            </form>
        </div>
    </div>
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

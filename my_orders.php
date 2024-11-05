<?php
session_start();
require 'dbconnection.php'; // Connect to the database

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch active orders (exclude 'Delivered' and 'Cancelled' orders)
$stmt_active = $pdo->prepare("
    SELECT o.order_id, oi.dish_name, oi.quantity, oi.price, oi.total_price, o.order_status, o.created_at, o.total_amount, p.payment_method
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN payments p ON o.order_id = p.order_id
    WHERE o.user_id = ? AND o.order_status NOT IN ('Delivered', 'Cancelled')
    ORDER BY o.created_at DESC
");

$stmt_active->execute([$user_id]);
$active_orders = $stmt_active->fetchAll(PDO::FETCH_ASSOC);

// Group the items by order_id for active orders
$grouped_active_orders = [];
foreach ($active_orders as $order) {
    $grouped_active_orders[$order['order_id']]['order_info'] = [
        'order_status' => $order['order_status'],
        'total_amount' => $order['total_amount'],
        'created_at' => $order['created_at'],
        'payment_method' => $order['payment_method']
    ];
    $grouped_active_orders[$order['order_id']]['items'][] = [
        'dish_name' => $order['dish_name'],
        'quantity' => $order['quantity'],
        'price' => $order['price'],
        'total_price' => $order['total_price']
    ];
}

// Handle cancel order request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = $_POST['cancel_order_id'];
    
    // Debug the POST request
    var_dump($_POST); // Check if the cancel_order_id is correctly passed
    
    try {
        // Update the order status to "Cancelled"
        $stmt_cancel = $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ? AND user_id = ?");
        $result = $stmt_cancel->execute([$cancel_order_id, $user_id]);

        // Debug query execution result
        if ($result) {
            echo "Order successfully cancelled.";
        } else {
            echo "Failed to cancel order.";
        }
        
        // Refresh the page to reflect the cancelled order
        header("Location: my_orders.php");
        exit;
    } catch (PDOException $e) {
        // Output any database errors
        echo "Error: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Gelline's Sizzling and Restaurant</title>
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

        .container {
            width: 65%;
            margin: 130px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
         .container h4{
            margin-left: 80%;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        }

        .back-btn {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #ffdb44;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            color: black;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
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

        .cancel-btn {
            margin-left: 82%;
            margin-top: 10px; /* Add some space between total amount and the button */
            display: block;
            padding: 10px 20px;
            background-color: #ff4d4d;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            color: white;
            width: fit-content; /* Ensure the button fits the text */
        }

        .cancel-btn:hover {
            background-color: #d93636;
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
    <div class="container">
        <h1>My Active Orders</h1>

        <!-- Active Orders Section -->
        
        <?php if (empty($grouped_active_orders)): ?>
            <p>No active orders.</p>
        <?php else: ?>
            <?php foreach ($grouped_active_orders as $order_id => $order): ?>
                <div class="order">
                    <h3>Order ID: <?php echo $order_id; ?></h3>
                    <p>Date: <?php echo $order['order_info']['created_at']; ?></p>
                    <p>Status: <?php echo $order['order_info']['order_status']; ?></p>
                    <p>Payment Method: <?php echo ucfirst($order['order_info']['payment_method']); ?></p> <!-- Display payment method -->
                    <table>
                        <thead>
                            <tr>
                                <th>Dish Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['dish_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <h4>Total Amount: ₱<?php echo number_format($order['order_info']['total_amount'], 2); ?></h4>
                    
                    <!-- Cancel Order Button (only show if the order is still pending) -->
                    <?php if ($order['order_info']['order_status'] === 'Pending'): ?>
                        <form method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="cancel_order_id" value="<?php echo $order_id; ?>">
                            <button type="submit" class="cancel-btn">Cancel Order</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

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
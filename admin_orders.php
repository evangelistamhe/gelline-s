<?php
require 'dbconnection.php'; // Connect to the database

// Fetch all active orders (excluding Delivered or Cancelled orders)
$active_orders_query = "
    SELECT o.order_id, u.first_name, u.last_name, o.total_amount, o.order_status, o.created_at, 
           oi.dish_name, oi.quantity, oi.price, oi.total_price
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_status NOT IN ('Delivered', 'Cancelled')
    ORDER BY o.created_at DESC, o.order_id
";
$active_orders_stmt = $pdo->query($active_orders_query);
$active_orders = $active_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all delivered or cancelled orders for the order history
$order_history_query = "
    SELECT o.order_id, u.first_name, u.last_name, o.total_amount, o.order_status, o.created_at, 
           oi.dish_name, oi.quantity, oi.price, oi.total_price
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_status IN ('Delivered', 'Cancelled')
    ORDER BY o.created_at DESC, o.order_id
";
$order_history_stmt = $pdo->query($order_history_query);
$order_history = $order_history_stmt->fetchAll(PDO::FETCH_ASSOC);

// Update the order status if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];

    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);

    echo "<script>alert('Order status updated!'); window.location.href = 'admin_orders.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - Gelline's Sizzling and Restaurant</title>
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
.home-btn, .ordr-btn, .adre-btn, .lout-btn {
    font-family: 'Rubik', sans-serif;
    background-color: transparent;
    border: none;
    color: black;
    padding: 5px 20px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    letter-spacing: 2px;
}
.home-btn:hover, .ordr-btn:hover, .adre-btn:hover, .lout-btn:hover {
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
.dropdown {
    position: relative;
    display: inline-block;
}
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #ffffff;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}
.dropdown-content a {
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
    margin: 120px auto;
}
.container-pend, .container-comp {
    margin-left: 2%;
    background-color: white;
    text-align: center;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
    max-width: 96%;
}
h1 {
    background-color: #ffdb44;
    color: black;
    padding: 20px;
    text-align: center;
    margin: 0;
    margin-bottom: 15px;
}
h2 {
    background-color: #ffdb44;
    color: black;
    padding: 20px;
    text-align: center;
    margin: 0;
    margin-bottom: 20px;
}
p {
    margin-bottom: 50px;
}
table {
    margin-left: 2%;
    
    width: 96%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background-color: rgba(249, 220, 46, 0.5);
    color: #333;
    font-weight: bold;
}
tr:nth-child(even) {
    background-color: rgba(249, 220, 46, 0.1);
}
li {
    list-style-type: none;
    margin: 0;
    padding: 0;
}

/* Style the select dropdown */
select {

    background-color: #ffffff;
    border: 1px solid #ddd;
    padding: 12px 14px;
    font-size: 16px;
    color: black;
    min-width: 100px;
    cursor: pointer;

    position: relative;
}

/* Add a custom arrow indicator */
select:after {
    content: '\25BC'; /* Downward arrow */
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}

/* Optional: Remove border-radius */
select {
    border-radius: 0;
}

/* Optional: Style for the dropdown options */
select option {
    padding: 10px;
    background-color: #ffffff;
    color: black;
    cursor: pointer;
}
button[name="update_status"] {
    background-color: #ffdb44; /* Set background color */
    color: black; /* Set text color */
    padding: 12px 20px; /* Add padding for size */
    border: none; /* Remove border */
    font-size: 15px; /* Set font size */
    font-weight: bold; /* Make the text bold */
    cursor: pointer; /* Change cursor to pointer */

    transition: background-color 0.3s ease; /* Smooth background color change */
}

button[name="update_status"]:hover {
    background-color: #ffcc00; /* Darken background color on hover */
}

</style>

</head>
<body>
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="ordr-btn" onclick="window.location.href = 'admin_dashboard.php';">Dashboard</button>
            
            <div class="dropdown">
                <button class="ordr-btn">Manage</button>
                <div class="dropdown-content">
                    <a href="admin_menu.php">Menu</a>
                    <a href="admin_tables.php">Tables</a>
                    <a href="admin_orders.php">Orders</a>
                    <a href="admin_reservations.php">Reservations</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="ordr-btn">Reports</button>
                <div class="dropdown-content">
                    <a href="admin_reports.php">Order</a>
                    <a href="reservation_reports.php">Reservation</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="ordr-btn">History</button>
                <div class="dropdown-content">
                    <a href="admin_order_historyy.php">Orders</a>
                    <a href="admin_reservation_historyy.php">Reservations</a>
                </div>
            </div>
            <button class="ordr-btn" onclick="window.location.href = 'audit_logs.php';">Audit Trail</button>
            <button class="ordr-btn" onclick="window.location.href = 'admin_login.php';">Log out</button>
        </div>

           
        </div>
    </header>
    <div class="container">
        <h1>Manage Orders</h1>

        <!-- Active Orders Section -->
        <div class="container-pend">
        <h2>Active Orders</h2>
        <?php if (count($active_orders) === 0): ?>
            <p>No active orders found.</p>
            <br>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_order_id = null;
                    foreach ($active_orders as $order): 
                        if ($current_order_id !== $order['order_id']):
                            if ($current_order_id !== null): ?>
                                </ul></td>
                                <td>₱<?php echo number_format($total_amount, 2); ?></td>
                                <td class="order-status"><?php echo $last_order_status; ?></td>
                                <td><?php echo $last_created_at; ?></td>
                                <td>
                                    <form method="post" class="update-form">
                                        <input type="hidden" name="order_id" value="<?php echo $last_order_id; ?>">
                                        <select name="order_status" required>
                                            <option value="Pending" <?php if ($last_order_status === 'Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Processing" <?php if ($last_order_status === 'Processing') echo 'selected'; ?>>Processing</option>
                                            <option value="Out for Delivery" <?php if ($last_order_status === 'Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                                            <option value="Delivered" <?php if ($last_order_status === 'Delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="Cancelled" <?php if ($last_order_status === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php 
                            endif; 

                            $current_order_id = $order['order_id'];
                            $total_amount = $order['total_amount'];
                            $last_order_status = $order['order_status'];
                            $last_created_at = $order['created_at'];
                            $last_order_id = $order['order_id'];
                            ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></td>
                                <td><ul>
                        <?php endif; ?>
                                    <li>
                                        <?php echo $order['quantity']; ?> x 
                                        <?php echo htmlspecialchars($order['dish_name']); ?> @ 
                                        ₱<?php echo number_format($order['price'], 2); ?> = 
                                        ₱<?php echo number_format($order['total_price'], 2); ?>
                                    </li>
                    <?php endforeach; ?>
                                </ul></td>
                                <td>₱<?php echo number_format($total_amount, 2); ?></td>
                                <td class="order-status"><?php echo $last_order_status; ?></td>
                                <td><?php echo $last_created_at; ?></td>
                                <td>
                                    <form method="post" class="update-form">
                                        <input type="hidden" name="order_id" value="<?php echo $last_order_id; ?>">
                                        <select name="order_status" required>
                                            <option value="Pending" <?php if ($last_order_status === 'Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Processing" <?php if ($last_order_status === 'Processing') echo 'selected'; ?>>Processing</option>
                                            <option value="Out for Delivery" <?php if ($last_order_status === 'Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                                            <option value="Delivered" <?php if ($last_order_status === 'Delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="Cancelled" <?php if ($last_order_status === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status">Update</button>
                                    </form>
                                </td>
                            </tr>
                </tbody>
            </table><br>
        <?php endif; ?>
    </div>


        <!-- Order History Section -->
        <br><div class="container-comp">
        <h2>Order History</h2>
        <?php if (count($order_history) === 0): ?>
            <p>No orders in history.</p>
            <br>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_order_id = null;
                    foreach ($order_history as $order): 
                        if ($current_order_id !== $order['order_id']): 
                            if ($current_order_id !== null): ?>
                                </ul></td>
                                <td>₱<?php echo number_format($total_amount, 2); ?></td>
                                <td class="order-status"><?php echo $last_order_status; ?></td>
                                <td><?php echo $last_created_at; ?></td>
                            </tr>
                            <?php 
                            endif;

                            $current_order_id = $order['order_id'];
                            $total_amount = $order['total_amount'];
                            $last_order_status = $order['order_status'];
                            $last_created_at = $order['created_at'];
                            ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></td>
                                <td><ul>
                        <?php endif; ?>
                                    <li>
                                        <?php echo $order['quantity']; ?> x 
                                        <?php echo htmlspecialchars($order['dish_name']); ?> @ 
                                        ₱<?php echo number_format($order['price'], 2); ?> = 
                                        ₱<?php echo number_format($order['total_price'], 2); ?>
                                    </li>
                    <?php endforeach; ?>
                                </ul></td>
                                <td>₱<?php echo number_format($total_amount, 2); ?></td>
                                <td class="order-status"><?php echo $last_order_status; ?></td>
                                <td><?php echo $last_created_at; ?></td>
                            </tr>
                </tbody>
            </table>
            <br>
        <?php endif; ?>
    </div>
    </div>
</body>
</html>

<?php
session_start();
require 'dbconnection.php'; // Connect to the database

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Fetch all items from the selected order
    $stmt = $pdo->prepare("
        SELECT oi.menu_id, oi.quantity
        FROM order_items oi
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add each item from the order to the cart
    foreach ($order_items as $item) {
        $menu_id = $item['menu_id'];
        $quantity = $item['quantity'];

        // Check if the dish is already in the cart
        $stmt_check = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND menu_id = ?");
        $stmt_check->execute([$user_id, $menu_id]);
        $existing_cart_item = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($existing_cart_item) {
            // Update the quantity if the dish is already in the cart
            $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND menu_id = ?");
            $stmt_update->execute([$quantity, $user_id, $menu_id]);
        } else {
            // Insert the new item into the cart
            $stmt_insert = $pdo->prepare("INSERT INTO cart_items (user_id, menu_id, quantity) VALUES (?, ?, ?)");
            $stmt_insert->execute([$user_id, $menu_id, $quantity]);
        }
    }

    // Redirect the user to the cart page
    header('Location: cart.php');
    exit;
}
?>

<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = $_POST['cart_item_id'];
    $new_quantity = $_POST['quantity'];
    $special_instructions = $_POST['special_instructions'] ?? '';

    try {
        if ($new_quantity > 0) {
            // Update the cart item quantity
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, special_instructions = ? WHERE cart_item_id = ? AND user_id = ?");
            $stmt->execute([$new_quantity, $special_instructions, $cart_item_id, $user_id]);
            echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
        } else {
            // Remove the item from the cart if quantity is zero
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
            $stmt->execute([$cart_item_id, $user_id]);
            echo json_encode(['status' => 'success', 'message' => 'Item removed']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>

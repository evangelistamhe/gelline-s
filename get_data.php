<?php
require 'dbconnection.php';

// Total Sales
$total_sales = $pdo->query("SELECT IFNULL(SUM(total_amount), 0) AS total_sales FROM orders")->fetch()['total_sales'];

// Total Users
$total_users = $pdo->query("SELECT COUNT(*) AS total_users FROM users")->fetch()['total_users'];

// Latest Orders
$latest_orders = $pdo->query("SELECT o.order_id, u.first_name, u.last_name, o.total_amount, o.order_date 
    FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC LIMIT 5")->fetchAll();

echo json_encode([
    'total_sales' => $total_sales,
    'total_users' => $total_users,
    'latest_orders' => $latest_orders
]);
?>

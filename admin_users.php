<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch users
$users = $pdo->query("SELECT * FROM users")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
</head>
<body>
    <h1>Manage Users</h1>
    <table border="1">
        <tr>
            <th>User ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Mobile Number</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['user_id']; ?></td>
            <td><?php echo $user['first_name']; ?></td>
            <td><?php echo $user['last_name']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['mobile_number']; ?></td>
            <td><a href="admin_user_orders.php?user_id=<?php echo $user['user_id']; ?>">View Orders</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

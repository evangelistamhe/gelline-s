<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch current settings
$settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_name = $_POST['restaurant_name'];
    $opening_hours = $_POST['opening_hours'];
    
    $stmt = $pdo->prepare("UPDATE settings SET restaurant_name = ?, opening_hours = ? WHERE id = 1");
    $stmt->execute([$restaurant_name, $opening_hours]);
    echo "Settings updated!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
</head>
<body>
    <h1>Settings</h1>
    <form method="post">
        <label>Restaurant Name:</label>
        <input type="text" name="restaurant_name" value="<?php echo $settings['restaurant_name']; ?>"><br>
        <label>Opening Hours:</label>
        <input type="text" name="opening_hours" value="<?php echo $settings['opening_hours']; ?>"><br>
        <button type="submit">Update Settings</button>
    </form>
</body>
</html>

<?php
// Database connection
$host = 'localhost';
$db = 'gellines_restaurant'; // Replace with your database name
$user = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

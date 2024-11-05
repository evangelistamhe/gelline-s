<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php'); // Redirect to admin login if not logged in
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gelline's Sizzling and Restaurant</title>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <p>Manage the system using the following features:</p>

    <!-- Navigation buttons for Admin functionalities -->
    <div>
        <a href="admin_profile.php"><button>Admin Profile</button></a>
        <a href="user_feedback.php"><button>User Feedback</button></a>
        <a href="audit_trail.php"><button>Track User Movements (Audit Trail)</button></a>
        <a href="inventory.php"><button>Manage Inventory</button></a>
        <a href="admin_orders.php"><button>Manage Orders</button></a>
    </div>
</body>
</html>

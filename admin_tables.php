<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection is included

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch all tables and their current status
$tables = $pdo->query("
    SELECT table_id, table_number, capacity, price_per_person, image
    FROM restaurant_tables
")->fetchAll();

// Handle table update and image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_id'])) {
    $table_id = $_POST['table_id'];
    $capacity = $_POST['capacity'];

    // Handle image upload
    if (!empty($_FILES['table_image']['name'])) {
        $target_dir = "uploads/tables/"; // Directory to store uploaded images
        $target_file = $target_dir . basename($_FILES["table_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $valid_extensions = ["jpg", "jpeg", "png", "gif"];

        // Check if file is an image
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES["table_image"]["tmp_name"], $target_file)) {
                // Update table info and image path in the database
                $stmt = $pdo->prepare("UPDATE restaurant_tables SET capacity = ?, image = ? WHERE table_id = ?");
                $stmt->execute([$capacity, $target_file, $table_id]);
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        // Update table info without changing the image
        $stmt = $pdo->prepare("UPDATE restaurant_tables SET capacity = ? WHERE table_id = ?");
        $stmt->execute([$capacity, $table_id]);
    }

    // Redirect back to the admin page after update
    header('Location: admin_tables.php');
    exit;
}

// Handle adding new tables
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_table'])) {
    $table_number = $_POST['table_number'];
    $capacity = $_POST['capacity'];
    $price_per_person = $_POST['price_per_person'];

    // Handle image upload for new table
    $image_path = null;
    if (!empty($_FILES['new_table_image']['name'])) {
        $target_dir = "uploads/tables/";
        $target_file = $target_dir . basename($_FILES["new_table_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $valid_extensions = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES["new_table_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                echo "Error uploading image for the new table.";
            }
        } else {
            echo "Only JPG, JPEG, PNG & GIF files are allowed for the new table image.";
        }
    }

    // Insert new table into the database
    $stmt = $pdo->prepare("INSERT INTO restaurant_tables (table_number, capacity, price_per_person, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$table_number, $capacity, $price_per_person, $image_path]);

    // Redirect back to the admin page after adding
    header('Location: admin_tables.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables - Admin</title>
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
            background: url('img/gellinesbg.png') repeat;
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
        .logo img {
            height: 120px;
            width: 130px;
            position: absolute;
            left: 0;
            top: 0px;
            z-index: 3;
            margin-left: 130px;
        }
        h1 {
            background-color: #ffdb44;
            color: black;
            padding: 20px;
            text-align: center;
            margin-top: 120px;
        }

        .add-container, .table-container {
            padding: 10px 20px;
            margin-left: 4%;
            background-color: white;
            max-width: 1400px;
            width: 89.5%;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
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
        .edit-btn, .add-btn {
            background-color: #ffdb44;
            color: black;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .edit-btn:hover, .add-btn:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        input, select {
            padding: 10px;
            width: 80%;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .inline-form input[type="number"],
        .inline-form input[type="file"],
        .inline-form button {
            display: inline-block;
            vertical-align: middle;
            margin: 5px;
        }

        .inline-form input[type="number"] {
            width: 15%; /* Minimize width for better inline fit */
            padding: 8px;
        }

        .inline-form input[type="file"] {
            width: 30%; /* Adjust file input width to fit inline */
            padding: 8px;
        }

        .inline-form button {
            width: auto;
            padding: 8px 16px;
            background-color: #ffdb44;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .inline-form button:hover {
            background-color: #e2c43b;
        }
        .inline-form {
            margin-left: 6%;
        }
        .add-container h3 {
            text-align: center;
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

    <h1>Manage Tables</h1>

    <!-- Add New Table Section -->
<div class="add-container">
    <h3>Add New Table</h3>
    <form method="POST" enctype="multipart/form-data" class="inline-form">
        <input type="number" name="table_number" required placeholder="Table Number">
        <input type="number" name="capacity" required placeholder="Capacity">
        <input type="number" name="price_per_person" required placeholder="Price per Head">
        <input type="file" name="new_table_image" accept="image/*">

        <button type="submit" class="add-btn" name="add_table">Add Table</button>
    </form>
</div>
<br>
    <!-- Current Tables Section -->
    <div class="table-container">
<br>
<br>
        <table>
            <thead>
                <tr>
                    <th>Table Number</th>
                    <th>Capacity</th>
                    <th>Price per Person</th>
                    <th>Image</th>
                    <th>Upload New Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table): ?>
                <tr>
                    <form method="POST" enctype="multipart/form-data">
                        <td><?php echo htmlspecialchars($table['table_number']); ?></td>
                        <td>
                            <input type="number" name="capacity" value="<?php echo htmlspecialchars($table['capacity']); ?>" required>
                        </td>
                        <td>
                            ₱<?php echo htmlspecialchars($table['price_per_person']); ?>
                        </td>
                        <td>
                            <?php if ($table['image']): ?>
                                <img src="<?php echo htmlspecialchars($table['image']); ?>" alt="Table Image" style="width:100px;height:auto;">
                            <?php else: ?>
                                <p>No image available</p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="file" name="table_image" accept="image/*">
                        </td>
                        <td>
                            <input type="hidden" name="table_id" value="<?php echo $table['table_id']; ?>">
                            <button type="submit" class="edit-btn">Update</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
</body>
</html>

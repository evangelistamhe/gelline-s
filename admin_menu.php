<?php
session_start();
require 'dbconnection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

$upload_dir = 'uploads/menu_img/'; // Correct directory to store uploaded images

// Ensure the directory exists, create it if not
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // Create the directory with proper permissions if it doesn't exist
}

// Handle add/update menu items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle adding a new menu item
    if (isset($_POST['add_dish'])) {
        $dish_name = $_POST['dish_name'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $price = $_POST['price'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        // Set the default status to 'available'
        $status = 'available';

        // Check if all required fields are filled
        if ($dish_name && $category_id && $price && $quantity) {
            // Check if the dish already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu WHERE dish_name = ?");
            $stmt->execute([$dish_name]);
            $dish_exists = $stmt->fetchColumn();

            if ($dish_exists) {
                echo "<script>alert('The dish already exists. Please choose a different name or update the existing dish.');</script>";
            } else {
                // Handle image upload
                $image_name = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_name = basename($_FILES['image']['name']);
                    $target_file = $upload_dir . $image_name;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_name = $target_file;
                    } else {
                        $error_message = "Failed to upload the image.";
                    }
                }

                // Add new menu item
                $stmt = $pdo->prepare("INSERT INTO menu (dish_name, category_id, price, status, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$dish_name, $category_id, $price, $status, $quantity, $image_name]);

                // Redirect to avoid form resubmission
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            echo "<script>alert('Please fill in all required fields including category.');</script>";
        }
    }



    // Handle update existing menu item
if (isset($_POST['update_status'])) {
    // Debugging: see the POST data
    var_dump($_POST); // Check the data being sent

    $menu_id = $_POST['menu_id'] ?? null;
    $price = $_POST['price'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($menu_id && $price && $quantity && $status) {
        // Handle image upload if a new image is uploaded
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_name = basename($_FILES['image']['name']);
            $target_file = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_name = $target_file;
            } else {
                $error_message = "Failed to upload the image.";
            }
        }

        // Update the menu item in the database
        if ($image_name) {
            $stmt = $pdo->prepare("UPDATE menu SET price = ?, status = ?, quantity = ?, image = ? WHERE menu_id = ?");
            $stmt->execute([$price, $status, $quantity, $image_name, $menu_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE menu SET price = ?, status = ?, quantity = ? WHERE menu_id = ?");
            $stmt->execute([$price, $status, $quantity, $menu_id]);
        }

        // Redirect after update
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Required fields are missing.";
    }
}
    // Handle deletion of menu item
    if (isset($_POST['delete_menu_id'])) {
        $menu_id_to_delete = $_POST['delete_menu_id'];

        // Prepare and execute the delete query
        $stmt = $pdo->prepare("DELETE FROM menu WHERE menu_id = ?");
        $stmt->execute([$menu_id_to_delete]);

        // After deletion, redirect to avoid resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch all menu items
$menu_items = $pdo->query("SELECT * FROM menu")->fetchAll();
// Fetch all categories from the categories table
$categories = $pdo->query("SELECT category_id, category_name FROM menu_category")->fetchAll();


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
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

        .menu-content {
            margin-top: 120px;
        }
        h1 {
            background-color: #ffdb44;
            color: black;
            padding: 20px;
            text-align: center;
            margin: 0;
            margin-bottom: 15px;
        }
        .add-container {
            padding: 10px 20px;
            margin-left: 4%;
            background-color: white;
            max-width: 1400px;
            width: 89.5%;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }
        .add-container h3 {
            text-align: center;
        }

        .add-container input[type="file"] {
            margin-left: 9%;
            border: 0.8px solid rgba(0, 0, 0, 0.5); /* Yellow outline color */
            padding: 8px; /* Add padding for better appearance */
            border-radius: 4px; /* Optional: rounded corners */
            background-color: #ffffff; /* White background */
            outline: none; /* Remove default outline */
            transition: border-color 0.3s ease; /* Smooth transition for hover/focus */
        }

        .current-container {
            margin-left: 4%;
            background-color: white;
            max-width: 1400px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }
        .current-container h2 {
            
            text-align: center;
        }

        .category-filter-container {
            margin-left: 76%;
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
        .current-container select {

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
        .current-container select:after {
            content: '\25BC'; /* Downward arrow */
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Optional: Remove border-radius */


        /* Optional: Style for the dropdown options */
        .current-container select option {
            padding: 10px;
            background-color: #ffffff;
            color: black;
            cursor: pointer;
        }

        .modal-content select {

            background-color: #ffffff;
            border: 1px solid #ddd;
            padding: 12px 14px;
            font-size: 16px;
            color: black;
            width: 90%;
            cursor: pointer;

            position: relative;
        }

        /* Add a custom arrow indicator */
        .modal-content select:after {
            content: '\25BC'; /* Downward arrow */
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Optional: Remove border-radius */


        /* Optional: Style for the dropdown options */
        .modal-content select option {
            padding: 10px;
            background-color: #ffffff;
            color: black;
            cursor: pointer;
        }
        
        .modal-content {
            border-radius: 10px;
        }
        .modal button[name="update_status"] {
            background-color: #ffdb44; /* Set background color */
            color: black; /* Set text color */
            padding: 12px 20px; /* Add padding for size */
            border: none; /* Remove border */
            font-size: 15px; /* Set font size */
            font-weight: bold; /* Make the text bold */
            cursor: pointer; /* Change cursor to pointer */
            width: 95%;
            margin: 2.5%;
            transition: background-color 0.3s ease; /* Smooth background color change */
            border-radius: 10px;
        }

        .modal button[name="update_status"]:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .modal input {
            border-radius: 5px;
            height: 40px;
            width: 90%;
            outline: none; /* Removes the input field outline */
            border: 1px solid #ccc; /* Optionally, add a subtle border */
            padding: 0 10px; /* Adds some padding for better UX */
        }

        .edit-btn {
            font-weight: bold;
            width: 40%;
            background-color: #ffdb44;
            color: black;
            padding: 10px 20px;
            border: none;
            
            font-size: 14px;
            cursor: pointer;
            margin: 5px 0; /* Add vertical margin */
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .edit-btn:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .form-inline {
            font-family: 'Rubik', sans-serif;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .form-inline input, .form-inline select {
            font-family: 'Rubik', sans-serif;
            padding: 5px;
            font-size: 14px;
        }

        .form-inline button {
            font-weight: bold;
            font-family: 'Rubik', sans-serif;
            background-color: #ffdb44;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-inline button:hover {
            background-color: #e2c43b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        
        /* Basic styles for modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
    <div class="menu-content">
        <h1>Manage Menu</h1>
        <div class="add-container">
            <h3>Add Dish</h3>
        <form method="post" class="add-menu-form" enctype="multipart/form-data">
            <div class="form-inline">
                <input type="file" name="image" accept="image/*">
                <input type="text" name="dish_name" placeholder="Dish Name" required>
                
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <select name="category_id" required>
                <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo $category['category_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="add_dish">Add Dish</button>
            </div>
        </form>
    </div>
    <br>
        <div class="current-container">
            <br>
        <div class="category-filter-container">
            <label for="categoryFilter">Filter by Category:</label>
            <select id="categoryFilter" onchange="filterMenuByCategory()">
                <option value="all">All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>">
                        <?php echo $category['category_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Dish</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menu_items as $item): ?>
                <tr class="menu-item" data-category="<?php echo $item['category_id']; ?>">
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['dish_name']); ?>" width="50" height="50">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['dish_name']; ?></td>
                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo ucfirst($item['status']); ?></td>
                    <td>
                        <button class="edit-btn" onclick="openModal(<?php echo $item['menu_id']; ?>, '<?php echo $item['dish_name']; ?>', <?php echo $item['price']; ?>, <?php echo $item['quantity']; ?>, '<?php echo $item['status']; ?>')">Edit</button>

                        <button class="edit-btn" onclick="confirmDelete(<?php echo $item['menu_id']; ?>)">Delete</button>

                        <!-- Form to handle deletion -->
                        <form id="deleteForm-<?php echo $item['menu_id']; ?>" method="post" style="display:none;">
                            <input type="hidden" name="delete_menu_id" value="<?php echo $item['menu_id']; ?>">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
    </div>
    </div>
    <br>

    <!-- Modal Structure -->
    <!-- Modal Structure -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <form id="editForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="menu_id" id="menu_id">
            <table>
                <tr>
                    <td><label>Dish Name:</label></td>
                    <td><input type="text" id="dish_name" disabled></td> <!-- Disable the input for dish name -->
                </tr>
                <tr>
                    <td><label for="price">Price:</label></td>
                    <td><input type="number" step="0.01" name="price" id="price" required></td>
                </tr>
                <tr>
                    <td><label for="quantity">Quantity:</label></td>
                    <td><input type="number" name="quantity" id="quantity" required></td>
                </tr>
                <tr>
                    <td><label for="status">Status:</label></td>
                    <td>
                        <select name="status" id="status">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </td>
                </tr>
            </table>
            <button type="submit" name="update_status">Update</button>
        </form>
    </div>
</div>




    <script>
        function confirmDelete(menuId) {
            const confirmation = confirm("Are you sure you want to delete this item?");
            
            if (confirmation) {
                // If confirmed, submit the form for deletion
                document.getElementById(`deleteForm-${menuId}`).submit();
            }
        }
                // Function to open modal and populate fields with existing data
        function openModal(menuId, dishName, price, quantity, status) {
            document.getElementById('menu_id').value = menuId;
            document.getElementById('dish_name').value = dishName; // Display the dish name in the disabled input
            document.getElementById('price').value = price;
            document.getElementById('quantity').value = quantity;
            document.getElementById('status').value = status;

            // Show the modal
            document.getElementById('editModal').style.display = "block";
        }


        // Function to close the modal
        function closeModal() {
            document.getElementById('editModal').style.display = "none";
        }

        // Function to filter the menu items by category
        function filterMenuByCategory() {
            const categoryFilter = document.getElementById('categoryFilter').value;
            const allMenuItems = document.querySelectorAll('.menu-item');

            allMenuItems.forEach(item => {
                if (categoryFilter === 'all' || item.getAttribute('data-category') === categoryFilter) {
                    item.style.display = 'table-row'; // Ensure the row is displayed correctly in a table
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Close modal when clicking outside of the modal content
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>

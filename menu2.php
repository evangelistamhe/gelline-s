<?php
session_start();
require 'dbconnection.php';

// Check if the user is logged in and pass that info to JavaScript
$is_logged_in = isset($_SESSION['user_id']) ? 'true' : 'false';

$user_id = $_SESSION['user_id'] ?? null; // User ID from session
$success_message = ''; // Store success messages

// Handle adding items to the order if logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_order']) && $user_id) {
    $menu_id = $_POST['menu_id'];
    $quantity = (int)$_POST['quantity'];
    $special_instructions = $_POST['special_instructions'] ?? '';

    // Fetch the selected dish details from the menu
    $stmt = $pdo->prepare("SELECT dish_name, price, quantity FROM menu WHERE menu_id = ?");
    $stmt->execute([$menu_id]);
    $menu_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($menu_item) {
        $available_quantity = (int)$menu_item['quantity'];

        // Validate the stock quantity
        if ($quantity > $available_quantity) {
            echo "<script>alert('Quantity exceeds available stock. Please try again.'); window.history.back();</script>";
        } else {
            // Create a new order if one does not exist
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_status) VALUES (?, 0, 'Pending')");
            $stmt->execute([$user_id]);
            $order_id = $pdo->lastInsertId(); // Retrieve the new order ID

            // Add the selected item to the order_items table
            $total_price = $menu_item['price'] * $quantity;
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, dish_name, quantity, price, total_price, special_instructions) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $order_id, 
                $menu_item['dish_name'], 
                $quantity, 
                $menu_item['price'], 
                $total_price, 
                $special_instructions
            ]);

            // Update the stock in the menu table
            $stmt = $pdo->prepare("UPDATE menu SET quantity = quantity - ? WHERE menu_id = ?");
            $stmt->execute([$quantity, $menu_id]);

            $success_message = 'Item added to order successfully!';
        }
    } else {
        echo "<script>alert('Menu item not found.'); window.history.back();</script>";
    }
}

// Fetch all categories from the categories table
$categories = $pdo->query("SELECT category_id, category_name FROM menu_category")->fetchAll();

// Fetch all available menu items
$menu_items = $pdo->query("SELECT * FROM menu WHERE quantity > 0")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/menu.css">
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

          .home-btn,
          .ordr-btn,
          .lout-btn,
          .image-button {
              font-family: 'Rubik', sans-serif;
              background-color: transparent;
              border: none;
              color: black;
              padding: 5px 20px;
              font-size: 16px;
              cursor: pointer;
              transition: background-color 0.3s ease, box-shadow 0.3s ease;
              text-shadow: ;
              letter-spacing: 2px;
          }

          /* Semi-transparent yellow hover effect */
          .home-btn:hover,
          .ordr-btn:hover,
          .lout-btn:hover,
          .image-button:hover {
              background-color: rgba(0, 0, 0, 0.1); /* Transparent yellow */
              box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.4); /* Elevation effect */
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

          .image-button {
              display: inline-block;
              border: none;
              padding: 0;
              background: none;
              cursor: pointer;
          }

          .image-button img {
              width: 30px;
              height: auto;
          }

          /* Dropdown CSS */
          .dropdown {
              position: relative;
              display: inline-block;
          }

          .dropdown-content {
              display: none;
              position: absolute;
              background-color: white;
              min-width: 160px;
              box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
              z-index: 1;
          }

          .dropdown-content a {
            font-family: 'Rubik', sans-serif;
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

          /* Main Menu CSS */
          .main-menu {
            margin-top: 100px; /* Adjusted margin to bring it below the header */
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: calc(100vh - 140px); /* Adjust for the header height */
        }

            .menu-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start; /* Ensures items are aligned to the left */
    gap: 10px; /* Add some space between items */
    padding: 20px;
}

.menu-item {
    width: 250px; /* Fixed width for each menu item */
    background-color: white;
    border: 1px solid #ddd;
    padding: 20px;
    margin: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}

@media screen and (min-width: 768px) {
    .menu-container {
        justify-content: flex-start; /* Adjust for smaller screens */
    }
}

@media screen and (min-width: 1024px) {
    .menu-container {
        justify-content: space-evenly; /* Evenly distribute items across larger screens */
    }
}


        .quantity-container {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .decrement-btn, .increment-btn {
            background-color: #ffdb44;
            border: 1px solid #ccc;
            padding: 5px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            text-align: center;
        }

        input[type="number"] {
            width: 50px;
            text-align: center;
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Cart button positioning */
        /* Center the cart button within the parent container */
/* Cart button styling */
.cart-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px; /* Add some spacing from other content if needed */
    width: 100%; /* Make sure it spans the width of its parent */
    cursor: pointer;
    background: none; /* Remove any background color */
    border: none; /* Remove border */
    box-shadow: none; /* Remove any shadow */
}


.cart-btn img {
    width: 30px;
    height: auto;
}



        .category-filter-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }

        .category-filter-container label {
            margin-right: 10px;
            font-size: 16px;
        }

        #categoryFilter {
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 250px; /* Fixed width to prevent movement */
            text-align: center;
        }

        #categoryFilter:hover {
            background-color: #ffffff;
            border-color: #bbb;
        }

        #categoryFilter:focus {
            outline: none;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
        }
                /* Add spacing between the icons in the header */
.image-button {
    display: inline-block;
    border: none;
    padding: 0;
    background: none;
    cursor: pointer;
    margin-left: 20px; /* Adjust this value to add space between icons */
}

.image-button img {
    width: 30px;
    height: auto;
}

/* Dropdown CSS for Profile */
.profile-dropdown {
    position: relative;
    display: inline-block;
    margin-right: 20px;
}

.profile-dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

.profile-dropdown-content a {
    font-family: 'Rubik', sans-serif;
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.profile-dropdown-content a:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

.profile-dropdown:hover .profile-dropdown-content {
    display: block;
}

        footer {
            background-color: black;
            color: white;
            padding: 15px;
            bottom: 0;
            width:  100%;
            box-sizing: border-box; /* Ensures padding doesn't cause overflow */
          }
          footer .footer-content {
            font-size: 13px;
              padding-left: 50px; /* Adjust this value to increase the left padding */
          }

    </style>
<script>
        const isLoggedIn = <?php echo $is_logged_in; ?>; // Pass the login status to JavaScript

        function handleAddToCart(event, menuId) {
            // Check if the user is logged in
            if (!isLoggedIn) {
                event.preventDefault(); // Stop form submission
                alert('Please log in first to add items to your cart.');
            }
        }

        function incrementQuantity(menuId) {
            if (!isLoggedIn) {
                alert('Please log in first to adjust the quantity.');
                return; // Prevent incrementing if not logged in
            }

            const quantityInput = document.getElementById('quantity_' + menuId);
            let currentQuantity = parseInt(quantityInput.value);
            let availableStock = parseInt(quantityInput.getAttribute('data-stock'));

            // Prevent the quantity from exceeding available stock
            if (currentQuantity < availableStock) {
                quantityInput.value = currentQuantity + 1;
            } else {
                alert('Maximum available stock reached.');
            }
        }

        function decrementQuantity(menuId) {
            if (!isLoggedIn) {
                alert('Please log in first to adjust the quantity.');
                return; // Prevent decrementing if not logged in
            }

            const quantityInput = document.getElementById('quantity_' + menuId);
            let currentQuantity = parseInt(quantityInput.value);

            if (currentQuantity > 1) {
                quantityInput.value = currentQuantity - 1;
            }
        }
        function filterMenuByCategory() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const allMenuItems = document.querySelectorAll('.menu-item');

    allMenuItems.forEach(item => {
        // Convert both values to strings to avoid type mismatch
        const itemCategory = item.getAttribute('data-category');
        
        if (categoryFilter === 'all' || itemCategory === categoryFilter) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}
    </script>
</head>
<body>
    <!-- Navigation Bar -->
    <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="home-btn" onclick="window.location.href = 'home2.php';">Home</button>
            <button class="ordr-btn" onclick="window.location.href = 'about2.php';">About Us</button>
          

            <div class="dropdown">
                <button class="ordr-btn">Menus</button>
                <div class="dropdown-content">
                    <a href="menu2.php">Food Menu</a>
                    
                </div>
            </div>
            <button class="ordr-btn" onclick="window.location.href = 'login.php';">Sign In</button>
        </div>
    </header>

   <!-- Main Menu Section -->
    <div class="main-menu">
        <h2>Gelline's Menu</h2>

        <!-- Display success message -->
        <?php if ($success_message): ?>
            <p style="color: green;"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <!-- Dropdown for category filter -->
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


        <!-- Menu Items Display -->
        <div class="menu-container">
            <?php foreach ($menu_items as $item): ?>
                <div class="menu-item" data-category="<?php echo $item['category_id']; ?>">
                    <center>
                        <img src="<?php echo $item['image']; ?>" 
                             alt="<?php echo $item['dish_name']; ?>" 
                             style="width:150px; height:150px; border-radius:5px; margin-bottom:10px;">
                    </center>
                    <h3><?php echo $item['dish_name']; ?></h3>
                    <p>Price: ₱<?php echo number_format($item['price'], 2); ?></p>
                    <p>Status: <?php echo $item['status'] === 'available' ? 'Available' : 'Unavailable'; ?></p>

                    <!-- Display the form if the item is available -->
                    <?php if ($item['status'] === 'available'): ?>
                        <!-- Attach the handleAddToCart function to the form onsubmit event -->
                        <form method="post" onsubmit="handleAddToCart(event, <?php echo $item['menu_id']; ?>)">
                            <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">

                            <div class="quantity-container">
                            <button type="button" class="decrement-btn" onclick="decrementQuantity(<?php echo $item['menu_id']; ?>)">-</button>
                            
                            <input type="number" id="quantity_<?php echo $item['menu_id']; ?>" 
                                   name="quantity" value="1" min="1" 
                                   max="<?php echo $item['quantity']; ?>" 
                                   data-stock="<?php echo $item['quantity']; ?>">
                            
                            <button type="button" class="increment-btn" onclick="incrementQuantity(<?php echo $item['menu_id']; ?>)">+</button>
                        </div>

                            <!-- Replace submit button with image -->
                            <button type="submit" name="add_to_order" class="cart-btn">
                                <img src="img/add-cart.png" alt="Add to Cart" style="width: 40px; height: auto;">
                            </button>
                        </form>

                    <?php else: ?>
                        <p style="color: red;">Unavailable</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    </div>
    <footer>
  <div class="footer-content">
      <h2>Contact Us</h2>
        <p>Do you have any questions, or would you like to make a reservation? Feel free to contact us at:</p>
          <ul>
              <li>Email: gelssizzlingresto@gmail.com</li>
                      <li>Phone: +63 923 653 3181</li>
                      <li>Address: Dulong bayan, Santa Maria, Philippines</li>
          </ul>
        <p>We look forward to serving you at Gelline's Sizzling and Restaurant!</p>
      </div>
  </footer>
</body>
</html>
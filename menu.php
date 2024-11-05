<?php
session_start();
require 'dbconnection.php'; // Ensure the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // User ID from session
$success_message = ''; // Store success messages
$error_message = ''; // Store error messages

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
    $quantity = (int)$_POST['quantity'];

    // Validation: Ensure quantity is a valid number
    if ($quantity <= 0 || !is_numeric($quantity)) {
        $error_message = "Please enter a valid quantity.";
    } else {
        // Fetch the selected dish details from the menu
        $stmt = $pdo->prepare("SELECT dish_name, price, quantity FROM menu WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $menu_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu_item) {
            $available_quantity = (int)$menu_item['quantity'];

            // Validate the stock quantity
            if ($quantity > $available_quantity) {
                $error_message = "Quantity exceeds available stock. Please try again.";
            } else {
                // Check if the item already exists in the cart
                $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND menu_id = ?");
                $stmt->execute([$user_id, $menu_id]);
                $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cart_item) {
                    // Update quantity if the item already exists
                    $new_quantity = $cart_item['quantity'] + $quantity;
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                    $stmt->execute([$new_quantity, $cart_item['cart_item_id']]);
                } else {
                    // Insert a new item into the cart
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (user_id, menu_id, quantity) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$user_id, $menu_id, $quantity]);
                }

                // Update the stock in the menu table
                $stmt = $pdo->prepare("UPDATE menu SET quantity = quantity - ? WHERE menu_id = ?");
                $stmt->execute([$quantity, $menu_id]);

                $success_message = 'Item added to cart successfully!';
            }
        } else {
            $error_message = "Menu item not found.";
        }
    }
}

// Fetch all categories from the categories table
$categories = $pdo->query("SELECT category_id, category_name FROM menu_category")->fetchAll();

// Fetch all menu items, including those with quantity 0
$menu_items = $pdo->query("SELECT * FROM menu")->fetchAll();

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

        document.addEventListener('DOMContentLoaded', function () {
    // Select all quantity inputs
    const quantityInputs = document.querySelectorAll('input[type="number"]');

    // Add an input event listener to each quantity input
    quantityInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');


            // Check if the value is less than the minimum or greater than the maximum
            const min = parseInt(this.getAttribute('min')) || 1;
            const max = parseInt(this.getAttribute('max')) || 100;

            if (this.value !== '' && (this.value < min || this.value > max)) {
                alert(`Quantity should be between ${min} and ${max}.`);
                this.value = min; // Reset to minimum if out of bounds
            }
        });
    });
});

        function filterMenuByCategory() {
            const categoryFilter = document.getElementById('categoryFilter').value;
            const allMenuItems = document.querySelectorAll('.menu-item');

            allMenuItems.forEach(item => {
                if (categoryFilter === 'all' || item.getAttribute('data-category') === categoryFilter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function incrementQuantity(menuId) {
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
            const quantityInput = document.getElementById('quantity_' + menuId);
            let currentQuantity = parseInt(quantityInput.value);

            if (currentQuantity > 1) {
                quantityInput.value = currentQuantity - 1;
            }
        }
        window.onload = function() {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 1000); // 1000 milliseconds = 1 second
        }
    };
    </script>
</head>
<body>
    <!-- Navigation Bar -->
        <header>
        <div class="home-container">
            <div class="logo">
                <img src="img/gellineslogoo.png" alt="Logo">
            </div>
            <button class="home-btn" onclick="window.location.href = 'home.php';">Home</button>
            <button class="ordr-btn" onclick="window.location.href = 'about.php';">About Us</button>
          

            <div class="dropdown">
                <button class="ordr-btn">Menus</button>
                <div class="dropdown-content">
                    <a href="menu.php">Food Menu</a>
                </div>
            </div>
   
            <div class="dropdown profile-dropdown">
                <button class="image-button">
                    <img src="img/user.png" alt="Profile Icon">
                </button>
                <div class="dropdown-content profile-dropdown-content">
                    <a href="my_orders.php">Orders</a>
                    <a href="my_reservations.php">Reservations</a>
                    <a href="order_history.php">Order History</a>
                    <a href="reservation_history.php">Reservation History</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

           
            <a class="image-button" href="cart.php">
                <img src="img/cart.png" alt="Button">
            </a>
        </div>
    </header>

   <!-- Main Menu Section -->
    <div class="main-menu">
        <h2>Gelline's Menu</h2>

        <!-- Display error message if any -->
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if ($success_message): ?>
    <p id="successMessage" style="color: black; background-color: #c8f7c5;width: 80%; padding: 10px; border-radius: 5px;">
        <?php echo $success_message; ?>
    </p>
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
                     style="width:150px; height:auto; border-radius:5px; margin-bottom:10px;">
            </center>
            <h3><?php echo $item['dish_name']; ?></h3>
            <p>Price: ₱<?php echo number_format($item['price'], 2); ?></p>

            <!-- Display Stock and Availability Status -->
            <?php if ($item['quantity'] == 0): ?>
                <p style="color: red;">Out of Stock</p>
            <?php elseif ($item['status'] === 'unavailable'): ?>
                <p style="color: red;">Unavailable</p>
            <?php else: ?>
                <p>Status: Available</p>

                <!-- Display the form to add to the cart if item is available and in stock -->
                <form method="post">
                    <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">

                    <div class="quantity-container">
                        <button type="button" class="decrement-btn" onclick="decrementQuantity(<?php echo $item['menu_id']; ?>)">-</button>
                        
                        <input type="number" id="quantity_<?php echo $item['menu_id']; ?>" 
                               name="quantity" value="1" min="1" 
                               max="<?php echo $item['quantity']; ?>" 
                               data-stock="<?php echo $item['quantity']; ?>">
                        
                        <button type="button" class="increment-btn" onclick="incrementQuantity(<?php echo $item['menu_id']; ?>)">+</button>
                    </div>

                    <!-- Add to Cart Button -->
                    <button type="submit" name="add_to_cart" class="cart-btn" onclick="return confirm('Are you sure you want to add this item to the cart?')">
                        <img src="img/add-cart.png" alt="Add to Cart" style="width: 40px; height: auto;">
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
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

<?php
session_start();
require 'dbconnection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = ''; // Store success messages
$errors = []; // Store validation errors

// Fetch items from the cart
$stmt = $pdo->prepare("
    SELECT ci.cart_item_id, m.menu_id, m.dish_name, ci.quantity AS quantity, m.price, (ci.quantity * m.price) AS total_price, ci.special_instructions
    FROM cart_items ci
    JOIN menu m ON ci.menu_id = m.menu_id
    WHERE ci.user_id = ?
");
$stmt->execute([$user_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter out items with a quantity of zero and calculate total price
$order_items = array_filter($order_items, function($item) {
    return $item['quantity'] > 0;
});

// Calculate the total cart price for items with non-zero quantity
$total_price = array_sum(array_column($order_items, 'total_price'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $update_cart_data = [];

    // Begin a transaction to ensure all operations succeed or fail together
    $pdo->beginTransaction();
    try {
        foreach ($_POST['cart_item_id'] as $index => $cart_item_id) {
            $new_quantity = $_POST['quantity'][$index];
            $special_instructions = $_POST['special_instructions'][$index];

            if ($new_quantity > 0) {
                // Update cart item quantities and special instructions
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, special_instructions = ? WHERE cart_item_id = ?");
                $stmt->execute([$new_quantity, $special_instructions, $cart_item_id]);
            } else {
                // If quantity is zero, remove the item from the cart
                $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
                $stmt->execute([$cart_item_id]);
            }
        }

        // Commit the transaction
        $pdo->commit();

        // Reload the page to reflect changes in the cart
        header('Location: cart.php');
        exit;

    } catch (Exception $e) {
        // Rollback if there's an error
        $pdo->rollBack();
        $errors[] = "Error updating cart: " . $e->getMessage();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $cart_item_id = $_POST['cart_item_id'];

    try {
        // Begin transaction for delete
        $pdo->beginTransaction();

        // Remove the cart item
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
        $stmt->execute([$cart_item_id]);

        // Commit the transaction
        $pdo->commit();

        // Reload the page after deletion
        header('Location: cart.php');
        exit;
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $pdo->rollBack();
        $errors[] = "Error deleting item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Gelline's Sizzling and Restaurant</title>
    <link rel="website icon" type="png" href="img/gellineslogoo.png">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/cart.css"> <!-- Link to external CSS -->
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

.home-btn, .ordr-btn, .lout-btn, .image-button {
    font-family: 'Rubik', sans-serif;
    background-color: transparent;
    border: none;
    color: black;
    padding: 5px 20px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    text-shadow: none;
    letter-spacing: 2px;
}

.home-btn:hover, .ordr-btn:hover, .lout-btn:hover, .image-button:hover {
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

.image-button {
    display: inline-block;
    border: none;
    padding: 0;
    background: none;
    cursor: pointer;
    margin-left: 20px;
}

.image-button img {
    width: 30px;
    height: auto;
}

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

.your-cart {
    margin-top: 140px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 80%;
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
}
.your-cart-content{
    max-width: 950px;
    width: 100%;
}
.your-cart-content h4 {
    text-align: center;
    margin-top: 120px;
    margin-bottom: 60px;
    color: rgba(0, 0, 0, 0.2);
}
.your-cart-content h2 {
    margin-bottom: 30px;
    font-size: 24px;
    letter-spacing: 1px;
    color: #333;
    text-align: center;
}
.your-cart-content p {
    margin-left: 80%;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.cart-table th, .cart-table td {
    padding: 15px;
    text-align: center;
    border: 1px solid #ddd;
    font-size: 16px;
}

.cart-table th {
    background-color: #ffdb44;
    color: black;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.cart-table td {
    font-size: 14px;
    color: #555;
    background-color: white;
}

.cart-input {
    width: 60px;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
    text-align: center;
}

.cart-table tr:hover {
    background-color: #f9f9f9;
}

.cart-btn, .delete-btn {
    background-color: #ffdb44;
    color: black;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
    margin: 10px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    background-image: linear-gradient(145deg, #ffdb44, #e2c43b);
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

.cart-btn:hover, .delete-btn:hover {
    background-color: #e2c43b;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.add-more-btn, .checkout-btn {
    background-color: #ffdb44;
    color: black;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    margin: 0;
    cursor: pointer;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}
.add-more-btn {
    margin-left: 50%;
}

.add-more-btn:hover, .checkout-btn:hover {
    background-color: #e2c43b;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

footer {
    background-color: black;
    color: white;
    padding: 15px;
    bottom: 0;
    width: 100%;
    box-sizing: border-box;
}

footer .footer-content {
    font-size: 13px;
    padding-left: 50px;
}

.cart-input[type="number"] {
    width: 60px;
    height: 40px;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
    text-align: center;
    box-sizing: border-box;
    font-size: 16px;
}

.cart-input[type="text"], .your-cart-content textarea {
    resize: none;
    width: 100%;
    height: 70px;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
    box-sizing: border-box;
    font-size: 16px;
}

.cart-table th {
    background-color: #ffdb44;
    color: black;
    padding: 12px 10px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

.cart-table td {
    padding: 12px 10px;
    text-align: center;
    vertical-align: middle;
}
    </style>
    <script>
    // Function to update the total price for a row
        function handleQuantityChange(index, cart_item_id) {
            const quantityInput = document.getElementById(`quantity_${index}`);
            const newQuantity = parseInt(quantityInput.value, 10);
            if (newQuantity < 1) {
                alert('Quantity cannot be less than 1');
                quantityInput.value = 1;
                return;
            } else if (newQuantity > 20) {
        alert('Quantity cannot be more than 20');
        quantityInput.value = 20;
        newQuantity = 20;
    }

            const specialInstructions = document.querySelector(`textarea[name="special_instructions[]"]`).value;

            const formData = new FormData();
            formData.append('cart_item_id', cart_item_id);
            formData.append('quantity', newQuantity);
            formData.append('special_instructions', specialInstructions);

            // Send the AJAX request to update the cart
            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update the total price for the row and grand total
                    updateRowTotal(index);
                } else {
                    console.error(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateRowTotal(index) {
            const quantity = document.getElementById(`quantity_${index}`).value;
            const price = document.getElementById(`price_${index}`).value;
            const total = quantity * price;

            // Update the row's total price
            document.getElementById(`row-total-${index}`).innerText = `₱${total.toFixed(2)}`;

            // Update the grand total
            updateTotalPrice();
        }

        function updateTotalPrice() {
            let grandTotal = 0;

            // Loop through all the total rows and sum them up
            document.querySelectorAll('[id^="row-total-"]').forEach(function(rowTotal) {
                const total = parseFloat(rowTotal.innerText.replace('₱', ''));
                grandTotal += total;
            });

            // Update the total price in the cart summary
            document.getElementById('total-price').innerText = `₱${grandTotal.toFixed(2)}`;
        }

        function submitCartForm() {
            document.getElementById('update_cart_form').submit();
        }

    </script>
</head>
<body>
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

    <div class="your-cart">
        <div class="your-cart-content">
            <h2>Your Cart</h2>

            <!-- Display errors if any -->
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (count($order_items) > 0): ?>
                <form method="post" id="update_cart_form">
    <table class="cart-table">
        <tr>    
            <th>Dish</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Special Instructions</th>
            <th>Total</th>
            <th></th>
        </tr>
        <?php foreach ($order_items as $index => $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['dish_name']); ?></td>
            <td>₱<?php echo number_format($item['price'], 2); ?></td>
            <td>
                <input type="number" class="cart-input" id="quantity_<?php echo $index; ?>" 
                       name="quantity[]" value="<?php echo $item['quantity']; ?>" min="0" 
                       oninput="handleQuantityChange(<?php echo $index; ?>, '<?php echo $item['cart_item_id']; ?>')">
                <input type="hidden" id="price_<?php echo $index; ?>" value="<?php echo $item['price']; ?>">
                <input type="hidden" name="cart_item_id[]" value="<?php echo $item['cart_item_id']; ?>">
            </td>
            <td>
                <textarea class="cart-input" name="special_instructions[]"><?php echo htmlspecialchars($item['special_instructions']); ?></textarea>
            </td>
            <td id="row-total-<?php echo $index; ?>">₱<?php echo number_format($item['total_price'], 2); ?></td>
            <td>
                <!-- Add delete button with confirmation -->
                <form method="post" class="delete-form">
                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                    <button class="delete-btn" type="submit" name="delete_item">Delete</button>
                </form>
            </td>
            <script>
    // Select all forms with the class 'delete-form'
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const confirmed = confirm("Are you sure you want to delete this item?");
            if (!confirmed) {
                // Prevent form submission if the user cancels the action
                event.preventDefault();
            }
        });
    });
</script>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><strong>Total Price: <span id="total-price">₱<?php echo number_format($total_price, 2); ?></span></strong></p>
</form>

                <button class="add-more-btn" type="button" onclick="window.location.href='menu.php';">Add More Items</button>

                <a href="checkout.php"><button class="checkout-btn" type="button" onclick="submitCartForm()">Review Payment and Address</button></a>

            <?php else: ?>
                <h4>Your cart is empty.</h4>
            <?php endif; ?>
        </div>
    </div>
  <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
  
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
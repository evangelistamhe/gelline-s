-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2024 at 05:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gellines_restaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`) VALUES
(1, 'admin@example.com', 'adminpassword');

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`) VALUES
(1, 1, '2024-10-03 01:02:12'),
(2, 12, '2024-10-04 01:51:09'),
(3, 13, '2024-10-06 01:46:05'),
(4, 14, '2024-10-06 02:07:00'),
(5, 15, '2024-10-06 20:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `special_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_item_id`, `cart_id`, `menu_id`, `quantity`, `special_instructions`) VALUES
(1, 1, 1, 1, NULL),
(21, 1, 16, 1, NULL),
(22, 1, 17, 1, NULL),
(24, 1, 15, 2, NULL),
(26, 1, 22, 1, NULL),
(35, 4, 19, 5, NULL),
(36, 3, 18, 2, ''),
(42, 5, 18, 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `dish_name` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `availability` enum('available','unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `dish_name`, `image`, `price`, `category_id`, `status`, `availability`) VALUES
(18, 'Beef Kare-Kare', 'img/menu/bkk.jpg', 300.00, 8, 'unavailable', 'available'),
(19, 'Crispy Pata', 'img/menu/pata.jpg', 850.00, 7, 'available', 'available'),
(20, 'Bistek Tagalog', '', 280.00, 8, 'available', 'available'),
(22, 'Chicken Curry', 'img/menu/curry.jpg', 250.00, 6, 'available', 'available'),
(23, 'Tempura', '', 90.00, 5, 'available', 'available'),
(24, 'Calamares', '', 90.00, 5, 'available', 'available'),
(25, 'Garlic Buttered-Shrimp', '', 260.00, 5, 'available', 'available'),
(26, 'Fried Fish Fillet', '', 260.00, 5, 'available', 'available'),
(27, 'Tapsilog', 'img/menu/tap.jpg', 260.00, 3, 'available', 'available'),
(28, 'Porksilog', 'img/menu/pork.jpg', 260.00, 3, 'available', 'available'),
(29, 'Liemsilog', 'img/menu/liem.jpg', 260.00, 3, 'available', 'available'),
(30, 'Chicksilog', 'img/menu/chik.jpg', 260.00, 3, 'available', 'available'),
(31, 'Sinigang na Baboy', 'img/menu/sb.jpg', 260.00, 7, 'available', 'available'),
(32, 'Beef Broccoli', '', 260.00, 8, 'available', 'available'),
(33, 'Nilagang Baboy', '', 260.00, 7, 'available', 'available'),
(34, 'Bulalo', '', 260.00, 8, 'available', 'available'),
(35, 'Papaitan', '', 260.00, 8, 'available', 'available'),
(36, 'Valenciana', '', 260.00, 6, 'available', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `menu_category`
--

CREATE TABLE `menu_category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_category`
--

INSERT INTO `menu_category` (`category_id`, `category_name`) VALUES
(1, 'Popular'),
(2, 'Appetizer'),
(3, 'Silog'),
(4, 'Sizzling w/ Rice'),
(5, 'Seafoods'),
(6, 'Chicken'),
(7, 'Pork'),
(8, 'Beef');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `order_delivery_brgy` varchar(50) NOT NULL,
  `order_delivery_street` varchar(255) NOT NULL,
  `order_delivery_city` varchar(50) NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_status` varchar(20) DEFAULT 'Pending',
  `note_to_rider` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `cart_id`, `order_delivery_brgy`, `order_delivery_street`, `order_delivery_city`, `landmark`, `total_amount`, `order_date`, `order_status`, `note_to_rider`) VALUES
(2, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:41:47', 'Pending', NULL),
(3, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:36', 'Pending', NULL),
(4, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:36', 'Pending', NULL),
(5, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:36', 'Pending', NULL),
(6, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:37', 'Pending', NULL),
(7, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:37', 'Pending', NULL),
(8, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:37', 'Pending', NULL),
(9, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:43:37', 'Pending', NULL),
(10, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:44:05', 'Pending', NULL),
(11, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:44:06', 'Pending', NULL),
(12, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:46:15', 'Pending', NULL),
(13, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:48:37', 'Pending', NULL),
(14, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:48:37', 'Pending', NULL),
(15, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:48:45', 'Pending', NULL),
(16, 12, 2, '', '', '', NULL, 2470.00, '2024-10-04 02:49:15', 'Pending', NULL),
(17, 12, 2, 'Poblacion', 'JP Rizal St', 'Santa Maria Bulacan', 'sa tapat ng mang thomas ', 3670.00, '2024-10-06 01:41:37', 'Processing', 'asddasd'),
(18, 12, 2, 'Camangyanan', 'Kamatis st', 'Santa Maria Bulacan', 'sa tapat ng mang thomas ', 300.00, '2024-10-06 01:36:21', 'Pending', 'dsadsa'),
(19, 12, 2, 'Poblacion', 'JP Rizal St', 'Santa Maria Bulacan', '', 3150.00, '2024-10-06 01:38:19', 'Pending', ''),
(20, 15, 5, 'Lapnit', '2m Gonzales st.', 'San Ildefonso', 'Jollibee', 2400.00, '2024-10-07 05:36:34', 'Pending', 'Mwa'),
(21, 15, 5, 'Lapnit', '4m', 'San Ildefonso', '', 201950.00, '2024-10-07 06:40:40', 'Pending', ''),
(22, 15, 5, 'Lapnit', '2m Gonzales st.', 'San Ildefonso', '', 851120.00, '2024-10-07 20:30:02', 'Order Completed', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_rider`
--

CREATE TABLE `order_rider` (
  `order_rider_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `note_to_rider` text DEFAULT NULL,
  `delivery_status` varchar(20) DEFAULT 'assigned',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `amount`, `payment_date`) VALUES
(1, 17, 'gcash', 'Pending', 3670.00, '2024-10-06 01:16:31'),
(2, 18, 'gcash', 'Pending', 300.00, '2024-10-06 01:36:21'),
(3, 19, 'gcash', 'Pending', 3150.00, '2024-10-06 01:38:19'),
(4, 20, 'cash_on_delivery', 'Pending', 2400.00, '2024-10-07 05:36:34'),
(5, 21, 'cash_on_delivery', 'Pending', 201950.00, '2024-10-07 06:40:40'),
(6, 22, 'cash_on_delivery', 'Pending', 851120.00, '2024-10-07 07:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `rider_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile_number` varchar(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gcash_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `mobile_number`, `created_at`, `gcash_number`) VALUES
(1, 'Alen', 'Osdana', 'alen@gmail.com', '$2y$10$qyjtdx7DjnMWnZ9haFNB2.NRVqjTXgA.8qj4uRZQP4TsXVvWw/872', '09164107856', '2024-10-02 23:31:43', NULL),
(2, 'Juan', 'Dela Cruz', 'juan.delacruz@gmail.com', 'password123', '09123456789', '2024-10-03 15:45:29', NULL),
(3, 'Maria', 'Santos', 'maria.santos@gmail.com', 'password123', '09123456780', '2024-10-03 15:45:29', NULL),
(4, 'Jose', 'Reyes', 'jose.reyes@gmail.com', 'password123', '09123456781', '2024-10-03 15:45:29', NULL),
(5, 'Ana', 'Garcia', 'ana.garcia@gmail.com', 'password123', '09123456782', '2024-10-03 15:45:29', NULL),
(6, 'Pedro', 'Lopez', 'pedro.lopez@gmail.com', 'password123', '09123456783', '2024-10-03 15:45:29', NULL),
(7, 'Catherine', 'Ramos', 'catherine.ramos@gmail.com', 'password123', '09123456784', '2024-10-03 15:45:29', NULL),
(8, 'Daniel', 'Torres', 'daniel.torres@gmail.com', 'password123', '09123456785', '2024-10-03 15:45:29', NULL),
(9, 'Patricia', 'Aquino', 'patricia.aquino@gmail.com', 'password123', '09123456786', '2024-10-03 15:45:29', NULL),
(10, 'Mark', 'Gonzalez', 'mark.gonzalez@gmail.com', 'password123', '09123456787', '2024-10-03 15:45:29', NULL),
(11, 'Rosa', 'Castillo', 'rosa.castillo@gmail.com', 'password123', '09123456788', '2024-10-03 15:45:29', NULL),
(12, 'Marc', 'Carmona', 'marc@gmail.com', '$2y$10$RTPBFM9JxvcrbNWZmaXe4.d6biWVSDmGkZU4mHRprDI5AAxqWahcK', '123123123', '2024-10-04 01:48:32', '09234156781'),
(13, 'Loise Jazmine', 'Cruz', 'loise@gmail.com', '$2y$10$p1buth8S5BSiY4Un9uM7G.3wtODlD90mS00wLgIJTIjEajZB25lvi', '09361562515', '2024-10-06 01:45:37', NULL),
(14, 'Made', 'Hipolito', 'made@gmail.com', '$2y$10$Zlx8jOjQj3hCDTF.gU7htujOQNUFfKggUx1K4zak6.HMYOBrG7ijO', '09133445561', '2024-10-06 02:04:35', '09234156781'),
(15, 'Marc Joseph', 'Carmona', 'carmonamj0323@gmail.com', '$2y$10$i9hFb8a0hqt20XARrTdfDuWtDMFM2kdaasfppZ/kEj/Q41xcI1oFi', '09612952593', '2024-10-06 20:03:46', '09164107856');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_brgy` varchar(255) NOT NULL,
  `user_street` varchar(255) NOT NULL,
  `user_city` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `user_id`, `user_brgy`, `user_street`, `user_city`) VALUES
(36, 12, 'Poblacion', 'JP Rizal St', 'Santa Maria Bulacan'),
(49, 12, 'Camangyanan', 'Kamatis st', 'Santa Maria Bulacan'),
(50, 12, 'Camangyanan', 'Kamatis st', 'Santa Maria Bulacan'),
(51, 12, 'Camangyanan', 'Kamatis st', 'Santa Maria Bulacan'),
(52, 12, 'Camangyanan', 'Kamatis st', 'Santa Maria Bulacan'),
(53, 12, 'Camangyanan', 'Kamatis st', 'Santa Maria Bulacan'),
(56, 14, 'Thomas Ohoy', 'Wakwakin st.', 'Santa Maria Bulacan'),
(58, 15, 'Lapnit', '2m Gonzales st.', 'San Ildefonso'),
(59, 15, 'Lapnit', '3m Gonzales st.', 'San Ildefonso'),
(66, 15, 'Lapnit', '4m', 'San Ildefonso'),
(68, 15, 'Lapnit', '5m', 'San Ildefonso');

-- --------------------------------------------------------

--
-- Table structure for table `user_feedback`
--

CREATE TABLE `user_feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `menu_category`
--
ALTER TABLE `menu_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_cart_id` (`cart_id`);

--
-- Indexes for table `order_rider`
--
ALTER TABLE `order_rider`
  ADD PRIMARY KEY (`order_rider_id`),
  ADD KEY `fk_order_id` (`order_id`),
  ADD KEY `fk_order_rider_rider_id` (`rider_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`rider_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_feedback`
--
ALTER TABLE `user_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `menu_category`
--
ALTER TABLE `menu_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_rider`
--
ALTER TABLE `order_rider`
  MODIFY `order_rider_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `rider_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `user_feedback`
--
ALTER TABLE `user_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `menu_category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_cart_id` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_rider`
--
ALTER TABLE `order_rider`
  ADD CONSTRAINT `fk_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_rider_rider_id` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`rider_id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_feedback`
--
ALTER TABLE `user_feedback`
  ADD CONSTRAINT `user_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

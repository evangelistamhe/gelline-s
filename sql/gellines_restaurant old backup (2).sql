-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2024 at 07:13 AM
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
(2, 12, '2024-10-04 01:51:09');

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
(27, 2, 22, 7, NULL),
(28, 2, 23, 8, NULL),
(29, 2, 18, 4, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `dish_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `availability` enum('available','unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `dish_name`, `price`, `category_id`, `status`, `availability`) VALUES
(18, 'Beef Kare-Kare', 300.00, 8, 'available', 'available'),
(19, 'Crispy Pata', 450.00, 7, 'available', 'available'),
(20, 'Bistek Tagalog', 200.00, 8, 'available', 'available'),
(22, 'Chicken Curry', 250.00, 6, 'available', 'available'),
(23, 'Hotdog', 90.00, 6, 'available', 'available'),
(24, 'Hotdog', 90.00, 6, 'available', 'available'),
(25, 'Sinigang na Baboy', 260.00, 7, 'available', 'available');

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
  `house_number` varchar(20) NOT NULL,
  `floor` varchar(10) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `delivery_option` varchar(20) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_status` varchar(20) DEFAULT 'Pending',
  `cart_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `house_number`, `floor`, `delivery_address`, `delivery_instructions`, `delivery_option`, `payment_method`, `total_amount`, `order_date`, `order_status`, `cart_id`) VALUES
(2, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:41:47', 'Pending', 2),
(3, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:36', 'Pending', 2),
(4, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:36', 'Pending', 2),
(5, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:36', 'Pending', 2),
(6, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:37', 'Pending', 2),
(7, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:37', 'Pending', 2),
(8, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:37', 'Pending', 2),
(9, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:43:37', 'Pending', 2),
(10, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:44:05', 'Pending', 2),
(11, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:44:06', 'Pending', 2),
(12, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:46:15', 'Pending', 2),
(13, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:48:37', 'Pending', 2),
(14, 12, '', NULL, 'asd', 'asd', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:48:37', 'Pending', 2),
(15, 12, '', NULL, 'dsa', 'dsa', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:48:45', 'Pending', 2),
(16, 12, '', NULL, 'ako', 'ako', 'Santa Maria Bulacan', 'gcash', 2470.00, '2024-10-04 02:49:15', 'Pending', 2);

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
  `delivery_address` text DEFAULT NULL,
  `gcash_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `mobile_number`, `created_at`, `delivery_address`, `gcash_number`) VALUES
(1, 'Alen', 'Osdana', 'alen@gmail.com', '$2y$10$qyjtdx7DjnMWnZ9haFNB2.NRVqjTXgA.8qj4uRZQP4TsXVvWw/872', '09164107856', '2024-10-02 23:31:43', 'SANTA MARIA', NULL),
(2, 'Juan', 'Dela Cruz', 'juan.delacruz@gmail.com', 'password123', '09123456789', '2024-10-03 15:45:29', NULL, NULL),
(3, 'Maria', 'Santos', 'maria.santos@gmail.com', 'password123', '09123456780', '2024-10-03 15:45:29', NULL, NULL),
(4, 'Jose', 'Reyes', 'jose.reyes@gmail.com', 'password123', '09123456781', '2024-10-03 15:45:29', NULL, NULL),
(5, 'Ana', 'Garcia', 'ana.garcia@gmail.com', 'password123', '09123456782', '2024-10-03 15:45:29', NULL, NULL),
(6, 'Pedro', 'Lopez', 'pedro.lopez@gmail.com', 'password123', '09123456783', '2024-10-03 15:45:29', NULL, NULL),
(7, 'Catherine', 'Ramos', 'catherine.ramos@gmail.com', 'password123', '09123456784', '2024-10-03 15:45:29', NULL, NULL),
(8, 'Daniel', 'Torres', 'daniel.torres@gmail.com', 'password123', '09123456785', '2024-10-03 15:45:29', NULL, NULL),
(9, 'Patricia', 'Aquino', 'patricia.aquino@gmail.com', 'password123', '09123456786', '2024-10-03 15:45:29', NULL, NULL),
(10, 'Mark', 'Gonzalez', 'mark.gonzalez@gmail.com', 'password123', '09123456787', '2024-10-03 15:45:29', NULL, NULL),
(11, 'Rosa', 'Castillo', 'rosa.castillo@gmail.com', 'password123', '09123456788', '2024-10-03 15:45:29', NULL, NULL),
(12, 'Marc', 'Carmona', 'marc@gmail.com', '$2y$10$T980mGFVrwGn/Ruyiwky7.YzkNSAfeXdVUaLZXSbOze1DnPu3NBg6', '12312312332', '2024-10-04 01:48:32', NULL, '09234156781');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `house_number` varchar(20) NOT NULL,
  `floor` varchar(10) DEFAULT NULL,
  `note_to_rider` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `user_id`, `address`, `house_number`, `floor`, `note_to_rider`) VALUES
(5, 12, 'iloveu', '123', '4', NULL),
(6, 12, 'asd', 'asd', 'asd', NULL),
(7, 12, 'asd', 'asd', 'asd', NULL),
(8, 12, 'asd', 'asd', 'asd', NULL),
(9, 12, 'asd', 'asd', 'asd', NULL),
(10, 12, 'asd', 'asd', 'asd', NULL),
(11, 12, 'asd', 'asd', 'asd', NULL),
(12, 12, 'asd', '123', '123', NULL),
(13, 12, 'asd', '123', '123', NULL),
(14, 12, 'asd', '123', '123', NULL),
(15, 12, 'asd', '123', '123', NULL),
(16, 12, 'asd', '123', '123', NULL),
(17, 12, 'asd', '123', '123', NULL);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `menu_category`
--
ALTER TABLE `menu_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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

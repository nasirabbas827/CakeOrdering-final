-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2026 at 08:33 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cake_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `cakes`
--

CREATE TABLE `cakes` (
  `cake_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cakes`
--

INSERT INTO `cakes` (`cake_id`, `name`, `price`, `image`, `description`, `category_id`, `is_available`) VALUES
(1, 'Dark Chocolate Cake', 1500.00, 'uploads/cake.jpg', 'Rich dark chocolate cake with creamy frosting.', 1, 1),
(2, 'Chocolate Truffle Cake', 1800.00, 'uploads/cake.jpg', 'Decadent chocolate truffle layers with ganache.', 1, 1),
(3, 'Chocolate Fudge Cake', 1600.00, 'uploads/cake.jpg', 'Moist chocolate fudge cake perfect for any occasion.', 1, 1),
(4, 'Classic Vanilla Cake', 1200.00, 'uploads/cake.jpg', 'Soft and fluffy vanilla cake with buttercream.', 2, 1),
(5, 'Vanilla Almond Cake', 1400.00, 'uploads/cake.jpg', 'Vanilla cake with almond essence and cream.', 2, 1),
(6, 'Vanilla Strawberry Cake', 1500.00, 'uploads/cake.jpg', 'Vanilla sponge cake with strawberry filling.', 2, 1),
(7, 'Mixed Fruit Cake', 2000.00, 'uploads/cake.jpg', 'Loaded with dried fruits and nuts.', 3, 1),
(8, 'Pineapple Cake', 1800.00, 'uploads/cake.jpg', 'Tangy pineapple layered cake.', 3, 1),
(9, 'Apple Cinnamon Cake', 1700.00, 'uploads/cake.jpg', 'Apple chunks with cinnamon flavor.', 3, 1),
(10, 'Classic Cheesecake', 2200.00, 'uploads/cake.jpg', 'Smooth creamy cheesecake with graham crust.', 4, 1),
(11, 'Blueberry Cheesecake', 2400.00, 'uploads/cake.jpg', 'Cheesecake topped with fresh blueberries.', 4, 1),
(12, 'Chocolate Cheesecake', 2500.00, 'uploads/cake.jpg', 'Rich chocolate cheesecake with ganache.', 4, 1),
(13, 'Chocolate Cupcake', 300.00, 'uploads/cake.jpg', 'Mini chocolate cake with frosting.', 5, 1),
(14, 'Vanilla Cupcake', 280.00, 'uploads/cake.jpg', 'Classic vanilla cupcake with cream.', 5, 1),
(15, 'Red Velvet Cupcake', 350.00, 'uploads/cake.jpg', 'Soft red velvet with cream cheese frosting.', 5, 1),
(16, 'Happy Birthday Cake', 2000.00, 'uploads/cake.jpg', 'Perfect birthday cake with colorful decorations.', 6, 1),
(17, 'Rainbow Birthday Cake', 2500.00, 'uploads/cake.jpg', 'Vibrant multi-layered birthday cake.', 6, 1),
(18, 'Chocolate Birthday Cake', 2200.00, 'uploads/cake.jpg', 'Chocolate cake with birthday candles.', 6, 1),
(19, 'Classic Wedding Cake', 5000.00, 'uploads/cake.jpg', 'Elegant multi-tier wedding cake.', 7, 1),
(20, 'Vanilla Wedding Cake', 5200.00, 'uploads/cake.jpg', 'Vanilla sponge with fondant decoration.', 7, 1),
(21, 'Chocolate Wedding Cake', 5400.00, 'uploads/cake.jpg', 'Rich chocolate wedding cake for celebrations.', 7, 1),
(22, 'Chocolate Ice Cream Cake', 1800.00, 'uploads/cake.jpg', 'Chocolate cake with ice cream layers.', 8, 1),
(23, 'Vanilla Ice Cream Cake', 1700.00, 'uploads/cake.jpg', 'Vanilla ice cream cake with cream topping.', 8, 1),
(24, 'Strawberry Ice Cream Cake', 1900.00, 'uploads/cake.jpg', 'Strawberry flavored ice cream cake.', 8, 1),
(25, 'Eggless Chocolate Cake', 1600.00, 'uploads/cake.jpg', 'Soft chocolate cake without eggs.', 9, 1),
(26, 'Eggless Vanilla Cake', 1500.00, 'uploads/cake.jpg', 'Fluffy eggless vanilla cake.', 9, 1),
(27, 'Eggless Fruit Cake', 1700.00, 'uploads/cake.jpg', 'Fruit cake prepared without eggs.', 9, 1),
(28, 'Red Velvet Cake', 2000.00, 'uploads/cake.jpg', 'Classic red velvet cake with cream cheese frosting.', 10, 1),
(29, 'Black Forest Cake', 2200.00, 'uploads/cake.jpg', 'Chocolate cake with cherries and cream.', 10, 1),
(30, 'Tiramisu Cake', 2500.00, 'uploads/cake.jpg', 'Coffee-flavored Italian specialty cake.', 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cake_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Chocolate Cakes'),
(2, 'Vanilla Cakes'),
(3, 'Fruit Cakes'),
(4, 'Cheesecakes'),
(5, 'Cupcakes'),
(6, 'Birthday Cakes'),
(7, 'Wedding Cakes'),
(8, 'Ice Cream Cakes'),
(9, 'Eggless Cakes'),
(10, 'Specialty Cakes');

-- --------------------------------------------------------

--
-- Table structure for table `custom_requests`
--

CREATE TABLE `custom_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `design_details` text NOT NULL,
  `status` enum('pending','accepted','suggested_modifications','rejected') DEFAULT 'pending',
  `admin_feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_requests`
--

INSERT INTO `custom_requests` (`request_id`, `user_id`, `design_details`, `status`, `admin_feedback`, `created_at`) VALUES
(1, 3, 'XYZ', 'accepted', 'Okay', '2026-02-14 07:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `expense_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('processing','confirmed','dispatched','delivered','rejected') DEFAULT 'processing',
  `estimated_time` varchar(255) DEFAULT NULL,
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `status`, `estimated_time`, `payment_status`, `name`, `address`, `phone`, `created_at`) VALUES
(1, 3, 1800.00, 'processing', '45-60 minutes', 'unpaid', 'NASIR ABBAS', 'P/o Basti Chhina Tehsil jampur District Rajanpur\r\nP/o Basti Chhina Tehsil jampur District Rajanpur', '9853', '2026-02-14 07:28:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cake_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `cake_id`, `quantity`, `price`) VALUES
(1, 1, 2, 1, 1800.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment_vouchers`
--

CREATE TABLE `payment_vouchers` (
  `voucher_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `age`, `full_name`, `bio`) VALUES
(3, 'nasir', '$2y$10$a/SgsGBswoo1YfNrJd./p.Jqey.MDYC.QoLsyJGas3H5cdesUPvjq', 'nasiryt.827@gmail.com', '9853', 23, 'NASIR ABBAS', 'ds');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cakes`
--
ALTER TABLE `cakes`
  ADD PRIMARY KEY (`cake_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cake_id` (`cake_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_requests`
--
ALTER TABLE `custom_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cake_id` (`cake_id`);

--
-- Indexes for table `payment_vouchers`
--
ALTER TABLE `payment_vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cakes`
--
ALTER TABLE `cakes`
  MODIFY `cake_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `custom_requests`
--
ALTER TABLE `custom_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_vouchers`
--
ALTER TABLE `payment_vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cakes`
--
ALTER TABLE `cakes`
  ADD CONSTRAINT `cakes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`cake_id`) REFERENCES `cakes` (`cake_id`);

--
-- Constraints for table `custom_requests`
--
ALTER TABLE `custom_requests`
  ADD CONSTRAINT `custom_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`cake_id`) REFERENCES `cakes` (`cake_id`);

--
-- Constraints for table `payment_vouchers`
--
ALTER TABLE `payment_vouchers`
  ADD CONSTRAINT `payment_vouchers_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

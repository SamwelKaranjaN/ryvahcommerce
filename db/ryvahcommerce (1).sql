-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 12, 2025 at 05:45 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ryvahcommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(58, 19, 7, 1, '2025-05-11 15:04:16', '2025-05-11 15:04:16'),
(57, 20, 3, 1, '2025-05-11 14:56:51', '2025-05-11 14:56:51'),
(56, 13, 3, 1, '2025-05-11 14:55:43', '2025-05-11 14:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `loyalty_points` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('pending','processing','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_address` text,
  `billing_address` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_date` (`order_date`),
  KEY `payment_status` (`payment_status`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_amount`, `payment_status`, `payment_method`, `shipping_address`, `billing_address`, `notes`, `created_at`, `updated_at`) VALUES
(59, 20, '2025-05-11 16:01:53', 5.99, 'pending', NULL, '12345, qqqq, qqq 12345, qqqq, qqq 12345, Phone: 0793715233', NULL, NULL, '2025-05-11 13:01:53', '2025-05-11 13:01:53'),
(58, 20, '2025-05-11 15:38:16', 5.99, 'pending', NULL, '12345, tertre, trtrt 87888, tertre, trtrt 87888, Phone: 0793715233', NULL, NULL, '2025-05-11 12:38:16', '2025-05-11 12:38:16'),
(57, 20, '2025-05-11 15:25:56', 11.99, 'pending', NULL, '12345, rrrrrrrrrrrr, rrrrrrrrr 44444, rrrrrrrrrrrr, rrrrrrrrr 44444, Phone: 0793715233', NULL, NULL, '2025-05-11 12:25:56', '2025-05-11 12:25:56'),
(56, 20, '2025-05-11 14:49:06', 0.01, 'pending', NULL, '11111111, sfdsfd, gfdg 11111, sfdsfd, gfdg 11111, Phone: 0793715233', NULL, NULL, '2025-05-11 11:49:06', '2025-05-11 11:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(73, 59, 3, 1, 5.99, 0.00, '2025-05-11 13:01:53', '2025-05-11 13:01:53'),
(72, 58, 3, 1, 5.99, 0.00, '2025-05-11 12:38:16', '2025-05-11 12:38:16'),
(71, 57, 4, 1, 11.99, 0.00, '2025-05-11 12:25:56', '2025-05-11 12:25:56'),
(70, 56, 12, 1, 0.01, 0.00, '2025-05-11 11:49:06', '2025-05-11 11:49:06'),
(69, 55, 6, 1, 14.50, 0.00, '2025-05-11 11:37:38', '2025-05-11 11:37:38'),
(68, 54, 7, 1, 9.99, 0.00, '2025-05-11 11:25:08', '2025-05-11 11:25:08'),
(67, 53, 7, 1, 9.99, 0.00, '2025-05-11 11:21:40', '2025-05-11 11:21:40'),
(66, 52, 9, 1, 12.30, 0.00, '2025-05-11 10:59:48', '2025-05-11 10:59:48'),
(65, 52, 7, 1, 9.99, 0.00, '2025-05-11 10:59:48', '2025-05-11 10:59:48'),
(64, 51, 3, 1, 5.99, 0.00, '2025-05-11 10:57:35', '2025-05-11 10:57:35'),
(63, 51, 2, 1, 7.49, 0.00, '2025-05-11 10:57:35', '2025-05-11 10:57:35'),
(62, 50, 4, 1, 11.99, 0.00, '2025-05-11 10:34:42', '2025-05-11 10:34:42'),
(61, 49, 3, 4, 5.99, 0.00, '2025-05-11 10:33:19', '2025-05-11 10:33:19'),
(60, 48, 3, 1, 5.99, 0.00, '2025-05-11 10:19:18', '2025-05-11 10:19:18'),
(59, 47, 2, 1, 7.49, 0.00, '2025-05-11 09:42:43', '2025-05-11 09:42:43'),
(58, 46, 4, 1, 11.99, 0.00, '2025-05-11 09:39:35', '2025-05-11 09:39:35'),
(57, 45, 3, 1, 5.99, 0.00, '2025-05-11 09:34:40', '2025-05-11 09:34:40'),
(56, 44, 3, 2, 5.99, 0.00, '2025-05-11 09:31:36', '2025-05-11 09:31:36'),
(55, 43, 2, 1, 7.49, 0.00, '2025-05-11 09:30:44', '2025-05-11 09:30:44'),
(54, 42, 3, 2, 5.99, 0.00, '2025-05-11 09:28:13', '2025-05-11 09:28:13'),
(53, 41, 7, 1, 9.99, 0.00, '2025-05-11 09:25:38', '2025-05-11 09:25:38'),
(52, 41, 5, 1, 4.99, 0.00, '2025-05-11 09:25:38', '2025-05-11 09:25:38'),
(51, 41, 3, 1, 5.99, 0.00, '2025-05-11 09:25:38', '2025-05-11 09:25:38'),
(50, 40, 3, 2, 5.99, 0.00, '2025-05-11 09:02:58', '2025-05-11 09:02:58'),
(49, 40, 2, 1, 7.49, 0.00, '2025-05-11 09:02:58', '2025-05-11 09:02:58'),
(48, 39, 3, 1, 5.99, 0.00, '2025-05-11 09:00:35', '2025-05-11 09:00:35'),
(47, 38, 4, 1, 11.99, 0.00, '2025-05-11 08:57:55', '2025-05-11 08:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `order_payments`
--

DROP TABLE IF EXISTS `order_payments`;
CREATE TABLE IF NOT EXISTS `order_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` enum('pending','processing','completed','failed','refunded') NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('paint','ebook') NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `author` varchar(60) NOT NULL,
  `filepath` varchar(200) NOT NULL,
  `file_size` varchar(60) NOT NULL,
  `thumbs` varchar(80) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int DEFAULT NULL,
  `description` text NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `stock` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `updated_by` (`timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `type`, `sku`, `name`, `author`, `filepath`, `file_size`, `thumbs`, `price`, `stock_quantity`, `description`, `timestamp`, `updated_at`, `stock`) VALUES
(12, 'paint', '222222222222', '@Sammy_skn', 'Jane Doe', 'Uploads/pdfs/PROVISIONAL RESULTS-BUS-241-1952024.pdf', '178.13 KB', 'Uploads/thumbs/image.png', 0.01, 221, '2222', '2025-05-09 09:24:35', '2025-05-11 11:49:06', 0),
(2, 'ebook', 'EBK002', 'JavaScript for Beginners', 'Jane Smith', 'files/ebooks/js_beginners.pdf', '3.5MB', 'thumbs/js_beginners.jpg', 7.49, 92, 'Learn JavaScript basics in a simplified and effective way.', '2025-05-09 05:55:13', '2025-05-11 10:57:35', 0),
(3, 'ebook', 'EBK003', 'The CSS Handbook', 'Alan White', 'files/ebooks/css_handbook.pdf', '2.8MB', 'thumbs/css_handbook.jpg', 5.99, 39, 'A practical guide to mastering CSS for web design.', '2025-05-09 05:55:13', '2025-05-11 13:01:53', 0),
(4, 'ebook', 'EBK004', 'React Essentials', 'Mary Johnson', 'files/ebooks/react_essentials.pdf', '6.1MB', 'thumbs/react_essentials.jpg', 11.99, 20, 'An essential resource for React developers.', '2025-05-09 05:55:13', '2025-05-11 12:25:56', 0),
(5, 'ebook', 'EBK005', 'Linux Commands Guide', 'Robert Black', 'files/ebooks/linux_commands.pdf', '1.9MB', 'thumbs/linux_commands.jpg', 4.99, 199, 'Quick reference to essential Linux commands.', '2025-05-09 05:55:13', '2025-05-11 09:25:38', 0),
(6, 'paint', 'PNT001', 'Sunset Orange - 1L', 'ColorCo Ltd.', 'files/paints/sunset_orange.jpg', '1.2MB', 'thumbs/sunset_orange.jpg', 14.50, 49, 'High-quality acrylic paint with a vibrant orange tone.', '2025-05-09 05:55:13', '2025-05-11 11:37:38', 0),
(7, 'ebook', 'PNT002', 'Ocean Blue - 5', 'AquaPaints Inc.', 'files/paints/ocean_blue.jpg', '1.4MB', 'thumbs/ocean_blue.jpg', 9.90, 21, 'Premium paint ideal for exterior walls and marine use.', '2025-05-09 05:55:13', '2025-05-11 15:26:48', 0);

-- --------------------------------------------------------

--
-- Table structure for table `saved_items`
--

DROP TABLE IF EXISTS `saved_items`;
CREATE TABLE IF NOT EXISTS `saved_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('Admin','Client','Employee') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Client',
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `encryption_key` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `iv` varchar(255) NOT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `role`, `password`, `phone`, `encryption_key`, `salt`, `iv`, `address`, `created_at`) VALUES
(20, 'Jane Ngari Atieno', 'janengari3467@gmail.com', 'Admin', '$2y$10$7LnHacncUUXL5pSvSOKiSOTdQ46aJ/sHTnZ39/31C7ZAIHdnEr62u', '0793715233', '758cf263ff7035e40aa8c85aabcf2332444df4cfe1d6781eb39894b68b504171', '8467cc387e1ca3ebce299bbb739a2e28', '', '12345', '2025-05-11 09:46:54'),
(19, 'Samwel Karanja Njoroge', 'samwelnjoroge757@gmail.com', 'Client', '$2y$10$iwnYoFu2Srkkc7BMUW/EauhEvR/piq4Wb8HYhfcqbsG.yqaJsidty', '0793878068', 'd7ccfa11c3a6304bca0ac2f5ee2291c6edf01931deb3d70ec69ae2c379a9b7c9', '00895509daea96f301d6e1bcb5071128', '', '12345', '2025-05-11 05:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

DROP TABLE IF EXISTS `user_logs`;
CREATE TABLE IF NOT EXISTS `user_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `session_duration` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `login_time` (`login_time`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `email`, `phone`, `ip_address`, `login_time`, `logout_time`, `session_duration`, `created_at`) VALUES
(18, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-12 08:39:04', NULL, NULL, '2025-05-12 05:39:04'),
(17, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-11 18:07:10', NULL, NULL, '2025-05-11 15:07:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_purchases`
--

DROP TABLE IF EXISTS `user_purchases`;
CREATE TABLE IF NOT EXISTS `user_purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `order_id` int NOT NULL,
  `purchase_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `download_count` int NOT NULL DEFAULT '0',
  `last_download` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product_order` (`user_id`,`product_id`,`order_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE IF NOT EXISTS `warehouses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `location`) VALUES
(1, 'Main Warehouse', 'New York'),
(2, 'Main Warehouse', 'New York');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

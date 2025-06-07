-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 07, 2025 at 09:24 AM
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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(180) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `label` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Home',
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'N/A',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `label`, `street`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 19, 'Samwel', 'hyt', 'yyty', 'New York', 'NY', '343', 'US', 1, '2025-05-30 12:37:54', '2025-06-01 17:52:03'),
(2, 20, NULL, 'HOME', '1264-Thika', 'Thika', 'EMBU', '01000', 'Other', 0, '2025-06-01 19:29:50', '2025-06-01 19:29:50'),
(4, 43, NULL, 'my home address', '4791 Myra Street Newport', 'Newport', 'RI', '02840', 'United States', 0, '2025-06-05 16:25:23', '2025-06-05 16:25:23'),
(5, 43, NULL, 'my home address', '4791 Myra Street Newport', 'Newport', 'RI', '02840', 'United States', 1, '2025-06-05 16:25:23', '2025-06-05 16:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `details`, `created_at`) VALUES
(1, 20, 'update_tax', '{\"product_type\":\"paint\",\"old_rate\":\"7.75\",\"new_rate\":\"7.77\",\"is_active\":1}', '2025-05-29 15:29:05'),
(2, 20, 'update_tax', '{\"product_type\":\"ebook\",\"old_rate\":\"7.75\",\"new_rate\":\"7.75\",\"is_active\":1}', '2025-05-29 15:29:05'),
(3, 20, 'update_tax', '{\"product_type\":\"paint\",\"old_rate\":\"7.75\",\"new_rate\":\"7.77\",\"is_active\":1}', '2025-05-29 15:29:51'),
(4, 20, 'update_tax', '{\"product_type\":\"ebook\",\"old_rate\":\"7.75\",\"new_rate\":\"7.75\",\"is_active\":1}', '2025-05-29 15:29:51'),
(5, 20, 'update_tax', '{\"product_type\":\"paint\",\"old_rate\":\"7.75\",\"new_rate\":\"7.77\",\"is_active\":1}', '2025-05-29 15:30:01'),
(6, 20, 'update_tax', '{\"product_type\":\"ebook\",\"old_rate\":\"7.75\",\"new_rate\":\"7.75\",\"is_active\":1}', '2025-05-29 15:30:01'),
(7, 20, 'update_tax', '{\"product_type\":\"paint\",\"old_rate\":\"7.77\",\"new_rate\":\"7.75\",\"is_active\":1}', '2025-05-29 15:30:11'),
(8, 20, 'update_tax', '{\"product_type\":\"ebook\",\"old_rate\":\"7.75\",\"new_rate\":\"7.75\",\"is_active\":1}', '2025-05-29 15:30:11'),
(9, 20, 'update_tax', '{\"product_type\":\"paint\",\"old_rate\":\"7.77\",\"new_rate\":\"7.75\",\"is_active\":1}', '2025-05-29 15:32:57'),
(10, 20, 'add_tax', '{\"product_type\":\"book\",\"tax_rate\":7.75}', '2025-05-29 15:33:24'),
(11, 20, 'delete_tax', '{\"product_type\":\"book\"}', '2025-05-29 15:41:02'),
(12, 20, 'add_tax', '{\"product_type\":\"book\",\"tax_rate\":7.75}', '2025-05-29 15:41:20'),
(13, 20, 'update_tax_status', '{\"product_type\":\"book\",\"is_active\":\"0\"}', '2025-05-29 15:41:36'),
(14, 20, 'update_tax_status', '{\"product_type\":\"book\",\"is_active\":\"1\"}', '2025-05-29 15:41:42'),
(15, 20, 'update_tax_status', '{\"product_type\":\"ebook\",\"is_active\":\"0\"}', '2025-06-06 13:28:48'),
(16, 20, 'delete_shipping_fee', '{\"product_type\":\"ebook\"}', '2025-06-06 16:25:37');

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
) ENGINE=MyISAM AUTO_INCREMENT=186 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(105, 32, 7, 1, '2025-05-27 19:08:07', '2025-05-27 19:08:07'),
(168, 42, 7, 1, '2025-06-05 16:24:15', '2025-06-05 16:24:15'),
(164, 43, 12, 8, '2025-06-05 14:35:18', '2025-06-05 14:35:31'),
(185, 20, 12, 2, '2025-06-07 08:38:48', '2025-06-07 08:38:48');

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
-- Table structure for table `ebook_downloads`
--

DROP TABLE IF EXISTS `ebook_downloads`;
CREATE TABLE IF NOT EXISTS `ebook_downloads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `download_token` varchar(255) NOT NULL,
  `download_count` int NOT NULL DEFAULT '0',
  `max_downloads` int NOT NULL DEFAULT '3',
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `download_token` (`download_token`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ebook_downloads`
--

INSERT INTO `ebook_downloads` (`id`, `user_id`, `order_id`, `product_id`, `download_token`, `download_count`, `max_downloads`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 19, 71, 7, '651', 0, 5, '2025-07-04 06:24:53', '2025-06-04 06:24:53', '2025-06-04 06:24:53'),
(2, 19, 72, 16, '0', 0, 5, '2025-07-04 06:49:03', '2025-06-04 06:49:03', '2025-06-04 06:49:03'),
(3, 19, 75, 7, 'c03c182729751d37463bf32215e118c0ec66f72c4934f225b1317a8ab78259d5', 0, 5, '2025-07-05 16:39:20', '2025-06-05 16:39:20', '2025-06-05 16:39:20'),
(4, 19, 76, 7, 'b4982f656cdc8ffad57903a9acfb2a047ffa179345a0978b083c08e976f25448', 0, 5, '2025-07-05 16:41:45', '2025-06-05 16:41:45', '2025-06-05 16:41:45'),
(5, 19, 77, 7, 'bde460fb89cec4f404f84685e2457dfb2677b6cf68d4be7ded9a86caa3874ad1', 0, 5, '2025-07-05 19:09:33', '2025-06-05 19:09:33', '2025-06-05 19:09:33'),
(6, 19, 78, 7, 'ffc7d12a0839fa673f0b9506a7fd8a37000c6556d4fd49f348c8e71f0c5a09a5', 0, 5, '2025-07-06 08:19:49', '2025-06-06 08:19:49', '2025-06-06 08:19:49'),
(7, 19, 79, 7, '6497bc78166625e5a4290133d626af61587a1bfbfad667841bbcccc0163c77c2', 0, 5, '2025-07-06 08:36:25', '2025-06-06 08:36:25', '2025-06-06 08:36:25'),
(8, 19, 80, 7, '2a3a769833edb2f46079b07a7d790c747fbd566a96b16e495dd05191411866fa', 0, 5, '2025-07-06 11:11:00', '2025-06-06 11:11:00', '2025-06-06 11:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `pdf_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_number` (`invoice_number`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('pending','processing','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `paypal_order_id` varchar(255) DEFAULT NULL,
  `shipping_address` text,
  `billing_address` text,
  `shipping_method` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `user_id` (`user_id`),
  KEY `order_date` (`order_date`),
  KEY `payment_status` (`payment_status`),
  KEY `idx_orders_invoice` (`invoice_number`),
  KEY `idx_paypal_order_id` (`paypal_order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice_number`, `user_id`, `order_date`, `total_amount`, `tax_amount`, `shipping_amount`, `tax_rate`, `payment_status`, `payment_method`, `paypal_order_id`, `shipping_address`, `billing_address`, `shipping_method`, `notes`, `created_at`, `updated_at`, `currency`) VALUES
(66, '', 19, '2025-05-16 10:52:43', 11.99, 0.00, 0.00, 0.00, 'failed', NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-16 07:52:43', '2025-05-21 12:17:51', 'USD'),
(67, 'INV-20250601-0019-72', 19, '2025-06-01 13:44:20', 12.93, 0.93, 0.00, 0.00, 'completed', NULL, 'MOCK_ORDER_683c2f0484e65_1748774660', NULL, NULL, NULL, NULL, '2025-06-01 10:44:20', '2025-06-01 10:44:29', 'USD'),
(68, 'INV-20250601-0019-57', 19, '2025-06-01 13:45:26', 1.62, 0.12, 0.00, 0.00, 'pending', NULL, 'MOCK_ORDER_683c2f466b180_1748774726', NULL, NULL, NULL, NULL, '2025-06-01 10:45:26', '2025-06-01 10:45:26', 'USD'),
(69, 'INV-20250604-19-4980', 19, '2025-06-04 06:18:47', 0.01, 0.00, 0.00, 0.00, 'pending', NULL, 'MOCK_ORDER_1749017927_2256', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:18:47', '2025-06-04 06:18:47', 'USD'),
(70, 'INV-20250604-19-2022', 19, '2025-06-04 06:23:28', 0.01, 0.00, 0.00, 0.00, 'pending', NULL, 'MOCK_ORDER_1749018208_4236', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:23:28', '2025-06-04 06:23:28', 'USD'),
(71, 'INV-20250604-19-3928', 19, '2025-06-04 06:24:51', 0.01, 0.00, 0.00, 0.00, 'completed', NULL, 'MOCK_ORDER_1749018291_4033', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:24:51', '2025-06-04 06:24:53', 'USD'),
(72, 'INV-20250604-19-6838', 19, '2025-06-04 06:49:02', 1.65, 0.15, 0.00, 0.00, 'completed', NULL, 'FALLBACK_ORDER_1749019742_5242', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:49:02', '2025-06-04 06:49:03', 'USD'),
(73, 'INV-20250604-19-2612', 19, '2025-06-04 07:31:10', 13.19, 1.20, 0.00, 0.00, 'completed', NULL, 'FALLBACK_ORDER_1749022270_1136', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 07:31:10', '2025-06-04 07:31:11', 'USD'),
(74, 'INV-20250604-19-7483', 19, '2025-06-04 07:37:48', 15.95, 1.45, 0.00, 0.00, 'completed', NULL, 'FALLBACK_ORDER_1749022668_9769', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 07:37:48', '2025-06-04 07:37:49', 'USD'),
(75, 'RYV-20250605-19-25A3', 19, '2025-06-05 16:39:20', 0.01, 0.00, 0.00, 0.00, 'processing', 'paypal', '4RA53855HJ952423Y', '{\"id\":1,\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\"}', NULL, NULL, NULL, '2025-06-05 16:39:20', '2025-06-06 13:09:15', 'USD'),
(76, 'RYV-20250605-19-8E02', 19, '2025-06-05 16:41:45', 0.01, 0.00, 0.00, 0.00, 'completed', 'paypal', '6GX47511TG378831H', '{\"id\":1,\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\"}', NULL, NULL, NULL, '2025-06-05 16:41:45', '2025-06-05 16:44:03', 'USD'),
(77, 'RYV-20250605-19-4D52', 19, '2025-06-05 19:09:33', 0.01, 0.00, 0.00, 0.00, 'completed', 'paypal', '5SX91986PB135062W', '{\"id\":1,\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\"}', NULL, NULL, NULL, '2025-06-05 19:09:33', '2025-06-05 19:10:24', 'USD'),
(78, 'RYV-20250606-19-6820', 19, '2025-06-06 08:19:49', 0.01, 0.00, 0.00, 0.00, 'completed', 'paypal', '0C59362061454500J', '{\"id\":1,\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\"}', NULL, NULL, NULL, '2025-06-06 08:19:49', '2025-06-06 08:22:00', 'USD'),
(79, 'RYV-20250606-19-6295', 19, '2025-06-06 08:36:25', 0.01, 0.00, 0.00, 0.00, 'completed', 'paypal', '41274974CW5173539', '{\"id\":1,\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\"}', NULL, NULL, NULL, '2025-06-06 08:36:25', '2025-06-06 08:37:33', 'USD'),
(80, 'RYV-20250606-19-7EC0', 19, '2025-06-06 11:11:00', 0.01, 0.00, 0.00, 0.00, 'completed', 'paypal', '5BG3501527143770T', '{\"id\":1,\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\"}', NULL, NULL, NULL, '2025-06-06 11:11:00', '2025-06-06 11:11:40', 'USD');

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
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `tax_amount`, `created_at`, `updated_at`) VALUES
(80, 66, 4, 1, 11.99, 11.99, 0.00, '2025-05-16 07:52:43', '2025-05-16 07:52:43'),
(79, 65, 4, 1, 11.99, 11.99, 0.00, '2025-05-16 07:44:36', '2025-05-16 07:44:36'),
(78, 64, 4, 9, 11.99, 107.91, 0.00, '2025-05-16 06:49:38', '2025-05-16 06:49:38'),
(77, 63, 4, 9, 11.99, 107.91, 0.00, '2025-05-16 06:32:11', '2025-05-16 06:32:11'),
(81, 67, 4, 1, 11.99, 11.99, 0.93, '2025-06-01 10:44:20', '2025-06-01 10:44:20'),
(82, 67, 7, 1, 0.01, 0.01, 0.00, '2025-06-01 10:44:20', '2025-06-01 10:44:20'),
(83, 68, 12, 1, 1.50, 1.50, 0.12, '2025-06-01 10:45:26', '2025-06-01 10:45:26'),
(84, 69, 7, 1, 0.01, 0.01, 0.00, '2025-06-04 06:18:47', '2025-06-04 06:18:47'),
(85, 70, 7, 1, 0.01, 0.01, 0.00, '2025-06-04 06:23:28', '2025-06-04 06:23:28'),
(86, 71, 7, 1, 0.01, 0.01, 0.00, '2025-06-04 06:24:51', '2025-06-04 06:24:51'),
(87, 72, 16, 1, 1.50, 1.50, 0.00, '2025-06-04 06:49:02', '2025-06-04 06:49:02'),
(88, 73, 4, 1, 11.99, 11.99, 0.00, '2025-06-04 07:31:10', '2025-06-04 07:31:10'),
(89, 74, 6, 1, 14.50, 14.50, 0.00, '2025-06-04 07:37:48', '2025-06-04 07:37:48'),
(90, 75, 7, 1, 0.01, 0.01, 0.00, '2025-06-05 16:39:20', '2025-06-05 16:39:20'),
(91, 76, 7, 1, 0.01, 0.01, 0.00, '2025-06-05 16:41:45', '2025-06-05 16:41:45'),
(92, 77, 7, 1, 0.01, 0.01, 0.00, '2025-06-05 19:09:33', '2025-06-05 19:09:33'),
(93, 78, 7, 1, 0.01, 0.01, 0.00, '2025-06-06 08:19:49', '2025-06-06 08:19:49'),
(94, 79, 7, 1, 0.01, 0.01, 0.00, '2025-06-06 08:36:25', '2025-06-06 08:36:25'),
(95, 80, 7, 1, 0.01, 0.01, 0.00, '2025-06-06 11:11:00', '2025-06-06 11:11:00');

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
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(17, 66, 'failed', '', '2025-05-21 12:17:51', '2025-05-21 12:17:51'),
(16, 66, 'completed', '', '2025-05-21 12:17:43', '2025-05-21 12:17:43'),
(15, 66, 'failed', 'Payment cancelled by user', '2025-05-21 10:52:09', '2025-05-21 10:52:09'),
(14, 59, 'pending', '', '2025-05-13 14:20:36', '2025-05-13 14:20:36'),
(13, 59, 'completed', '', '2025-05-13 13:08:12', '2025-05-13 13:08:12'),
(12, 59, 'pending', '', '2025-05-13 12:54:58', '2025-05-13 12:54:58'),
(11, 59, 'processing', '', '2025-05-13 12:54:50', '2025-05-13 12:54:50'),
(18, 75, 'pending', 'Order created via PayPal', '2025-06-05 16:39:20', '2025-06-05 16:39:20'),
(19, 76, 'pending', 'Order created via PayPal', '2025-06-05 16:41:45', '2025-06-05 16:41:45'),
(20, 76, 'completed', 'Payment captured via PayPal', '2025-06-05 16:44:03', '2025-06-05 16:44:03'),
(21, 77, 'pending', 'Order created via PayPal', '2025-06-05 19:09:33', '2025-06-05 19:09:33'),
(22, 77, 'completed', 'Payment captured via PayPal', '2025-06-05 19:10:24', '2025-06-05 19:10:24'),
(23, 78, 'pending', 'Order created via PayPal', '2025-06-06 08:19:49', '2025-06-06 08:19:49'),
(24, 78, 'completed', 'Payment captured via PayPal', '2025-06-06 08:22:00', '2025-06-06 08:22:00'),
(25, 79, 'pending', 'Order created via PayPal', '2025-06-06 08:36:25', '2025-06-06 08:36:25'),
(26, 79, 'completed', 'Payment captured via PayPal', '2025-06-06 08:37:33', '2025-06-06 08:37:33'),
(27, 80, 'pending', 'Order created via PayPal', '2025-06-06 11:11:00', '2025-06-06 11:11:00'),
(28, 80, 'completed', 'Payment captured via PayPal', '2025-06-06 11:11:40', '2025-06-06 11:11:40'),
(29, 75, 'processing', '', '2025-06-06 13:09:15', '2025-06-06 13:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

DROP TABLE IF EXISTS `payment_logs`;
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` enum('success','failed','recovered','pending','cancelled') NOT NULL,
  `message` text NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ;

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `order_id`, `status`, `message`, `metadata`, `created_at`) VALUES
(1, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:15:55\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:15:55.226Z\"}', '2025-06-02 11:15:55'),
(2, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:16:02\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:16:02.256Z\"}', '2025-06-02 11:16:02'),
(3, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:16:49\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:16:49.179Z\"}', '2025-06-02 11:16:49'),
(4, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:16:53\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:16:53.571Z\"}', '2025-06-02 11:16:53'),
(5, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:16:59\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:16:59.296Z\"}', '2025-06-02 11:16:59'),
(6, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:17:00\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:17:00.594Z\"}', '2025-06-02 11:17:00'),
(7, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:17:09\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:17:08.644Z\"}', '2025-06-02 11:17:09'),
(8, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:17:09\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:17:08.645Z\"}', '2025-06-02 11:17:09'),
(9, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:27:29\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:27:29.423Z\"}', '2025-06-02 11:27:29'),
(10, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:27:36\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:27:36.459Z\"}', '2025-06-02 11:27:36'),
(11, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:27:45\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:27:45.419Z\"}', '2025-06-02 11:27:45'),
(12, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:27:45\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:27:45.584Z\"}', '2025-06-02 11:27:45'),
(13, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:28:42\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:28:42.335Z\"}', '2025-06-02 11:28:42'),
(14, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:28:52\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:28:52.176Z\"}', '2025-06-02 11:28:52'),
(15, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:29:03\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:29:03.176Z\"}', '2025-06-02 11:29:03'),
(16, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:29:03\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:29:03.186Z\"}', '2025-06-02 11:29:03'),
(17, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:32:16\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:32:16.205Z\"}', '2025-06-02 11:32:16'),
(18, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:32:23\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:32:23.379Z\"}', '2025-06-02 11:32:23'),
(19, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but not properly initialized', '{\"error\": \"Error: PayPal SDK loaded but not properly initialized\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:32:29\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:32:29.417Z\"}', '2025-06-02 11:32:29'),
(20, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:32:29\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:32:29.419Z\"}', '2025-06-02 11:32:29'),
(21, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal object not available', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal object not available\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:34:44\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:34:44.116Z\"}', '2025-06-02 11:34:44'),
(22, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but failed to initialize within 10 seconds', '{\"error\": \"Error: PayPal SDK loaded but failed to initialize within 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:34:56\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:34:56.179Z\"}', '2025-06-02 11:34:56'),
(23, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but failed to initialize within 10 seconds', '{\"error\": \"Error: PayPal SDK loaded but failed to initialize within 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:35:10\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:35:10.169Z\"}', '2025-06-02 11:35:10'),
(24, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:35:10\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:35:10.172Z\"}', '2025-06-02 11:35:10'),
(25, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal object not available', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal object not available\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:37:40\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:37:40.551Z\"}', '2025-06-02 11:37:40'),
(26, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal object not available', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal object not available\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:37:47\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:37:47.574Z\"}', '2025-06-02 11:37:47'),
(27, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal object not available', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal object not available\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:37:54\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:37:54.618Z\"}', '2025-06-02 11:37:54'),
(28, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:37:54\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:37:54.622Z\"}', '2025-06-02 11:37:54'),
(29, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:48:44\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:48:43.952Z\"}', '2025-06-02 11:48:44'),
(30, 0, 'failed', 'PayPal SDK Loading: Error: Failed to load PayPal SDK script from server', '{\"error\": \"Error: Failed to load PayPal SDK script from server\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:49:00\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:49:00.962Z\"}', '2025-06-02 11:49:00'),
(31, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:49:13\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:49:13.796Z\"}', '2025-06-02 11:49:13'),
(32, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:49:13\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:49:13.800Z\"}', '2025-06-02 11:49:13'),
(33, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:50:09\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:50:09.065Z\"}', '2025-06-02 11:50:09'),
(34, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:50:21\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:50:21.208Z\"}', '2025-06-02 11:50:21'),
(35, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:50:33\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:50:33.285Z\"}', '2025-06-02 11:50:33'),
(36, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:50:33\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:50:33.289Z\"}', '2025-06-02 11:50:33'),
(37, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:53:14\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:53:14.645Z\"}', '2025-06-02 11:53:14'),
(38, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:53:26\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:53:26.679Z\"}', '2025-06-02 11:53:26'),
(39, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:53:38\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:53:38.898Z\"}', '2025-06-02 11:53:38'),
(40, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 11:53:38\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T11:53:38.899Z\"}', '2025-06-02 11:53:38'),
(41, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 13:59:05\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Mobile Safari/537.36\", \"request_time\": \"2025-06-02T13:59:05.307Z\"}', '2025-06-02 13:59:05'),
(42, 0, 'failed', 'PayPal SDK Loading: Error: Failed to load PayPal SDK script from server', '{\"error\": \"Error: Failed to load PayPal SDK script from server\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 13:59:24\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T13:59:23.930Z\"}', '2025-06-02 13:59:24'),
(43, 0, 'failed', 'PayPal SDK Loading: Error: Failed to load PayPal SDK script from server', '{\"error\": \"Error: Failed to load PayPal SDK script from server\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 13:59:44\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T13:59:44.194Z\"}', '2025-06-02 13:59:44'),
(44, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 13:59:44\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T13:59:44.196Z\"}', '2025-06-02 13:59:44'),
(45, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 14:10:14\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T14:10:14.716Z\"}', '2025-06-02 14:10:14'),
(46, 0, 'failed', 'PayPal SDK Loading: Error: Failed to load PayPal SDK script from server', '{\"error\": \"Error: Failed to load PayPal SDK script from server\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 14:11:05\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T14:11:04.259Z\"}', '2025-06-02 14:11:05'),
(47, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds', '{\"error\": \"Error: PayPal SDK initialization timeout - paypal.Buttons not available after 10 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 14:11:19\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T14:11:19.927Z\"}', '2025-06-02 14:11:19'),
(48, 0, 'failed', 'PayPal SDK Loading: Error: PayPal SDK loaded but failed to initialize within 15 seconds', '{\"error\": \"Error: PayPal SDK loaded but failed to initialize within 15 seconds\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 14:12:28\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T14:12:28.227Z\"}', '2025-06-02 14:12:28'),
(49, 0, 'failed', 'PayPal SDK Loading: Error: Failed to load PayPal SDK script from server', '{\"error\": \"Error: Failed to load PayPal SDK script from server\", \"context\": \"PayPal SDK Loading\", \"user_id\": 19, \"timestamp\": \"2025-06-02 14:12:53\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T14:12:53.176Z\"}', '2025-06-02 14:12:53'),
(50, 0, 'failed', 'PayPal SDK Loading Final Failure: Error: All PayPal loading attempts failed after 3 tries', '{\"error\": \"Error: All PayPal loading attempts failed after 3 tries\", \"context\": \"PayPal SDK Loading Final Failure\", \"user_id\": 19, \"timestamp\": \"2025-06-02 14:12:53\", \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\", \"request_time\": \"2025-06-02T14:12:53.178Z\"}', '2025-06-02 14:12:53');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `provider`, `is_active`, `config`, `created_at`) VALUES
(1, 'PayPal', 'paypal', 1, NULL, '2025-06-01 17:52:02'),
(2, 'Credit Card', 'stripe', 0, NULL, '2025-06-01 17:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `payment_recovery`
--

DROP TABLE IF EXISTS `payment_recovery`;
CREATE TABLE IF NOT EXISTS `payment_recovery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('stripe','paypal') NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `status` enum('pending_recovery','recovered','failed') NOT NULL DEFAULT 'pending_recovery',
  `error_message` text,
  `recovery_attempts` int NOT NULL DEFAULT '0',
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pending_orders`
--

DROP TABLE IF EXISTS `pending_orders`;
CREATE TABLE IF NOT EXISTS `pending_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `source` enum('cart','failed_payment') NOT NULL DEFAULT 'cart',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_source` (`source`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pending_orders`
--

INSERT INTO `pending_orders` (`id`, `user_id`, `product_id`, `quantity`, `price`, `status`, `source`, `created_at`, `updated_at`) VALUES
(14, 19, 7, 1, 0.01, 'pending', 'cart', '2025-05-31 16:14:37', '2025-05-31 16:14:37'),
(15, 19, 12, 1, 1.50, 'pending', 'cart', '2025-05-31 16:14:37', '2025-05-31 16:14:37');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('paint','ebook','book') NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `stock` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `updated_by` (`timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `type`, `sku`, `name`, `author`, `filepath`, `file_size`, `thumbs`, `price`, `stock_quantity`, `description`, `timestamp`, `updated_at`, `stock`) VALUES
(12, 'book', '222222222222', 'Plight of the Fairies', 'Michael Leonard & Avirom Olive', 'Uploads/pdfs/PROVISIONAL RESULTS-BUS-241-1952024.pdf', '178.13 KB', 'Uploads/thumbs/fairy.jpg', 24.95, 221, '2222', '2025-05-09 09:24:35', '2025-06-05 22:37:02', 0),
(15, 'ebook', 'RE78945286', 'Ryvah Encyclopedia', 'Michael Leonard', 'Uploads/pdfs/Ryvah  Encyclopedia.pdf', '10.15 MB', 'Uploads/thumbs/ryvah-encyclopedia.png', 1.50, 50000, 'What is a Level?\r\nThe level of a given monster is a crude\r\napproximation of power and indicates the difficulty of\r\nthe whole encounter as defined. This includes the\r\nquantity of opponents. Notice everything has an exact\r\nquantity. Four informed and prepared characters of the\r\nsame level of a given encounter (a monster) should be\r\nable to defeat the given quantity of creatures with one\r\ncasualty. In this sense, a casualty means unconscious\r\nnot dead. A poorly prepared party should lose. If the\r\nparty is one level above the monster, than the Game\r\nMaster can probably triple the quantity of opponents.\r\nGoing down is not as simple. Ideally the Game Master\r\ncould divide the number of opponents by three, but this\r\ndoes not always diminish the power proportionally.\r\nThere is little point in battles with a two-level\r\ndifference; simply declare the lower one dead. For a\r\nparty fewer than four or greater than four the Game\r\nMaster should multiply the quantity of opponents by the\r\nsame ratio. With all that said, as a Game Master, if you\r\nare running a given quest, then it is completely\r\nacceptable to maintain the quantities as the quest calls\r\nfor because not all battles are intended to be winnable,\r\nand some are not winnable . . . yet. Such a plot would\r\nexpect the party to gain power before targeting\r\nparticular obstacles. Of course this means the Game\r\nMaster MUST have plenty of winnable challenges for\r\nthe party to explore first. And if the Game Master\r\ndoesn’t—then lower that challenge down to the point\r\nthat the party has at least a 50% chance of winning.\r\nWith this tool, the Game Master can now calculate and\r\npredict the amount of experience he will need to award\r\nbefore the party is locked into a do-or-die battle with\r\nthe final challenge. Now pick a time line and calculate\r\nan experience point progression. For example: The\r\nBoss: Level 4. This needs four characters at 56,000\r\nexperience. If they start at 10,000 (standard 10k build),\r\nthen we must award 46,000 experience before that\r\nbattle. Ideally you should have about 10 encounters per\r\nlevel. But maybe I don’t want that. I want this to last\r\nonly . . . 4 months (I am just making this up as I go to\r\nillustrate how easy it is to use). If we play once a week,\r\nthen I can plan on 18 games. 46,000 ÷ 18 = 2,875. It’s\r\nthat easy. But we can even get fancy. We can make\r\nany kind of curve or arc we want. Let’s start at 500\r\nexperience for three weeks; 1,000 for three more; 2,000\r\nfor the next three. So far we are at 9 weeks (the\r\nhalfway point), so the next 9 will be as high as the first\r\n9 were low. 3,000 for the next 3 weeks; 4,000 for the\r\nnext three weeks, and 4,500 for the last three weeks.\r\nIt’s close enough. With these mathematical tools my\r\nplayers will be at 55,000 experience, and if (and only if)\r\nthey have a decent plan to win, they will win. There\r\nwill be nothing gifted about this victory. A failure to\r\nplane and work together will bring defeat....................................................................', '2025-05-28 19:36:41', '2025-05-28 19:43:27', 0),
(3, 'ebook', 'EBK003', 'The CSS Handbook', 'Alan White', 'files/ebooks/css_handbook.pdf', '2.8MB', 'thumbs/css_handbook.jpg', 5.99, 38, 'A practical guide to mastering CSS for web design.', '2025-05-09 05:55:13', '2025-05-19 10:48:03', -3),
(4, 'book', 'EBK004', 'React Essentials', 'Mary Johnson', 'files/ebooks/react_essentials.pdf', '6.1MB', 'thumbs/react_essentials.jpg', 11.99, 16, 'An essential resource for React developers.', '2025-05-09 05:55:13', '2025-05-27 19:24:04', 0),
(5, 'ebook', 'EBK005', 'Linux Commands Guide', 'Robert Black', 'files/ebooks/linux_commands.pdf', '1.9MB', 'thumbs/linux_commands.jpg', 4.99, 198, 'Quick reference to essential Linux commands.', '2025-05-09 05:55:13', '2025-05-13 09:19:50', 0),
(6, 'paint', 'PNT001', 'Sunset Orange - 1L', 'ColorCo Ltd.', 'files/paints/sunset_orange.jpg', '1.2MB', 'thumbs/sunset_orange.jpg', 14.50, 49, 'High-quality acrylic paint with a vibrant orange tone.', '2025-05-09 05:55:13', '2025-05-11 11:37:38', 0),
(7, 'ebook', 'PNT002', 'Ocean Blue - 5', 'AquaPaints Inc.', 'files/paints/ocean_blue.jpg', '1.4MB', 'thumbs/ocean_blue.jpg', 0.01, 14, 'Premium paint ideal for exterior walls and marine use.', '2025-05-09 05:55:13', '2025-06-06 11:11:40', 0),
(16, 'ebook', 'SR124558', 'Ryvah System Rules', 'Michael Leonard', 'Uploads/pdfs/System Rules Ryvah.pdf', '13.08 MB', 'Uploads/thumbs/rules.png', 1.50, 5000, 'The Story of Pya\r\nThe world of Ryvah is vast and ancient.\r\nCultures and civilizations have risen and fallen hundreds\r\nof times. The humans were the first. At one time, their\r\ncities covered the land. It was a time before Elves,\r\nDwarves, the first of the enchanted creatures, and even\r\nbefore magic. The humans of old were supremely\r\nclever. Yet, for all of their ingenuity, they were lazy and\r\ngreedy and would risk anything to satisfy their appetite\r\nfor power. They made giant constructs in an attempt to\r\nbring Mother Nature to her knees and enslave her. In\r\ndoing so, they warped reality and, like a tidal wave,\r\ndevastated everything in their wake. Pure magic poured\r\ninto the world. Reality warped for but a moment, yet\r\nlife would never be the same.\r\nWithin a year, the entire civilization had\r\ncollapsed. Magic spread throughout the world, soaking\r\ninto the land, carried far and wide by the wind. As this\r\nhappened thousands of different races and monsters were\r\nsuddenly forged into existence. As centuries passed, one\r\nrace after another would grow in number to the point\r\nthat they had control over the entire world only to war or\r\ncollapse into extinction. As many millennia passed,\r\ndifferent races have held great power, some races many\r\ntimes over. Old cities built atop older cities built atop\r\neven older cities. As the ages passed, the population\r\nthinned out. Isolated kingdoms forged elaborate\r\ncultures. Isolated cities made sub-cultures. Knowledge\r\nof magic passed down from generation to generation\r\nand, as languages changed, secrecy increased, bits and\r\npieces of that knowledge were lost to that culture. Now\r\nit is not uncommon for travelers to encounter forms of\r\nmagic they have never seen. For precisely this reason,\r\nancient manuscripts, temples, and tombs often hold\r\nsecrets about the mysteries of magic. Unfortunately, a\r\ncombination of greed and the fear of magic falling into\r\nthe wrong hands have led to the decision of many archmagi to carry their secrets to their graves.\r\n*\r\nThe mystical Elven city of New Itosh had\r\ngravity defying towers of stone where magic dripped\r\nlike morning dew. With a small population of only\r\n10,000 elves and no enemies to speak of, the culture\r\nenjoyed a luxurious life style of music and art.\r\nElaborate columns and balconies decorated the complex\r\narchitecture wherever stained glass windows did not.\r\nIn the city of New Itosh, swords and bows\r\nserved no function but to decorate the walls. Those who\r\nhave magic rule, those who do not must serve. There is\r\nnothing in a hundred miles that can hope to challenge\r\nNew Itosh’s absolute power.\r\nThe evening was calm and warm with only a\r\nfew clouds to break up an otherwise clear night sky.\r\nCandles flickered as steam rose from a slow cooking\r\ncauldron in the corner. The moon, full and bright, shone\r\nin through the window as the cold night breeze delivered\r\na refreshing reprieve from the less then pleasant odors of\r\nthe cauldron, where a small coven of powerful witches\r\nplotted and planned an expedition to gather alchemy not\r\nnative to their region.\r\nKytoon breathed deep and paused from his toils,\r\nhe was the head of this particular coven of Elven\r\nwitches. His boots, dyed a deep red, were finely crafted\r\nwith brass clasps etched with runes. A thin embroidered\r\nwhite silk gown that shimmered like mother of pearl lay\r\nunder his heavy red and black velvet robe. The gown\r\nwas new and pristine in extreme contrast to his robe that,\r\nalthough well taken care of, showed the marks of being\r\nworn into battle by his father and his grandfather before\r\nhim. Much like his father once had, Kytoon held a seat\r\nin the city’s Ministry of Magic......................................................', '2025-05-28 19:41:40', '2025-06-05 22:56:19', 0);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires`, `created_at`) VALUES
(19, 19, 'b6c50101f43df19a2e1e5856aa715e1eae68dc0de6f6f93f0172b89aee69fee5', '2025-07-04 17:13:03', '2025-06-04 17:13:03');

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
-- Table structure for table `shipping_fees`
--

DROP TABLE IF EXISTS `shipping_fees`;
CREATE TABLE IF NOT EXISTS `shipping_fees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type` enum('paint','ebook','book') NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `applies_after_tax` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_type` (`product_type`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `shipping_fees`
--

INSERT INTO `shipping_fees` (`id`, `product_type`, `shipping_fee`, `is_active`, `applies_after_tax`, `description`, `created_at`, `updated_at`) VALUES
(1, 'book', 7.00, 1, 1, 'Standard shipping fee for physical books', '2025-06-06 13:49:56', '2025-06-06 13:49:56'),
(3, 'paint', 5.50, 1, 1, 'Standard shipping fee for paint products', '2025-06-06 13:49:56', '2025-06-06 13:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

DROP TABLE IF EXISTS `shipping_methods`;
CREATE TABLE IF NOT EXISTS `shipping_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `cost` decimal(10,2) NOT NULL,
  `estimated_days` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `description`, `cost`, `estimated_days`, `is_active`, `created_at`) VALUES
(1, 'Standard Shipping', 'Free shipping on orders over $50', 7.00, '5-7 business days', 1, '2025-06-01 17:52:02'),
(2, 'Express Shipping', 'Faster delivery for urgent orders', 15.00, '2-3 business days', 1, '2025-06-01 17:52:02'),
(3, 'Overnight Shipping', 'Next day delivery', 25.00, '1 business day', 1, '2025-06-01 17:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `tax_settings`
--

DROP TABLE IF EXISTS `tax_settings`;
CREATE TABLE IF NOT EXISTS `tax_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type` enum('paint','ebook','book') NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_type` (`product_type`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tax_settings`
--

INSERT INTO `tax_settings` (`id`, `product_type`, `tax_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'paint', 7.75, 1, '2025-05-29 15:02:53', '2025-05-29 15:30:11'),
(2, 'ebook', 7.75, 0, '2025-05-29 15:02:53', '2025-06-06 13:28:47'),
(4, 'book', 7.75, 1, '2025-05-29 15:41:20', '2025-05-29 15:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('stripe','paypal') NOT NULL,
  `status` enum('success','failed','pending') NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `error_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `payment_method`, `status`, `transaction_id`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 76, 'paypal', 'success', '0CP13509E3699784F', NULL, '2025-06-05 16:44:03', '2025-06-05 16:44:03'),
(2, 77, 'paypal', 'success', '3G361467C1434560N', NULL, '2025-06-05 19:10:24', '2025-06-05 19:10:24'),
(3, 78, 'paypal', 'success', '0Y884504JL527071D', NULL, '2025-06-06 08:22:00', '2025-06-06 08:22:00'),
(4, 79, 'paypal', 'success', '1PR47752Y4531023R', NULL, '2025-06-06 08:37:33', '2025-06-06 08:37:33'),
(5, 80, 'paypal', 'success', '6XV37548JL803981J', NULL, '2025-06-06 11:11:40', '2025-06-06 11:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('Admin','Client','Employee') NOT NULL DEFAULT 'Client',
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `encryption_key` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `iv` varchar(255) NOT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `role`, `password`, `phone`, `encryption_key`, `salt`, `iv`, `address`, `city`, `state`, `postal_code`, `created_at`) VALUES
(20, 'Jane Ngari Atieno', 'janengari3467@gmail.com', 'Admin', '$2y$10$7LnHacncUUXL5pSvSOKiSOTdQ46aJ/sHTnZ39/31C7ZAIHdnEr62u', '0793715233', '758cf263ff7035e40aa8c85aabcf2332444df4cfe1d6781eb39894b68b504171', '8467cc387e1ca3ebce299bbb739a2e28', '', '12345', 'New York', 'NY', '10001', '2025-05-11 09:46:54'),
(19, 'Samwel Karanja Njoroge', 'samwelnjoroge757@gmail.com', 'Client', '$2y$10$iwnYoFu2Srkkc7BMUW/EauhEvR/piq4Wb8HYhfcqbsG.yqaJsidty', '0793878068', 'd7ccfa11c3a6304bca0ac2f5ee2291c6edf01931deb3d70ec69ae2c379a9b7c9', '00895509daea96f301d6e1bcb5071128', '', '12345', 'New York', 'NY', '10001', '2025-05-11 05:48:43'),
(43, 'Christopher Wilson', 'chriswilson7850@gmail.com', 'Client', '$2y$10$SniLVQtXETNgixY8X2g9Fu8pyvKNrYWPglHvu8Q2ozXN7qlVudGQi', '4015542971', '8fa49c4da6533ad85866f0354270ca1c05f866c0f9142099c1835d5c5e3caa8e', 'db6a2e573d1d801ce84291c3037fcbdb', '7dc159d9be33d802ca514f8970388164', '4791 Myra Street Newport', 'Newport', 'RI', '02840', '2025-06-05 14:34:56'),
(42, 'Ryvah Commerce', 'ryvah256@gmail.com', 'Admin', '$2y$10$7X4E1Yz5tZcBCtZ7UERidelhIkMZ0Nzf0uQhDFQUJ4KQNtaKU3.82', '0717426793', 'caf90493432be6e9fa5703dffc7e9f055d6655614f83b27bd18850f2de213d77', '5e34c6c1a078d5e7c9e4d3460e171c5b', '3b37e82581142a884ba2587c7617af0f', '21 kapengura', 'Kapenguria', 'kapenguria', '30200', '2025-06-04 12:13:21');

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
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `email`, `phone`, `ip_address`, `login_time`, `logout_time`, `session_duration`, `created_at`) VALUES
(29, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-21 15:08:43', NULL, NULL, '2025-05-21 12:08:43'),
(28, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-21 15:04:45', NULL, NULL, '2025-05-21 12:04:45'),
(27, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-21 14:52:47', NULL, NULL, '2025-05-21 11:52:47'),
(26, 19, 'samwelnjoroge757@gmail.com', '0793878068', '::1', '2025-05-21 14:52:11', NULL, NULL, '2025-05-21 11:52:11'),
(25, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-19 15:44:48', NULL, NULL, '2025-05-19 12:44:48'),
(24, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-16 14:23:54', NULL, NULL, '2025-05-16 11:23:54'),
(23, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-16 13:17:02', NULL, NULL, '2025-05-16 10:17:02'),
(22, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-16 11:39:16', NULL, NULL, '2025-05-16 08:39:16'),
(30, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-21 15:10:00', NULL, NULL, '2025-05-21 12:10:00'),
(31, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-23 00:20:54', NULL, NULL, '2025-05-22 21:20:54'),
(32, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-27 22:12:35', NULL, NULL, '2025-05-27 19:12:35'),
(33, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-28 22:29:33', NULL, NULL, '2025-05-28 19:29:33'),
(34, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-29 05:01:44', NULL, NULL, '2025-05-29 02:01:44'),
(35, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-29 13:48:51', NULL, NULL, '2025-05-29 10:48:51'),
(36, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-29 13:52:13', NULL, NULL, '2025-05-29 10:52:13'),
(37, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-29 16:39:40', NULL, NULL, '2025-05-29 13:39:40'),
(38, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-05-29 18:09:36', NULL, NULL, '2025-05-29 15:09:36'),
(39, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-06-01 20:26:25', NULL, NULL, '2025-06-01 17:26:25'),
(40, 20, 'janengari3467@gmail.com', '0793715233', '102.215.77.246', '2025-06-04 16:47:17', NULL, NULL, '2025-06-04 16:47:17'),
(41, 20, 'janengari3467@gmail.com', '0793715233', '102.215.77.246', '2025-06-04 17:11:23', NULL, NULL, '2025-06-04 17:11:23'),
(42, 42, 'ryvah256@gmail.com', '0717426793', '102.215.77.246', '2025-06-04 19:35:23', NULL, NULL, '2025-06-04 19:35:23'),
(43, 42, 'ryvah256@gmail.com', '0717426793', '102.215.77.246', '2025-06-04 19:36:13', NULL, NULL, '2025-06-04 19:36:13'),
(44, 20, 'janengari3467@gmail.com', '0793715233', '102.215.77.246', '2025-06-05 21:32:47', NULL, NULL, '2025-06-05 21:32:47'),
(45, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-06-06 12:46:34', NULL, NULL, '2025-06-06 12:46:34'),
(46, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-06-06 13:28:28', NULL, NULL, '2025-06-06 13:28:28'),
(47, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-06-06 14:20:35', NULL, NULL, '2025-06-06 14:20:35'),
(48, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-06-06 18:28:32', NULL, NULL, '2025-06-06 18:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `save_payment_info` tinyint(1) DEFAULT '0',
  `email_notifications` tinyint(1) DEFAULT '1',
  `sms_notifications` tinyint(1) DEFAULT '0',
  `default_shipping_method` varchar(50) DEFAULT 'standard',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 04, 2025 at 09:27 AM
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
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `label` varchar(50) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `first_name`, `last_name`, `label`, `street`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 19, 'Samwel', 'Njoroge', 'hyt', 'yyty', 'New York', 'NY', '343', 'US', 1, '2025-05-30 12:37:54', '2025-06-01 17:52:03'),
(2, 20, NULL, NULL, 'HOME', '1264-Thika', 'Thika', 'EMBU', '01000', 'Other', 0, '2025-06-01 19:29:50', '2025-06-01 19:29:50');

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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

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
(14, 20, 'update_tax_status', '{\"product_type\":\"book\",\"is_active\":\"1\"}', '2025-05-29 15:41:42');

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
) ENGINE=MyISAM AUTO_INCREMENT=162 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(130, 20, 7, 1, '2025-06-01 17:28:10', '2025-06-01 17:28:10'),
(105, 32, 7, 1, '2025-05-27 19:08:07', '2025-05-27 19:08:07'),
(161, 19, 12, 1, '2025-06-04 07:44:24', '2025-06-04 07:44:24');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ebook_downloads`
--

INSERT INTO `ebook_downloads` (`id`, `user_id`, `order_id`, `product_id`, `download_token`, `download_count`, `max_downloads`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 19, 71, 7, '651', 0, 5, '2025-07-04 06:24:53', '2025-06-04 06:24:53', '2025-06-04 06:24:53'),
(2, 19, 72, 16, '0', 0, 5, '2025-07-04 06:49:03', '2025-06-04 06:49:03', '2025-06-04 06:49:03');

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `user_id` (`user_id`),
  KEY `order_date` (`order_date`),
  KEY `payment_status` (`payment_status`),
  KEY `idx_orders_invoice` (`invoice_number`),
  KEY `idx_paypal_order_id` (`paypal_order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice_number`, `user_id`, `order_date`, `total_amount`, `tax_amount`, `tax_rate`, `payment_status`, `payment_method`, `paypal_order_id`, `shipping_address`, `billing_address`, `shipping_method`, `notes`, `created_at`, `updated_at`) VALUES
(66, '', 19, '2025-05-16 10:52:43', 11.99, 0.00, 0.00, 'failed', NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-16 07:52:43', '2025-05-21 12:17:51'),
(67, 'INV-20250601-0019-72', 19, '2025-06-01 13:44:20', 12.93, 0.93, 0.00, 'completed', NULL, 'MOCK_ORDER_683c2f0484e65_1748774660', NULL, NULL, NULL, NULL, '2025-06-01 10:44:20', '2025-06-01 10:44:29'),
(68, 'INV-20250601-0019-57', 19, '2025-06-01 13:45:26', 1.62, 0.12, 0.00, 'pending', NULL, 'MOCK_ORDER_683c2f466b180_1748774726', NULL, NULL, NULL, NULL, '2025-06-01 10:45:26', '2025-06-01 10:45:26'),
(69, 'INV-20250604-19-4980', 19, '2025-06-04 06:18:47', 0.01, 0.00, 0.00, 'pending', NULL, 'MOCK_ORDER_1749017927_2256', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:18:47', '2025-06-04 06:18:47'),
(70, 'INV-20250604-19-2022', 19, '2025-06-04 06:23:28', 0.01, 0.00, 0.00, 'pending', NULL, 'MOCK_ORDER_1749018208_4236', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:23:28', '2025-06-04 06:23:28'),
(71, 'INV-20250604-19-3928', 19, '2025-06-04 06:24:51', 0.01, 0.00, 0.00, 'completed', NULL, 'MOCK_ORDER_1749018291_4033', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:24:51', '2025-06-04 06:24:53'),
(72, 'INV-20250604-19-6838', 19, '2025-06-04 06:49:02', 1.65, 0.15, 0.00, 'completed', NULL, 'FALLBACK_ORDER_1749019742_5242', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 06:49:02', '2025-06-04 06:49:03'),
(73, 'INV-20250604-19-2612', 19, '2025-06-04 07:31:10', 13.19, 1.20, 0.00, 'completed', NULL, 'FALLBACK_ORDER_1749022270_1136', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 07:31:10', '2025-06-04 07:31:11'),
(74, 'INV-20250604-19-7483', 19, '2025-06-04 07:37:48', 15.95, 1.45, 0.00, 'completed', NULL, 'FALLBACK_ORDER_1749022668_9769', '{\"id\":1,\"user_id\":19,\"first_name\":\"Samwel\",\"last_name\":\"Njoroge\",\"label\":\"hyt\",\"street\":\"yyty\",\"city\":\"New York\",\"state\":\"NY\",\"postal_code\":\"343\",\"country\":\"US\",\"is_default\":1,\"created_at\":\"2025-05-30 12:37:54\",\"updated_at\":\"2025-06-01 17:52:03\"}', NULL, NULL, NULL, '2025-06-04 07:37:48', '2025-06-04 07:37:49');

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
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=latin1;

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
(89, 74, 6, 1, 14.50, 14.50, 0.00, '2025-06-04 07:37:48', '2025-06-04 07:37:48');

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
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

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
(11, 59, 'processing', '', '2025-05-13 12:54:50', '2025-05-13 12:54:50');

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
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;

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
  `config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

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
  `type` enum('paint','ebook','book') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `type`, `sku`, `name`, `author`, `filepath`, `file_size`, `thumbs`, `price`, `stock_quantity`, `description`, `timestamp`, `updated_at`, `stock`) VALUES
(12, 'book', '222222222222', 'Plight of the Faries ', 'Michael Leonard & Avirom Olive', 'Uploads/pdfs/PROVISIONAL RESULTS-BUS-241-1952024.pdf', '178.13 KB', 'Uploads/thumbs/output.jpg', 24.95, 221, '2222', '2025-05-09 09:24:35', '2025-06-01 17:27:07', 0),
(15, 'ebook', 'RE78945286', 'Ryvah Encyclopedia', 'Michael Leonard', 'Uploads/pdfs/Ryvah  Encyclopedia.pdf', '10.15 MB', 'Uploads/thumbs/ryvah-encyclopedia.png', 1.50, 50000, 'What is a Level?\r\nThe level of a given monster is a crude\r\napproximation of power and indicates the difficulty of\r\nthe whole encounter as defined. This includes the\r\nquantity of opponents. Notice everything has an exact\r\nquantity. Four informed and prepared characters of the\r\nsame level of a given encounter (a monster) should be\r\nable to defeat the given quantity of creatures with one\r\ncasualty. In this sense, a casualty means unconscious\r\nnot dead. A poorly prepared party should lose. If the\r\nparty is one level above the monster, than the Game\r\nMaster can probably triple the quantity of opponents.\r\nGoing down is not as simple. Ideally the Game Master\r\ncould divide the number of opponents by three, but this\r\ndoes not always diminish the power proportionally.\r\nThere is little point in battles with a two-level\r\ndifference; simply declare the lower one dead. For a\r\nparty fewer than four or greater than four the Game\r\nMaster should multiply the quantity of opponents by the\r\nsame ratio. With all that said, as a Game Master, if you\r\nare running a given quest, then it is completely\r\nacceptable to maintain the quantities as the quest calls\r\nfor because not all battles are intended to be winnable,\r\nand some are not winnable . . . yet. Such a plot would\r\nexpect the party to gain power before targeting\r\nparticular obstacles. Of course this means the Game\r\nMaster MUST have plenty of winnable challenges for\r\nthe party to explore first. And if the Game Master\r\ndoesn't—then lower that challenge down to the point\r\nthat the party has at least a 50% chance of winning.\r\nWith this tool, the Game Master can now calculate and\r\npredict the amount of experience he will need to award\r\nbefore the party is locked into a do-or-die battle with\r\nthe final challenge. Now pick a time line and calculate\r\nan experience point progression. For example: The\r\nBoss: Level 4. This needs four characters at 56,000\r\nexperience. If they start at 10,000 (standard 10k build),\r\nthen we must award 46,000 experience before that\r\nbattle. Ideally you should have about 10 encounters per\r\nlevel. But maybe I don't want that. I want this to last\r\nonly . . . 4 months (I am just making this up as I go to\r\nillustrate how easy it is to use). If we play once a week,\r\nthen I can plan on 18 games. 46,000 ÷ 18 = 2,875. It's\r\nthat easy. But we can even get fancy. We can make\r\nany kind of curve or arc we want. Let's start at 500\r\nexperience for three weeks; 1,000 for three more; 2,000\r\nfor the next three. So far we are at 9 weeks (the\r\nhalfway point), so the next 9 will be as high as the first\r\n9 were low. 3,000 for the next 3 weeks; 4,000 for the\r\nnext three weeks, and 4,500 for the last three weeks.\r\nIt's close enough. With these mathematical tools my\r\nplayers will be at 55,000 experience, and if (and only if)\r\nthey have a decent plan to win, they will win. There\r\nwill be nothing gifted about this victory. A failure to\r\nplane and work together will bring defeat....................................................................', '2025-05-28 19:36:41', '2025-05-28 19:43:27', 0),
(3, 'ebook', 'EBK003', 'The CSS Handbook', 'Alan White', 'files/ebooks/css_handbook.pdf', '2.8MB', 'thumbs/css_handbook.jpg', 5.99, 38, 'A practical guide to mastering CSS for web design.', '2025-05-09 05:55:13', '2025-05-19 10:48:03', -3),
(4, 'book', 'EBK004', 'React Essentials', 'Mary Johnson', 'files/ebooks/react_essentials.pdf', '6.1MB', 'thumbs/react_essentials.jpg', 11.99, 16, 'An essential resource for React developers.', '2025-05-09 05:55:13', '2025-05-27 19:24:04', 0),
(5, 'ebook', 'EBK005', 'Linux Commands Guide', 'Robert Black', 'files/ebooks/linux_commands.pdf', '1.9MB', 'thumbs/linux_commands.jpg', 4.99, 198, 'Quick reference to essential Linux commands.', '2025-05-09 05:55:13', '2025-05-13 09:19:50', 0),
(6, 'paint', 'PNT001', 'Sunset Orange - 1L', 'ColorCo Ltd.', 'files/paints/sunset_orange.jpg', '1.2MB', 'thumbs/sunset_orange.jpg', 14.50, 49, 'High-quality acrylic paint with a vibrant orange tone.', '2025-05-09 05:55:13', '2025-05-11 11:37:38', 0),
(7, 'ebook', 'PNT002', 'Ocean Blue - 5', 'AquaPaints Inc.', 'files/paints/ocean_blue.jpg', '1.4MB', 'thumbs/ocean_blue.jpg', 0.01, 19, 'Premium paint ideal for exterior walls and marine use.', '2025-05-09 05:55:13', '2025-05-22 21:21:26', 0),
(16, 'ebook', 'SR124558', 'Ryvah System Rules', 'Michael Leonard', 'Uploads/pdfs/System Rules Ryvah.pdf', '13.08 MB', 'Uploads/thumbs/system-rules.png', 1.50, 5000, 'The Story of Pya\r\nThe world of Ryvah is vast and ancient.\r\nCultures and civilizations have risen and fallen hundreds\r\nof times. The humans were the first. At one time, their\r\ncities covered the land. It was a time before Elves,\r\nDwarves, the first of the enchanted creatures, and even\r\nbefore magic. The humans of old were supremely\r\nclever. Yet, for all of their ingenuity, they were lazy and\r\ngreedy and would risk anything to satisfy their appetite\r\nfor power. They made giant constructs in an attempt to\r\nbring Mother Nature to her knees and enslave her. In\r\ndoing so, they warped reality and, like a tidal wave,\r\ndevastated everything in their wake. Pure magic poured\r\ninto the world. Reality warped for but a moment, yet\r\nlife would never be the same.\r\nWithin a year, the entire civilization had\r\ncollapsed. Magic spread throughout the world, soaking\r\ninto the land, carried far and wide by the wind. As this\r\nhappened thousands of different races and monsters were\r\nsuddenly forged into existence. As centuries passed, one\r\nrace after another would grow in number to the point\r\nthat they had control over the entire world only to war or\r\ncollapse into extinction. As many millennia passed,\r\ndifferent races have held great power, some races many\r\ntimes over. Old cities built atop older cities built atop\r\neven older cities. As the ages passed, the population\r\nthinned out. Isolated kingdoms forged elaborate\r\ncultures. Isolated cities made sub-cultures. Knowledge\r\nof magic passed down from generation to generation\r\nand, as languages changed, secrecy increased, bits and\r\npieces of that knowledge were lost to that culture. Now\r\nit is not uncommon for travelers to encounter forms of\r\nmagic they have never seen. For precisely this reason,\r\nancient manuscripts, temples, and tombs often hold\r\nsecrets about the mysteries of magic. Unfortunately, a\r\ncombination of greed and the fear of magic falling into\r\nthe wrong hands have led to the decision of many archmagi to carry their secrets to their graves.\r\n*\r\nThe mystical Elven city of New Itosh had\r\ngravity defying towers of stone where magic dripped\r\nlike morning dew. With a small population of only\r\n10,000 elves and no enemies to speak of, the culture\r\nenjoyed a luxurious life style of music and art.\r\nElaborate columns and balconies decorated the complex\r\narchitecture wherever stained glass windows did not.\r\nIn the city of New Itosh, swords and bows\r\nserved no function but to decorate the walls. Those who\r\nhave magic rule, those who do not must serve. There is\r\nnothing in a hundred miles that can hope to challenge\r\nNew Itosh's absolute power.\r\nThe evening was calm and warm with only a\r\nfew clouds to break up an otherwise clear night sky.\r\nCandles flickered as steam rose from a slow cooking\r\ncauldron in the corner. The moon, full and bright, shone\r\nin through the window as the cold night breeze delivered\r\na refreshing reprieve from the less then pleasant odors of\r\nthe cauldron, where a small coven of powerful witches\r\nplotted and planned an expedition to gather alchemy not\r\nnative to their region.\r\nKytoon breathed deep and paused from his toils,\r\nhe was the head of this particular coven of Elven\r\nwitches. His boots, dyed a deep red, were finely crafted\r\nwith brass clasps etched with runes. A thin embroidered\r\nwhite silk gown that shimmered like mother of pearl lay\r\nunder his heavy red and black velvet robe. The gown\r\nwas new and pristine in extreme contrast to his robe that,\r\nalthough well taken care of, showed the marks of being\r\nworn into battle by his father and his grandfather before\r\nhim. Much like his father once had, Kytoon held a seat\r\nin the city's Ministry of Magic......................................................', '2025-05-28 19:41:40', '2025-05-28 19:43:07', 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

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
(1, 'Standard Shipping', 'Free shipping on orders over $50', 5.00, '5-7 business days', 1, '2025-06-01 17:52:02'),
(2, 'Express Shipping', 'Faster delivery for urgent orders', 15.00, '2-3 business days', 1, '2025-06-01 17:52:02'),
(3, 'Overnight Shipping', 'Next day delivery', 25.00, '1 business day', 1, '2025-06-01 17:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `tax_settings`
--

DROP TABLE IF EXISTS `tax_settings`;
CREATE TABLE IF NOT EXISTS `tax_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type` enum('paint','ebook','book') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
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
(2, 'ebook', 7.75, 1, '2025-05-29 15:02:53', '2025-05-29 15:02:53'),
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
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `role`, `password`, `phone`, `encryption_key`, `salt`, `iv`, `address`, `city`, `state`, `postal_code`, `created_at`) VALUES
(20, 'Jane Ngari Atieno', 'janengari3467@gmail.com', 'Admin', '$2y$10$7LnHacncUUXL5pSvSOKiSOTdQ46aJ/sHTnZ39/31C7ZAIHdnEr62u', '0793715233', '758cf263ff7035e40aa8c85aabcf2332444df4cfe1d6781eb39894b68b504171', '8467cc387e1ca3ebce299bbb739a2e28', '', '12345', 'New York', 'NY', '10001', '2025-05-11 09:46:54'),
(19, 'Samwel Karanja Njoroge', 'samwelnjoroge757@gmail.com', 'Client', '$2y$10$iwnYoFu2Srkkc7BMUW/EauhEvR/piq4Wb8HYhfcqbsG.yqaJsidty', '0793878068', 'd7ccfa11c3a6304bca0ac2f5ee2291c6edf01931deb3d70ec69ae2c379a9b7c9', '00895509daea96f301d6e1bcb5071128', '', '12345', 'New York', 'NY', '10001', '2025-05-11 05:48:43'),
(21, 'James Karanja', 'jameskaranja@gmail.com', 'Client', '$2y$10$iwnYoFu2Srkkc7BMUW/EauhEvR/piq4Wb8HYhfcqbsG.yqaJsidty', '0721115900', 'd7ccfa11c3a6304bca0ac2f5ee2291c6edf01931deb3d70ec69ae2c379a9b7c9', '00895509daea96f301d6e1bcb5071128', '', '12345', 'New York', 'NY', '10001', '2025-05-11 05:48:43'),
(22, 'Samwel Karanja Njoroge', 'samwelnjoroge7517@gmail.com', 'Client', '$2y$10$R8vtlW6BKtAupmzxsTN9du8xyt6n/ysaVqI6jKZxb..0YEv0EEspO', '0793878068', 'a5059efeeefc42dee37185ae70fdfc942989012ca3baf3f926a4e5e1ff88af10', '07d7696f8e657df74b340ffbb05fbfd6', '60e8e78c7e304d0e40bc0a243237d284', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 14:25:56'),
(23, 'James Karanja', 'jemo@gmail.com', 'Client', '$2y$10$ctyKVx/CwQKTjOzGk34yW./ZXCDbLYmMHVyrCqjfsg5PMHS.24IjS', '111111111', 'cebf65a4dff0a8a81752d0f20b572d3183f1965ca56556460f7209a3d0921e94', 'effd6231c3d9601e0ba56ccbe7a3c875', '71bd951cb03da6f02563e5cdc7cc4058', '12345', 'EMBU', 'EMBU', '60100', '2025-05-21 14:27:41'),
(24, 'Samwel Karanja Njoroge', 'samwelnjoroger757@gmail.com', 'Client', '$2y$10$BroyFhcH7eAg17Qh3JQI9eP4Ar.xQwfy2clNAjoIZzh4dqFOaRxPu', '0793878068', 'ca5fad7011e0ffa75fa834073ceac061f8adbcd00d0d5c6d61484034a41bf3c5', '5669198a72d9c70a8f58fcdeeb35ee1c', '4ebc7fcdef1aaff3c65770c53b42084c', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 14:37:03'),
(25, 'Samwel Karanja Njoroge', 'samwelnjoroge7q57@gmail.com', 'Client', '$2y$10$6ge3u8UgBdcXT58O8aMqqexi0y8r3y.5Py5tac.vpGQj2L83e22Qy', '0793878068', '162f1b16967cddea90db726d13a0bb835c9f7f730ff59b0483f87c657e3de2f0', 'd0503b1609ec01495454cf9b6820065e', 'd73f7a871094dfbc5039db38b71ce169', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 14:41:04'),
(26, 'Samwel Karanja Njoroge', 'samwejoroge757@gmail.com', 'Client', '$2y$10$Am1R.4k.0rvaHg5womEIYevF9hq1UA1u4SVQg3VTJtftdqL8k8mR6', '0793878068', 'ebdd657ebe9fbcbe1815fb36f9a2689b8306a13aa2668b212550fdec2f1c2f1b', '55130882ccf455f589d3bcd91a3b64db', 'c2051a04de7ed6ae914dbdb473a71856', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 14:41:25'),
(27, 'Samwel Karanja Njoroge', 'samwelnjorog757@gmail.com', 'Client', '$2y$10$Dw9BjxOU8fe1Y0gFVsmac.ZtAXHNyEBPAxxuPcmD5Nv0tRE3KNhz6', '0793878068', '193a89e01071b65b1cd34a9ea1385af9659235241b60c315b91a1661c9819837', 'bf026999e1a36403f7afeb0180e66c3c', '9f80f9328f8ca7d2f16851882c699d12', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 14:46:22'),
(28, 'Samwel Karanja Njoroge', 'mkenya@gmail.com', 'Client', '$2y$10$K39o.kfXuq2FzMAGbkQBy.inlEXgMvgHl69X2agHpHnzbNXFU53eO', '0793878068', '7434b9dfd2ccf1ac59d260a449789d8390d69730b4bfe7e70597d65b176c1843', 'b7f7998a31fb86cc533e29bd3a71bdbd', '43e3b4d851c2933273e964e9f58c5943', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 14:52:03'),
(29, 'Samwel Karanja Njoroge', 'samwelnjorge757@gmail.com', 'Client', '$2y$10$slElAD4498gMEAGNmMqz6uvLL/6d3X5g6JaJl.0augiQ1Xq4FsZYq', '0793878068', 'bbc4684e70868ef4646a612bafa5f49e3161969fe2f4a36f0534385d40610623', '27869a4bb79d788ab5c24d64e85568e0', '42dd33687f03b089488b2708324a09c2', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 15:04:33'),
(30, 'Samwel Karanja Njoroge', 'samw1elnjoroge757@gmail.com', 'Client', '$2y$10$TKyTrJVpyiXITcUY/QuxpOqNzKgCIEPBf2nzpgua/0BVoaEvirY5W', '0793878068', 'bb968108d36f907e8b81c2c336d519b1aab61f47213c8aab7e025bb08654f67c', 'bc24c7ad998a54048acba3e99490dad2', '1a4aecebc9b882ed97aa54f1b888de8f', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 15:08:28'),
(31, 'Brian K', 'qsamwelnjoroge757@gmail.com', 'Client', '$2y$10$xD8C0TYhlPhlrQ792QmgheIReYsfJwzBR/ijKbzkOiAgi0NI2FWke', '0793878068', '6270740b5f5975e31991ac388633efdcb95a3dddbe5c2f67e9d2ce5b975b7608', 'f4770fc7e78ebb870fa53cc8aa7c8cd7', 'fef32fcb2386773e6dfa520acadd1477', '1264', 'Thika', 'EMBU', '01000', '2025-05-21 15:10:56'),
(32, 'James Mutuiri', 'samwelnjoroge557@gmail.com', 'Client', '$2y$10$z7z7814fYKDjk5qtxXCqw.1ObgGOEhLcXz5yVumccuVtrEYBkBk6K', '0793878068', '1a4548e1136ff0558839e0f264c328fe39e50c60ae616f0db2a2e84124410416', '4476302bfb887241e4b44feb46852d54', 'd94fd8f75251ba7bfad8d40a1b854671', 'Thika', 'Embu', 'EMBU', '60100', '2025-05-27 19:07:29'),
(33, 'Samwel Karanja Njoroge', 'samwqwvcelnjoroge757@gmail.com', 'Client', '$2y$10$sEDZvJAh1pb/F1ACmqLx0OK679Dfa8YoOeZOgnO7IOO3.DKHVJRhu', '0793878068', 'f11edd3bdb6aeaa89c7aaa31409198b2fb1e1c5a599f2ad1594d41fe34827e9f', '083d4b28b66b3c0ab4d10bef0d2bdb22', 'c27819c758311557ed822ff8eecb0df4', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 20:13:33'),
(34, 'Samwel Karanja Njoroge', 'samqwelnjoroge757@gmail.com', 'Client', '$2y$10$9EcRcyhCRgStObgeBCYuYeuyL0oPJdnTf/MEqCUx.kQO/.815Q/K.', '0793878068', 'cc58a2d993afbe645710c8f5794a1d2dcc093590efb835938775f8d7b43bb479', 'c22b615b9b6a92dde6fcbbdd63c05ec1', '6e370bf382fe7a690d7986aba2097d72', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 20:18:33'),
(35, 'Samwel Karanja Njoroge', 's1111amwelnjoroge757@gmail.com', 'Client', '$2y$10$JPz7xKOIlY1Prg1VctTDfOS56N19EJHBW17AVPy1mwr8zD60r5Za6', '0793878068', '35b05f24cb9a9cc76a78984c099aec39aff082c5a5c1b818b2875ed615c2b6be', '76b9ad4c7c60176f15d82be4bdd13d57', '358fa528bb293dd7d215531c1cbaec46', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 20:19:18'),
(36, 'Samwel Karanja Njoroge', 'qsasamwelnjoroge757@gmail.com', 'Client', '$2y$10$OiFF2AsjtWmfU6fY0NkyG.e../mQjN242Rbf593o4dcJHNyo7NqdW', '0793878068', 'caabff88c837d58033d9e036b798cf5954396a02eae20f37109afc7bab71130a', '2e26acdff00a9259b84feb9c93199102', '6f73cdaea4c23467ea9939feaade3674', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 20:29:04'),
(37, 'Samwel Karanja Njoroge', 'samw888elnjoroge757@gmail.com', 'Client', '$2y$10$G1LfW2d.L0Z2A.tJsH89FeWbKwG6xJSRVmRCCgb14kVh7JfQ5aJyi', '0793878068', '04c96e2b762720f60837b1330c38c33a12b7e717475af44854f75f0ec4d59faa', '7ab97d701af5dd2fe3e970a2090ef7e9', 'fa88747cf115251689d9ef531d2d1609', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 20:33:53'),
(38, 'Samwel Karanja Njoroge', 'sam78welnjoroge757@gmail.com', 'Client', '$2y$10$.C3Hs3B2YdC6dnjHBwDdUeTkqGJIdYufugIpkNs9aHgXL//F2TdaO', '0793878068', '5ff4aaac7a32481663a8876e3c7cdf8a93f847e0da66bd8082eaa82b6ffa5b47', '2f8024976d9c535aec81955dc5a69620', 'c23e66def0727b77ae29775d8da9843a', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 20:37:22'),
(39, 'Samwel Karanja Njoroge', 'samwe00lnjoroge757@gmail.com', 'Client', '$2y$10$.syYH53j3skGAjW/rNzD6eRWF0C9aM6lvtsMVRBNTAc7llNjAHr92', '0793878068', '4dff45e82627d0e312ac39ef1a9f56ff9495f77f989ebb1c70154804452954fb', '229a1bb853f041d873470eea84c37294', 'b20dff318fe1d81ba5e9f1596b08939d', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 21:13:39'),
(40, 'Test User', 'test@example.com', 'Client', '$2y$10$ezFasWQle7Y63ERnHRyrVeH03BCb3gry5sX.GcyHK9RA2ygHbL1Y6', NULL, '', '', '', NULL, NULL, NULL, NULL, '2025-05-28 21:37:37'),
(41, 'Samwel Karanja Njoroge', '78samwelnjoroge757@gmail.com', 'Client', '$2y$10$8CzwJdh4iRCSkBtkEz.N6OLEJfTAhYwFNaDjuVZFn28Ab4VdEEcHe', '0793878068', '902cdb25ed6148984cb503e76354d718562d086f2ac59b90864e48bbad6bcb60', '9078e97295ae3296747a11fd3e2578b6', '6001eac626fa8fa91dc4a7707e1c7c24', '1264', 'Thika', 'EMBU', '01000', '2025-05-28 22:36:15');

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
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;

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
(39, 20, 'janengari3467@gmail.com', '0793715233', '::1', '2025-06-01 20:26:25', NULL, NULL, '2025-06-01 17:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
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

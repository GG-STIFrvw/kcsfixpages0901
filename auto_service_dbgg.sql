-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2025 at 11:49 AM
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
-- Database: `auto_service_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled','declined') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bay_id` int(11) DEFAULT NULL,
  `mechanic_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `vehicle_id`, `service_id`, `scheduled_date`, `scheduled_time`, `status`, `notes`, `created_at`, `bay_id`, `mechanic_id`) VALUES
(1, 5, 1, 1, '2025-08-30', '10:19:00', 'completed', 'inventory test', '2025-08-28 13:20:11', 1, 3),
(2, 5, 2, 1, '2025-08-30', '10:19:00', 'in_progress', 'Track your vehicle service progress', '2025-08-29 14:20:01', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_services`
--

CREATE TABLE `appointment_services` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bays`
--

CREATE TABLE `bays` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bays`
--

INSERT INTO `bays` (`id`, `name`, `status`) VALUES
(1, 'Bay 1', 'Not Available'),
(2, 'Bay 2', 'Not Available'),
(3, 'Bay 3', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_code` varchar(15) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `product_category` enum('A/C Products','Adhesives & Tapes','Automotive Accessories','Automotive Care Products','Automotive Maintenance','Automotive Parts','Chemical','Component','Detailing & Paint Care','Electrical Component','Electrical Consumables','Electrical Parts','Electrical Supplies','Fluids & Lubricants','Hardware & Fastener','Paint Remover & Degreaser','Paints & Coatings','Refinish Product','Rugs & Floor Mats','Seals & Gaskets','Welding & Fabrication') DEFAULT NULL,
  `brand` enum('ZIC','AGPHA','ANZAHL','BESCO','BOSCH','FABRA','FARECLA','GARD','GLASURITT','HERMES','HIPPO','HUDSON','JOHNSEN','LUBRIGOLD','LUCKY','MAG 1','OLYMPIA','OMEGA','OSRAM','PRO-99','STP','TCL','TOYOTA','VALVOLINE','WHIZ','WURTH','ZEBRA','MARFAK') DEFAULT NULL,
  `unit` enum('Liter','Meter','Box','Gallon','Volt','Pack','Bottle','Quart','Kilogram','Gram','Millimeter','Piece','Can','Milliliter','Milligram','Watt') DEFAULT NULL,
  `category` enum('non-moving','slow-moving','fast-moving') DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reorder_threshold` int(11) DEFAULT NULL,
  `kcs_purchasePrice` decimal(10,0) NOT NULL DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `location` enum('Rack A1','Rack A2','Rack A3','Rack A4','Rack A5','Rack B1','Rack B2','Rack B3','Rack B4','Rack B5','Rack C1','Rack C2','Rack C3','Rack C4','Rack C5','Rack D1','Rack D2','Rack D3','Rack D4','Rack D5','Rack E1','Rack E2','Rack E3','Rack E4','Rack E5','Rack F1','Rack F2','Rack F3','Rack F4','Rack F5','Rack G1','Rack G2','Rack G3','Rack G4','Rack G5','Rack H1','Rack H2','Rack H3','Rack H4','Rack H5','Rack I1','Rack I2','Rack I3','Rack I4','Rack I5','Rack J1','Rack J2','Rack J3','Rack J4','Rack J5','Rack K1','Rack K2','Rack K3','Rack K4','Rack K5','Rack L1','Rack L2','Rack L3','Rack L4','Rack L5','Rack M1','Rack M2','Rack M3','Rack M4','Rack M5','Rack N1','Rack N2','Rack N3','Rack N4','Rack N5','Rack O1','Rack O2','Rack O3','Rack O4','Rack O5','Rack P1','Rack P2','Rack P3','Rack P4','Rack P5','Rack Q1','Rack Q2','Rack Q3','Rack Q4','Rack Q5','Rack R1','Rack R2','Rack R3','Rack R4','Rack R5','Rack S1','Rack S2','Rack S3','Rack S4','Rack S5','Rack T1','Rack T2','Rack T3','Rack T4','Rack T5','Rack U1') NOT NULL,
  `supplier` enum('SANZEI','QC NGK','TORCHERE','SPANDEX','PRIMAL','WURTH','JKSS TRADING','WELCOME','JMJC','GATY','MAR RAMOS','RLC PAINT','GOLDEN JUBILEE','1C DAPITAN','ERCL','TLB HARDWARE','RED OCTAGON','STR8LUCK','GOLDEN RUSH','BOP PENA') DEFAULT NULL,
  `lastPurchase_date` date NOT NULL,
  `expiry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `job_order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quantity_used` int(11) DEFAULT NULL,
  `action` enum('add','withdraw') DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_orders`
--

CREATE TABLE `job_orders` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `mechanic_id` int(11) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `status` enum('assigned','in_progress','completed') DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_orders`
--

INSERT INTO `job_orders` (`id`, `appointment_id`, `mechanic_id`, `diagnosis`, `status`, `image_path`) VALUES
(1, 1, 2, 'staff4 - inv test', 'completed', NULL),
(2, 2, 2, 'Track your vehicle service progress', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `log_time`, `ip_address`, `user_agent`) VALUES
(1, 4, 'User \'inventory\' logged in.', '2025-08-28 12:44:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(2, 4, 'User \'inventory\' logged in.', '2025-08-28 13:17:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(3, 5, 'User \'MM\' logged in.', '2025-08-28 13:18:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(4, 2, 'User \'staff4\' logged in.', '2025-08-28 13:19:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(5, 2, 'User \'staff4\' logged out.', '2025-08-28 13:24:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(6, 5, 'User \'MM\' logged out.', '2025-08-28 13:25:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(7, 4, 'User \'inventory\' logged in.', '2025-08-28 13:56:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(8, 4, 'User \'inventory\' logged in.', '2025-08-29 04:04:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(9, 4, 'User \'inventory\' logged out.', '2025-08-29 06:11:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(10, 4, 'User \'inventory\' logged in.', '2025-08-29 06:11:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(11, 4, 'User \'inventory\' logged in.', '2025-08-29 06:13:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(12, 4, 'User \'inventory\' logged out.', '2025-08-29 06:30:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(13, 1, 'User \'admin1\' logged in.', '2025-08-29 06:30:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(14, 1, 'User \'admin1\' logged out.', '2025-08-29 06:32:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(15, 1, 'User \'admin1\' logged in.', '2025-08-29 06:32:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(16, 1, 'User \'admin1\' logged out.', '2025-08-29 06:33:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(17, 4, 'User \'inventory\' logged in.', '2025-08-29 06:33:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(18, 4, 'User \'inventory\' logged out.', '2025-08-29 06:37:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(19, 1, 'User \'admin1\' logged in.', '2025-08-29 06:37:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(20, NULL, 'Failed login attempt for username \'MM\'.', '2025-08-29 06:50:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(21, 5, 'User \'MM\' logged in.', '2025-08-29 06:50:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(22, 1, 'User \'admin1\' logged in.', '2025-08-29 09:56:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(23, NULL, 'Failed login attempt for username \'MM\'.', '2025-08-29 10:07:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(24, 5, 'User \'MM\' logged in.', '2025-08-29 10:07:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(25, NULL, 'Failed login attempt for username \'admin1\'.', '2025-08-29 10:35:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(26, 1, 'User \'admin1\' logged in.', '2025-08-29 10:36:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(27, 1, 'User \'admin1\' logged out.', '2025-08-29 10:39:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(28, 2, 'User \'staff4\' logged in.', '2025-08-29 10:39:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(29, 2, 'User \'staff4\' logged out.', '2025-08-29 10:45:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(30, 2, 'User \'staff4\' logged in.', '2025-08-29 10:46:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(31, NULL, 'Failed login attempt for username \'admin1\'.', '2025-08-29 11:16:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(32, 1, 'User \'admin1\' logged in.', '2025-08-29 11:16:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(33, 1, 'User \'admin1\' logged out.', '2025-08-29 11:17:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(34, 5, 'User \'MM\' logged in.', '2025-08-29 13:31:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(35, 5, 'User \'MM\' logged in.', '2025-08-29 14:17:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'),
(36, 2, 'User \'staff4\' logged in.', '2025-08-29 14:20:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `status`, `created_at`) VALUES
(1, 5, 'Your appointment for Subaru Innova (Plate: Subaru123) on 2025-08-30 at 10:19:00 for Change Oil has been confirmed.', 'read', '2025-08-28 13:20:18'),
(2, 5, 'Hi MM, a new quotation has been generated for Job Order #1.', 'read', '2025-08-28 13:21:11');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('paid','unverified') DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `quotation_id`, `reference_number`, `amount`, `status`, `payment_date`, `receipt_path`) VALUES
(1, 5, 1, '123', 5154.00, 'paid', '2025-08-28', 'uploads/1756387384_68b0583872d8c_Pay-QR4-1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quote_details` longtext DEFAULT NULL,
  `decline_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `job_order_id`, `amount`, `status`, `created_at`, `quote_details`, `decline_note`) VALUES
(1, 1, 5154.00, '', '2025-08-28 13:21:11', '\r\n                Quotation Preview\r\n                Dear MM,Thank you for choosing our services. Here is the quotation for your vehicle:\r\n                \r\n                    \r\n                        \r\n                            Description\r\n                            Cost (PHP)\r\n                        \r\n                    \r\n                    Change Oil (Base Service)1500.001 LTR ZIC X3000 MOTOR OIL 15W-40 x103654.00\r\n                    \r\n                        \r\n                            Total Estimated Cost\r\n                            ₱ 5154.00\r\n                        \r\n                    \r\n                \r\n                Notes: This quotation is valid for 30 days. Prices are subject to change based on final inspection.\r\n            ', NULL),
(2, 2, 1920.00, 'pending', '2025-08-29 14:22:10', '\r\n                Quotation Preview\r\n                \r\n                \r\n                    \r\n                        \r\n                            Description\r\n                            Cost (PHP)\r\n                        \r\n                    \r\n                    \r\n                    \r\n                        \r\n                            Total Estimated Cost\r\n                            \r\n                        \r\n                    \r\n                \r\n                \r\n            ', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_products`
--

CREATE TABLE `quotation_products` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_products`
--

INSERT INTO `quotation_products` (`id`, `quotation_id`, `product_id`, `quantity`, `price_per_unit`) VALUES
(1, 1, 2, 10, 365.40),
(2, 2, 1, 1, 420.00);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` enum('Available','Not Available') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `cost`, `status`) VALUES
(1, 'Change Oil', 'Change oil service', 1500.00, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `unavailable_dates`
--

CREATE TABLE `unavailable_dates` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unavailable_dates`
--

INSERT INTO `unavailable_dates` (`id`, `date`) VALUES
(3, '2025-08-01'),
(2, '2025-08-04'),
(1, '2025-08-28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','staff','inventory_manager','mechanic','customer') DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `home_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `full_name`, `contact_number`, `home_address`, `created_at`, `status`, `email_verified`, `verification_token`, `profile_picture`) VALUES
(1, 'admin1', '$2y$10$9u9Unuwq/Orfcke7hfwdguQMpGrn.2uEltriQArH813/3YpYXHstO', 'kcsadmn20@gmail.com', 'admin', 'admin number1', '09', '', '2025-05-05 07:35:46', 'active', 1, NULL, '1_68b1833b28c8a.jpg'),
(2, 'staff4', '$2y$10$tQOVNK4L/tRU1NyOAICKfetH52cr5dvir7U5nW6NvjFsiuDoeiZZ2', 'kcsautorepair09@gmail.com', 'staff', 'staff4 testing', NULL, NULL, '2025-05-05 12:33:11', 'active', 1, NULL, '9_pic.jpg'),
(3, 'mechanic1', '$2y$10$Wf4.kmNe9srboNdCWGjI1.aC.90i5/vEa3rHevy2SP9ZYlmMlzHCO', 'test@mechanic.com', 'mechanic', 'mechanic testing 1', NULL, NULL, '2025-05-06 17:14:46', 'active', 0, NULL, NULL),
(4, 'inventory', '$2y$10$ZsyzdTgiJB/pB6j0PoiXi.kYhx90zBy.rRFsjC/yEdaWKI2nsfWyu', 'kcsinventory71@gmail.com', 'inventory_manager', 'Bernald Solomon', NULL, NULL, '2025-05-12 11:53:21', 'active', 1, NULL, NULL),
(5, 'MM', '$2y$10$4SfFW/XJdt/4zjzurPA.8Ou6lR99K1kZ616LlE9lWMkHzUF3xOBgO', 'sm.snfs.m.btd.ndn.y@gmail.com', 'customer', 'MM', '09875653214', 'Fairmont', '2025-07-23 08:55:14', 'active', 1, NULL, '5_68adb11dd8f37.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `plate_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `brand`, `model`, `plate_number`) VALUES
(1, 5, 'Subaru', 'Innova', 'Subaru123'),
(2, 5, 'Toyota', 'Hillux', 'HHH-123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `fk_appointments_bay` (`bay_id`),
  ADD KEY `fk_appointments_mechanic` (`mechanic_id`);

--
-- Indexes for table `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `bays`
--
ALTER TABLE `bays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `job_order_id` (`job_order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `job_orders`
--
ALTER TABLE `job_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `mechanic_id` (`mechanic_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_order_id` (`job_order_id`);

--
-- Indexes for table `quotation_products`
--
ALTER TABLE `quotation_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bays`
--
ALTER TABLE `bays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_orders`
--
ALTER TABLE `job_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quotation_products`
--
ALTER TABLE `quotation_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `fk_appointments_bay` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`),
  ADD CONSTRAINT `fk_appointments_mechanic` FOREIGN KEY (`mechanic_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`),
  ADD CONSTRAINT `inventory_log_ibfk_2` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`),
  ADD CONSTRAINT `inventory_log_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `job_orders`
--
ALTER TABLE `job_orders`
  ADD CONSTRAINT `job_orders_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `job_orders_ibfk_2` FOREIGN KEY (`mechanic_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

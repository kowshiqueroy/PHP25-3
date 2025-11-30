-- SQL Setup Script for Sales Management System
-- This script drops the existing database and tables, then recreates the schema and initial data.

-- Disable foreign key checks to avoid errors during dropping tables
SET FOREIGN_KEY_CHECKS=0;

-- Drop the database if it exists
DROP DATABASE IF EXISTS `sales_management`;

-- Create the database
CREATE DATABASE `sales_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sales_management`;

--
-- Drop existing tables if they exist
--
DROP TABLE IF EXISTS `system_logs`;
DROP TABLE IF EXISTS `invoice_items`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `shops`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `companies`;

--
-- Table structure for table `companies`
--
CREATE TABLE `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Manager', 'SR', 'Viewer') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

--
-- Table structure for table `routes`
--
CREATE TABLE `routes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_route_per_company` (`company_id`, `name`)
) ENGINE=InnoDB;

--
-- Table structure for table `shops`
--
CREATE TABLE `shops` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `route_id` INT NOT NULL,
  `company_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_shop_per_route` (`route_id`, `name`)
) ENGINE=InnoDB;

--
-- Table structure for table `items`
--
CREATE TABLE `items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `rate` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_item_per_company` (`company_id`, `name`)
) ENGINE=InnoDB;

--
-- Table structure for table `invoices`
--
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_serial` VARCHAR(20) UNIQUE,
  `company_id` INT NOT NULL,
  `sr_id` INT NOT NULL,
  `route_id` INT NOT NULL,
  `shop_id` INT NOT NULL,
  `order_date` DATE NOT NULL,
  `delivery_date` DATE NOT NULL,
  `grand_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `remarks` TEXT,
  `status` ENUM('Draft', 'Confirmed', 'Approved', 'Rejected', 'On Process', 'On Delivery', 'Delivered', 'Returned', 'Damaged') NOT NULL DEFAULT 'Draft',
  `submitted_at` TIMESTAMP NULL DEFAULT NULL,
  `print_queue_order` INT NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sr_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`shop_id`) REFERENCES `shops`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

--
-- Table structure for table `invoice_items`
--
CREATE TABLE `invoice_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `rate` DECIMAL(10, 2) NOT NULL,
  `total` DECIMAL(12, 2) NOT NULL,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

--
-- Table structure for table `system_logs`
--
CREATE TABLE `system_logs` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action_type` VARCHAR(50) NOT NULL,
  `details` TEXT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

--
-- Insert Initial Data
--

-- 1. Default Company
INSERT INTO `companies` (`id`, `name`) VALUES (1, 'Default Corp');

-- 2. Users (Password is 'password' for all)
-- Admin (has no company ID, global access)
INSERT INTO `users` (`id`, `company_id`, `username`, `password_hash`, `role`) VALUES
(1, NULL, 'admin', '$2y$10$nptndkIgBxdSWpE3Dfnn7uLeYyZBrIicQ88SSbEIfWCfvieCHDTHC', 'Admin');

-- Manager for Default Corp
INSERT INTO `users` (`id`, `company_id`, `username`, `password_hash`, `role`) VALUES
(2, 1, 'manager1', '$2y$10$nptndkIgBxdSWpE3Dfnn7uLeYyZBrIicQ88SSbEIfWCfvieCHDTHC', 'Manager');

-- Sales Rep for Default Corp
INSERT INTO `users` (`id`, `company_id`, `username`, `password_hash`, `role`) VALUES
(3, 1, 'sr1', '$2y$10$nptndkIgBxdSWpE3Dfnn7uLeYyZBrIicQ88SSbEIfWCfvieCHDTHC', 'SR');

-- Viewer for Default Corp
INSERT INTO `users` (`id`, `company_id`, `username`, `password_hash`, `role`) VALUES
(4, 1, 'viewer1', '$2y$10$nptndkIgBxdSWpE3Dfnn7uLeYyZBrIicQ88SSbEIfWCfvieCHDTHC', 'Viewer');

-- Log the initial setup
-- Note: We can't log to system_logs before users are created, so this must come after user insertion.
-- The user_id=1 corresponds to the 'admin' user.
INSERT INTO `system_logs` (`user_id`, `action_type`, `details`) VALUES
(1, 'SETUP', 'Initial database schema created and test users inserted.');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

COMMIT;
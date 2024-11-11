-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2024 at 06:48 PM
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
-- Database: `auction`
CREATE DATABASE IF NOT EXISTS auction;
USE auction;
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,  -- Increased length to accommodate longer emails.
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, -- Increased length to accommodate hashed passwords
  `average_rating` int(100) UNSIGNED NOT NULL DEFAULT 0,
  `accountType` varchar(30) NOT NULL,
  PRIMARY KEY (`username`)  -- Added PRIMARY KEY for `username`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_section` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`category_id`),  -- Added PRIMARY KEY.
  UNIQUE KEY `unique_category` (`category_name`, `category_section`)  -- Ensure the combination is unique
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

-- Inserting categories as these are going to be 'hardcoded' 

INSERT IGNORE INTO categories (category_section, category_name)
VALUES
('Do it yourself!', 'Knit patterns'),
('Do it yourself!', 'Crochet patterns'),
('Do it yourself!', 'Project kits'),
('Do it yourself!', 'Other DIY'),

('Clothes & Accessories', 'Sweaters & Cardigans'),
('Clothes & Accessories', 'Headwear'),
('Clothes & Accessories', 'Scarves'),
('Clothes & Accessories', 'Mittens'),
('Clothes & Accessories', 'Socks'),
('Clothes & Accessories', 'Baby clothing'),
('Clothes & Accessories', 'Other clothes'),

('Home & Toys', '⁠Amigurumi & Toys'),
('Home & Toys', 'Blankets'),
('Home & Toys', 'Pillows & Cushion covers'),
('Home & Toys', 'Other home decor');

SELECT * 
FROM `categories`
ORDER BY 
    FIELD(`category_section`, 'Do it yourself!', 'Clothes & Accessories', 'Home & Toys'),  -- Custom order for category_section
    `category_id`; 

-- --------------------------------------------------------

--
-- Table structure for table `auction`
--
-- This table now combines item and auction details (item_id replaced by auction_id).
--

CREATE TABLE IF NOT EXISTS `auction` (
  `auction_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,  -- Primary Key for the auction.
  `item_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `item_description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,  -- Increased length for description.
  `category_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `starting_price` int(10) UNSIGNED NOT NULL,
  `reserve_price` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `auction_status` ENUM('active', 'closed') NOT NULL,  -- Using ENUM for status.
  PRIMARY KEY (`auction_id`),  -- PRIMARY KEY for the auction.
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),  -- {FK} to `categories`.
  FOREIGN KEY (`username`) REFERENCES `users` (`username`)  -- {FK} to `users`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE IF NOT EXISTS `bids` (
  `bid_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL, 
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `bid_amount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bid_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bid_id`),  -- PRIMARY KEY for `bid_id`.
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`),  -- {FK} to `auction`.
  FOREIGN KEY (`username`) REFERENCES `users` (`username`)  -- {FK} to `users`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `closed_auctions`
--
-- Tracking the final sale details for auctions that have ended.
--

CREATE TABLE IF NOT EXISTS `closed_auctions` (
  `win_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL, 
  `bid_id` int(10) UNSIGNED NOT NULL,  -- Bid that won the auction.
  `seller_rating` int(10) UNSIGNED NOT NULL,
  `buyer_rating` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`win_id`),  -- PRIMARY KEY for `win_id`.
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`),  -- {FK} to `auction`.
  FOREIGN KEY (`bid_id`) REFERENCES `bids` (`bid_id`)  -- {FK} to `bids`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--
-- This table tracks sales transactions for items sold by users.
--

CREATE TABLE IF NOT EXISTS `sales` (
  `sale_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL, 
  `seller_username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,  
  `buyer_username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,  
  `sale_price` int(10) UNSIGNED NOT NULL,  -- Price at which the item was sold.
  PRIMARY KEY (`sale_id`),  -- PRIMARY KEY for `sales`.
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`),  -- {FK} to `auction`.
  FOREIGN KEY (`seller_username`) REFERENCES `users` (`username`),  -- {FK} to `users` (seller).
  FOREIGN KEY (`buyer_username`) REFERENCES `users` (`username`)  -- {FK} to `users` (buyer).
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--
-- This table tracks purchases made by users.
--

CREATE TABLE IF NOT EXISTS `purchases` (
  `purchase_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `buyer_username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,  -- Buyer's username.
  `purchase_price` int(10) UNSIGNED NOT NULL,  -- Price at which the item was purchased.
  PRIMARY KEY (`purchase_id`),  -- PRIMARY KEY for `purchases`.
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`),  -- {FK} to `auction`.
  FOREIGN KEY (`buyer_username`) REFERENCES `users` (`username`)  -- {FK} to `users` (buyer).
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table 'watchlist' 
-- This table tracks which auction users are watching

CREATE TABLE IF NOT EXISTS `watchlist` (
  `watch_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,  -- username.
  `auction_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`watch_id`),  -- PRIMARY KEY for `watchlist`.
  FOREIGN KEY (`username`) REFERENCES `users` (`username`),  -- {FK} to `users`.
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`)  -- {FK} to `auction`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

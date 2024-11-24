-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 06:48 PM
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
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `average_rating` int(100) UNSIGNED NOT NULL DEFAULT 0,
  `accountType` varchar(30) DEFAULT 'user',
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users (username, email, password, average_rating, accountType)
VALUES 
('admin', 'admin@admin.com', 'admin1234', 0, 'admin'),
('ola', 'ola@ola.com', 'user1234', 6, 'user'),
('rachel', 'rachel@rachel.com', 'user1234', 9, 'user'),
('jingyi', 'jingyi@jingyi.com', 'user1234', 7, 'user'),
('user4', 'user4@user.com', 'user1234', 6, 'user'),
('user5', 'user5@user.com', 'user1234', 4, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_section` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `unique_category` (`category_name`, `category_section`)
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
('Home & Toys', 'Amigurumi & Toys'),
('Home & Toys', 'Blankets'),
('Home & Toys', 'Pillows & Cushion covers'),
('Home & Toys', 'Other home decor');

SELECT * 
FROM `categories`
ORDER BY 
    FIELD(`category_section`, 'Do it yourself!', 'Clothes & Accessories', 'Home & Toys'),
    `category_id`; 

-- --------------------------------------------------------
-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE IF NOT EXISTS `sizes` (
  `size_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `size` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`size_id`),
  UNIQUE KEY `unique_size` (`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Inserting sizes as these are going to be 'hardcoded' 

INSERT IGNORE INTO sizes (size)
VALUES
('XS'), ('S'), ('M'),('L'),('XL'), ('one-size');

SELECT * 
FROM `sizes`
ORDER BY `size_id`;
    
-- --------------------------------------------

-- Table structure for table `materials`
--

CREATE TABLE IF NOT EXISTS `materials` (
  `material_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `material` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`material_id`),
  UNIQUE KEY `unique_material` (`material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Inserting categories as these are going to be 'hardcoded' 

INSERT IGNORE INTO materials (material)
VALUES
('Cotton'), ('Wool'), ('Polyester'),('Acrylic'), ('Other');

-- --------------------------------------------

-- Table structure for table `colors`
--

CREATE TABLE IF NOT EXISTS `colors` (
  `color_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `color` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`color_id`),
  UNIQUE KEY `unique_color` (`color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Inserting colors as these are going to be 'hardcoded' 

INSERT IGNORE INTO colors (color)
VALUES
('White'), ('Black'), ('Grey'),('Brown'),('Red'),('Green'),('Blue'),('Yellow'),('Orange'),('Purple'),('Multicolor');

-- --------------------------------------------

--
-- Table structure for table `auction`
--

CREATE TABLE IF NOT EXISTS `auction` (
  `auction_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `starting_price` int(10) UNSIGNED NOT NULL,
  `reserve_price` int(10) UNSIGNED NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `auction_status` ENUM('active', 'closed') NOT NULL,
  `image_path` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `material_id` int(10) UNSIGNED DEFAULT NULL,
  `item_condition` ENUM('new', 'used') NOT NULL DEFAULT 'new',
  `color_id` int(10) UNSIGNED DEFAULT NULL,
  `size_id` int(10) UNSIGNED DEFAULT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`auction_id`),
  UNIQUE KEY `unique_auction` (`username`, `item_name`),
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`),
  FOREIGN KEY (`color_id`) REFERENCES `colors` (`color_id`),
  FOREIGN KEY (`size_id`) REFERENCES `sizes` (`size_id`)
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserting dummy data into the auction table

INSERT INTO `auction` (`item_name`, `item_description`, `category_id`, `username`, `starting_price`, `reserve_price`, `start_date`, `end_date`, `auction_status`, `image_path`, `material_id`, `item_condition`, `color_id`, `size_id`, `views`)
VALUES
-- Active Listings
('Cozy Wool Sweater', 'A warm and cozy hand-knitted sweater perfect for chilly days.', 5, 'ola', 60, 40, '2024-11-11', '2024-12-04', 'active', 'test.jpg', 2, 'new', 1, 3, 5),
('Amigurumi Elephant', 'A cute and soft crochet elephant perfect for kids.', 12, 'rachel', 30, 20, '2024-11-12', '2024-12-05', 'active', 'test.jpg', 1, 'new', 3, 6, 3),
('Crochet Blanket Pattern', 'DIY crochet pattern for a beautiful blanket. Instant digital download.', 13, 'jingyi', 10, 10, '2024-11-15', '2024-12-06', 'active', 'test.jpg', 2, 'new', 7, 6, 8),
('Cozy Knit Blanket', 'A warm and comfortable hand-knitted blanket. Perfect for cold evenings and snuggling up.', 13, 'user4', 50, 30, '2024-11-10', '2024-12-07', 'active', 'test.jpg', 2, 'new', 7, 4, 15),
('Colourful Crochet Shawl', 'A vibrant shawl featuring a beautiful crochet pattern. Lightweight and perfect for any occasion.', 2, 'user5', 40, 20, '2024-11-08', '2024-12-08', 'active', 'test.jpg', 1, 'new', 11, 3, 12),
('Soft Wool Socks', 'Handmade wool socks that will keep your feet toasty during winter. Unisex, one size fits all.', 9, 'ola', 25, 10, '2024-11-09', '2024-12-09', 'active', 'test.jpg', 2, 'new', 1, 6, 8),
('Baby Blue Cardigan', 'A charming knitted cardigan in baby blue, perfect for layering in the winter months.', 5, 'rachel', 45, 25, '2024-11-11', '2024-12-10', 'active', 'test.jpg', 4, 'new', 7, 2, 20),
('Granny Square Throw', 'A stunning granny square throw blanket with mixed colours. Ideal as a gift or home decor.', 13, 'jingyi', 60, 40, '2024-11-07', '2024-12-11', 'active', 'test.jpg', 5, 'used', 11, 4, 10),
('Handmade Crochet Toy Bear', 'Adorable crochet bear made from 100% cotton yarn. Safe for children and makes a perfect cuddly friend.', 12, 'user4', 35, 15, '2024-11-09', '2024-12-12', 'active', 'test.jpg', 1, 'new', 4, 3, 18),
('Wool Mittens', 'Cozy wool mittens that are perfect for cold weather. Hand-knitted with love.', 8, 'user5', 120, 20, '2024-11-10', '2024-12-13', 'active', 'test.jpg', 2, 'new', 3, 1, 13),
('Crochet Baby Blanket', 'Handmade baby blanket made from soft yarn, perfect as a baby shower gift.', 13, 'ola', 35, 25, '2024-11-13', '2024-12-14', 'active', 'test.jpg', 2, 'new', 8, 6, 9),
('Knitted Wool Hat', 'A stylish knitted hat to keep you warm during winter.', 6, 'rachel', 25, 15, '2024-11-14', '2024-12-15', 'active', 'test.jpg', 2, 'new', 7, 3, 11),
('Rainbow Crochet Blanket', 'Colourful crochet blanket, perfect for adding some brightness to your room.', 13, 'jingyi', 75, 50, '2024-11-12', '2024-12-16', 'active', 'test.jpg', 1, 'new', 11, 4, 18),
('Handmade Cotton Headband', 'A soft and comfortable handmade headband, perfect for daily use.', 6, 'user4', 15, 8, '2024-11-15', '2024-12-17', 'active', 'test.jpg', 1, 'new', 2, 6, 7),
('Knit Fingerless Gloves', 'Hand-knitted fingerless gloves, perfect for staying warm while using your phone.', 8, 'user5', 30, 18, '2024-11-14', '2024-12-18', 'active', 'test.jpg', 2, 'new', 5, 2, 14),

-- Closed listings
('Vintage Wool Sweater', 'A vintage sweater made from wool. Slightly worn, but in good condition.', 5, 'ola', 45, 30, '2024-11-01', '2024-11-15', 'closed', 'test.jpg', 2, 'used', 1, 3, 25),
('Crochet Pillow Set', 'A set of 2 handmade crochet pillows. Adds a cozy touch to your living room.', 14, 'rachel', 60, 40, '2024-11-02', '2024-11-16', 'closed', 'test.jpg', 1, 'new', 7, 4, 30),
('Acrylic Yarn Bundle', 'A bundle of colourful acrylic yarns, perfect for multiple DIY projects.', 3, 'jingyi', 30, 20, '2024-11-03', '2024-11-17', 'closed', 'test.jpg', 4, 'new', 11, 6, 22);

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
  PRIMARY KEY (`bid_id`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`),
  FOREIGN KEY (`username`) REFERENCES `users` (`username`)
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `highest_bids`
--
-- This table stores the highest bid for each auction for faster access.
--

CREATE TABLE IF NOT EXISTS `highest_bids` (
  `auction_id` int(10) UNSIGNED NOT NULL,
  `highest_bid` DECIMAL(10, 2) NOT NULL,
  `last_bidder` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`auction_id`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`) 
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `closed_auctions`
--

CREATE TABLE IF NOT EXISTS `closed_auctions` (
  `win_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `bid_id` int(10) UNSIGNED NOT NULL,
  `seller_rating` int(10) UNSIGNED NOT NULL,
  `buyer_rating` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`win_id`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`),
  FOREIGN KEY (`bid_id`) REFERENCES `bids` (`bid_id`)
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE IF NOT EXISTS `sales` (
  `sale_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `seller_username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `buyer_username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sale_price` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`sale_id`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`seller_username`) REFERENCES `users` (`username`),
  FOREIGN KEY (`buyer_username`) REFERENCES `users` (`username`)
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales` (`auction_id`,`seller_username`,`buyer_username`,`sale_price`)
VALUES
(16,'ola','rachel',40),
(17,'rachel','jingyi',50),
(18,'jingyi','ola',25);

-- --------------------------------------------------------


-- Table structure for table 'watchlist' 

CREATE TABLE IF NOT EXISTS `watchlist` (
  `watch_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `auction_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`watch_id`),
  UNIQUE KEY `unique_watchlist` (`username`, `auction_id`),
  FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`)
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `user_views`

CREATE TABLE IF NOT EXISTS `user_views` (
  `view_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`view_id`),
  UNIQUE KEY `unique_user_views` (`username`, `auction_id`),
  FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`) 
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Triggers for automatic updates

DELIMITER $$

CREATE TRIGGER `update_highest_bid`
AFTER INSERT ON `bids`
FOR EACH ROW
BEGIN
  INSERT INTO `highest_bids` (`auction_id`, `highest_bid`, `last_bidder`)
  VALUES (NEW.`auction_id`, NEW.`bid_amount`, NEW.`username`)
  ON DUPLICATE KEY UPDATE `highest_bid` = GREATEST(`highest_bid`, NEW.`bid_amount`), `last_bidder` = NEW.`username`;
END$$

DELIMITER ;
-- ------------------------------------------------------

-- Table structure for table `review`

CREATE TABLE IF NOT EXISTS `review` (
  `review_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `review_author` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `reviewed_user` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `review` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  PRIMARY KEY (`review_id`),
  FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`review_author`) REFERENCES `users` (`username`),
  FOREIGN KEY (`reviewed_user`) REFERENCES `users` (`username`)
      ON DELETE CASCADE 
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `profile`

CREATE TABLE IF NOT EXISTS `profile` (
    `profile_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    `bank_account` VARCHAR(50) NOT NULL,
    `delivery_address` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`profile_id`),
    FOREIGN KEY (`username`) REFERENCES `users`(`username`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `profile` (`username`, `bank_account`, `delivery_address`)
VALUES
    ('ola', '1234567890123456', '123 Main St, Springfield'),
    ('rachel', '9876543210987654', '456 Elm St, Shelbyville'),
    ('jingyi', '5678901234567890', '789 Oak St, Capital City'),
    ('user4', '3456783452345678', '792 oxford St, London'),
    ('user5', '9876534523456666', '456 Refent St, London');



-- --------------------------------------------------------

-- Final configuration and cleanup

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
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
  `blocked` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`username`, `email`, `password`, `average_rating`, `accountType`, `blocked`) VALUES
('admin', 'admin@admin.com', '$2y$10$5bHV5CRcy3x0PgKIuKoaLuWMpl.wnZtesVzicINtB97pgy5oWFU22', 0, 'admin', 0),
('jingyi', 'jingyi@jingyi.com', '$2y$10$QD07SEZLWOL9g86heIvpm..8PVVUH/D6VqEbTG3v2KD7YieMRcLs2', 5, 'user', 0),
('ola', 'ola@ola.com', '$2y$10$MyZKXNUnNCpBxXZk592MR.1VOn89x/p/8p60bCFGg20OukERdrWXG', 3, 'user', 0),
('rachel', 'rachel@rachel.com', '$2y$10$l3phFc70eX/R6wFeIFQyZeqc4tCmjxoZunCyxIVO/cfE4ltOKKSsS', 5, 'user', 0),
('user4', 'user4@user.com', '$2y$10$LvCa3U34VB9jM6S5tI8tUub8NpsF1CIwS2D7DhyKf5erIFUkZ6f.q', 1, 'user', 1),
('user5', 'user5@user.com', '$2y$10$fL41H6mZzqJjIQIIo9Utn.V3sMSmjw5KqHROvy.f5b0SRomp9x85a', 4, 'user', 0);

-- Passwords are user1234 / admin1234

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

INSERT INTO `auction` (`auction_id`, `item_name`, `item_description`, `category_id`, `username`, `starting_price`, `reserve_price`, `start_date`, `end_date`, `auction_status`, `image_path`, `material_id`, `item_condition`, `color_id`, `size_id`, `views`) VALUES
(20, 'Cozy Wool Sweater', 'A warm and cozy hand-knitted sweater perfect for chilly days.', 5, 'ola', 40, 0, '2024-11-27 11:09:13', '2024-12-10 11:00:00', 'active', './images/6746fdd929aca6.68593608.png', 2, 'new', NULL, 3, 1),
(21, 'Plushie elephant', 'A cute and soft crochet elephant perfect for kids.', 12, 'ola', 30, 0, '2024-11-27 11:11:21', '2024-12-15 20:30:00', 'active', './images/6746fe5940a886.12215654.jpg', 3, 'new', 3, 6, 4),
(22, 'Crochet Blanket Pattern', 'DIY crochet pattern for a beautiful blanket. Instant digital download.', 2, 'ola', 5, 0, '2024-11-27 11:13:05', '2024-12-02 12:00:00', 'active', './images/6746fec15f7fc3.33332364.png', 5, 'new', 11, 6, 0),
(23, 'Cozy Blanket', 'A warm and comfortable hand-knitted blanket. Perfect for cold evenings and snuggling up.', 13, 'ola', 35, 40, '2024-11-27 11:15:16', '2024-11-28 11:15:00', 'active', './images/6746ff44dcd5d2.58085683.png', 5, 'new', 1, 6, 0),
(24, 'Colourful Crochet Shawl', 'A vibrant shawl featuring a beautiful crochet pattern. Lightweight and perfect for any occasion.', 7, 'ola', 50, 0, '2024-11-27 11:17:10', '2024-11-29 13:00:00', 'active', './images/6746ffb6eee9c4.21453873.png', 1, 'new', 11, 6, 0),
(25, 'Soft Wool Socks', 'Handmade wool socks that will keep your feet toasty during winter. Unisex, one size fits all.', 9, 'rachel', 15, 0, '2024-11-27 11:19:34', '2024-12-11 11:00:00', 'active', './images/674700462a2ea9.30274860.png', 2, 'new', 3, 6, 1),
(26, 'Baby Blue Cardigan', 'A charming knitted cardigan in baby blue, perfect for layering in the winter months. Made by my mum!', 5, 'rachel', 45, 50, '2024-11-27 11:22:43', '2024-12-12 11:22:00', 'active', './images/6747010323e0f2.54712505.png', 4, 'new', 7, 3, 0),
(27, 'Baby Clothes Knitting Patterns', 'Helping my mum sell her amazing patterns!!! Includes 20 patterns for jumpers, beanies, cardigans, mittens and more. You can still make some before Christmas!', 1, 'rachel', 7, 0, '2024-11-27 11:25:32', '2024-12-25 18:00:00', 'active', './images/674701aca29ba5.33749318.png', NULL, 'new', NULL, NULL, 2),
(28, 'Baby Girl Dress', 'Made by my mum. Used to fit our 4 years old but she\'s growing fast so I need to sell to make others appreciate it!', 10, 'rachel', 15, 0, '2024-11-27 11:27:50', '2024-12-15 09:00:00', 'active', './images/674702365e8b15.65156781.png', 1, 'used', 11, NULL, 0),
(29, 'Granny Square Throw', 'A stunning granny square throw blanket with mixed colours. Ideal as a gift or home decor.', 13, 'rachel', 25, 30, '2024-11-27 11:29:37', '2024-11-30 11:30:00', 'active', './images/674702a122e378.56338976.png', 3, 'new', 11, NULL, 0),
(30, 'Handmade Crochet Toy Bear', 'Adorable crochet bear made from 100% cotton yarn. Safe for children and makes a perfect cuddly friend.', 12, 'rachel', 10, 30, '2024-11-27 11:31:14', '2025-02-20 21:00:00', 'active', './images/674703020b2682.14611284.png', 3, 'new', 4, NULL, 1),
(31, 'Wool Mittens', 'Cozy wool mittens that are perfect for cold weather. Hand-knitted with love.', 8, 'rachel', 10, 25, '2024-11-27 11:33:21', '2024-11-28 11:00:00', 'active', './images/674703815ea4f6.79384279.png', 2, 'new', 3, 4, 2),
(32, 'Crochet Baby Blanket', 'Handmade baby blanket made from soft yarn, perfect as a baby shower gift.', 13, 'jingyi', 15, 0, '2024-11-27 11:36:31', '2024-12-24 11:00:00', 'active', './images/6747043fc38a64.37972302.png', 5, 'new', 1, NULL, 0),
(33, 'Knitted Wool Hat', 'A stylish knitted hat to keep you warm during winter.', 6, 'jingyi', 5, 0, '2024-11-27 11:38:21', '2024-12-07 14:00:00', 'active', './images/674704ad477aa4.54953914.png', 4, 'used', 2, 4, 3),
(34, 'Rainbow Crochet Blanket', 'Colourful crochet blanket, perfect for adding some brightness to your room.', 13, 'jingyi', 20, 0, '2024-11-27 11:40:13', '2024-12-20 11:40:00', 'active', './images/6747051d2f8710.96673434.png', 5, 'new', 11, NULL, 0),
(35, 'Handmade Cotton Headband', 'A soft and comfortable handmade headband, perfect for daily use.', 6, 'jingyi', 10, 0, '2024-11-27 11:42:10', '2024-12-20 18:00:00', 'active', './images/674705923563d0.24776727.png', 1, 'new', 11, 6, 2),
(36, 'Knit Fingerless Gloves', 'Hand-knitted fingerless gloves, perfect for staying warm while using your phone.', 8, 'jingyi', 12, 0, '2024-11-27 11:43:48', '2025-02-20 23:55:00', 'active', './images/674705f4142fa3.39398712.png', 2, 'new', 10, 2, 4),
(37, 'Vintage Wool Sweater', 'A vintage sweater made from wool. Slightly worn, but in good condition.', 5, 'jingyi', 20, 0, '2024-11-27 11:45:31', '2024-11-27 15:00:00', 'closed', './images/6747065bcf3b81.36176342.png', 2, 'used', 4, 3, 7),
(38, 'Crochet Pillow Set', 'A set of 2 handmade crochet pillows. Adds a cozy touch to your living room.', 14, 'jingyi', 50, 0, '2024-11-27 11:46:57', '2024-11-27 12:00:00', 'closed', './images/674706b1da8d50.62375108.png', NULL, 'new', 7, NULL, 3),
(39, 'Acrylic Yarn Bundle', 'A bundle of colourful acrylic yarns, perfect for multiple DIY projects.', 4, 'jingyi', 10, 0, '2024-11-27 11:48:19', '2024-11-28 09:00:00', 'active', './images/67470703c73225.52194658.png', 4, 'new', 11, NULL, 3);

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

INSERT INTO `bids` (`bid_id`, `auction_id`, `username`, `bid_amount`, `bid_time`) VALUES
(2, 21, 'jingyi', 35, '2024-11-27 11:49:45'),
(3, 37, 'ola', 25, '2024-11-27 11:51:17'),
(4, 39, 'ola', 11, '2024-11-27 11:51:34'),
(5, 37, 'rachel', 30, '2024-11-27 11:52:55'),
(6, 36, 'rachel', 13, '2024-11-27 14:28:04'),
(7, 31, 'jingyi', 11, '2024-11-27 14:31:21');

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

INSERT IGNORE INTO `highest_bids` (`auction_id`, `highest_bid`, `last_bidder`) VALUES
(21, 35.00, 'jingyi'),
(31, 11.00, 'jingyi'),
(36, 13.00, 'rachel'),
(37, 30.00, 'rachel'),
(39, 11.00, 'ola');


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

INSERT INTO `sales` (`sale_id`, `auction_id`, `seller_username`, `buyer_username`, `sale_price`) VALUES
(4, 37, 'jingyi', 'rachel', 30);


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

INSERT INTO `watchlist` (`watch_id`, `username`, `auction_id`) VALUES
(4, 'jingyi', 21),
(3, 'jingyi', 36),
(5, 'ola', 27),
(7, 'rachel', 27),
(6, 'rachel', 38);

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


INSERT INTO `user_views` (`view_id`, `username`, `auction_id`, `view_count`) VALUES
(10, 'ola', 20, 1),
(11, 'rachel', 25, 1),
(12, 'rachel', 21, 1),
(13, 'jingyi', 21, 3),
(14, 'jingyi', 36, 2),
(15, 'ola', 37, 2),
(16, 'ola', 39, 2),
(17, 'ola', 27, 1),
(18, 'rachel', 37, 4),
(19, 'rachel', 30, 1),
(20, 'rachel', 38, 2),
(21, 'rachel', 27, 1),
(22, 'rachel', 36, 2),
(23, 'jingyi', 35, 2),
(24, 'jingyi', 38, 1),
(25, 'jingyi', 37, 1),
(26, 'jingyi', 39, 1),
(27, 'jingyi', 31, 2),
(28, 'user4', 33, 3);

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

INSERT INTO `review` (`review_id`, `auction_id`, `review_author`, `reviewed_user`, `review`, `rating`) VALUES
(1, 37, 'rachel', 'jingyi', 'Very happy with my purchase!', 5);



-- --------------------------------------------------------

-- Table structure for table `profile`

CREATE TABLE `profile` (
    `profile_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    `sort_code` VARCHAR(10), -- Added sort_code column
    `bank_account` VARCHAR(50) NOT NULL,
    `phone_number` VARCHAR(15), -- Added phone_number column
    `delivery_address` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `postcode` VARCHAR(20),
    PRIMARY KEY (`profile_id`),
    FOREIGN KEY (`username`) REFERENCES `users`(`username`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `profile` (`username`, `sort_code`, `bank_account`, `phone_number`, `delivery_address`,`postcode`)
VALUES
    ('ola', '400106', '23400123', '07368598774', 'Gower St, London', 'WC1E 6AE'),
    ('rachel', '400106', '23400145', '07472856294', 'wellington Square, oxford', 'OX1 2JD'),
    ('jingyi', '040003', '78950345', '07274957305', 'Ivor court , London', 'EC4 5AS'),
    ('user4', '400106', '56787343', '07397294730', 'Oxford Rd, Manchester', 'M13 9PL'),
    ('user5', '040003', '34587247', '07930467239', '221B Baker St, London', 'NW1 6XE');



-- --------------------------------------------------------

-- Table structure for table `notifications`

CREATE TABLE IF NOT EXISTS notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  auction_id INT(10) UNSIGNED,
  message TEXT NOT NULL,
  type ENUM('bidding', 'auction', 'watchlist') NOT NULL,  -- Updated ENUM values for categorisation
  is_read BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (username) REFERENCES users(username)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (auction_id) REFERENCES auction(auction_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `notifications` (`notification_id`, `username`, `auction_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(5, 'jingyi', 21, 'You have placed a bid of £35.00 on auction ID: 21', 'bidding', 0, '2024-11-27 11:49:45'),
(6, 'ola', 21, 'A new bid of £35.00 has been placed on your auction (ID: 21)', 'auction', 0, '2024-11-27 11:49:45'),
(7, 'ola', 37, 'You have placed a bid of £25.00 on auction ID: 37', 'bidding', 0, '2024-11-27 11:51:17'),
(8, 'jingyi', 37, 'A new bid of £25.00 has been placed on your auction (ID: 37)', 'auction', 0, '2024-11-27 11:51:17'),
(9, 'ola', 39, 'You have placed a bid of £11.00 on auction ID: 39', 'bidding', 0, '2024-11-27 11:51:34'),
(10, 'jingyi', 39, 'A new bid of £11.00 has been placed on your auction (ID: 39)', 'auction', 0, '2024-11-27 11:51:34'),
(11, 'rachel', 37, 'You have placed a bid of £30.00 on auction ID: 37', 'bidding', 0, '2024-11-27 11:52:55'),
(12, 'ola', 37, 'You have been outbid on auction ID: 37', 'bidding', 0, '2024-11-27 11:52:55'),
(13, 'jingyi', 37, 'A new bid of £30.00 has been placed on your auction (ID: 37)', 'auction', 0, '2024-11-27 11:52:55'),
(14, 'jingyi', 38, 'Your auction for Auction ID 38 ended without any bids being placed.', 'auction', 0, '2024-11-27 14:16:43'),
(15, 'rachel', 36, 'You have placed a bid of £13.00 on auction ID: 36', 'bidding', 0, '2024-11-27 14:28:04'),
(16, 'jingyi', 36, 'A new bid of £13.00 has been placed on an auction you are watching (ID: 36)', 'watchlist', 0, '2024-11-27 14:28:04'),
(17, 'jingyi', 36, 'A new bid of £13.00 has been placed on your auction (ID: 36)', 'auction', 0, '2024-11-27 14:28:04'),
(18, 'jingyi', 31, 'You have placed a bid of £11.00 on auction ID: 31', 'bidding', 0, '2024-11-27 14:31:21'),
(19, 'rachel', 31, 'A new bid of £11.00 has been placed on your auction (ID: 31)', 'auction', 0, '2024-11-27 14:31:21'),
(20, 'rachel', 37, 'Congratulations! You won the auction for Auction ID 37 with a bid of £30.00.', 'bidding', 0, '2024-11-27 15:00:51'),
(21, 'jingyi', 37, 'Congratulations! Your auction for Auction ID 37 ended successfully with the highest bid of £30.00 to User: rachel.', 'auction', 0, '2024-11-27 15:00:51');



-- Final configuration and cleanup

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

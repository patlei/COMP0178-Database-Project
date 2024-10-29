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
--

-- --------------------------------------------------------

--
-- Table structure for table `auctions`
--

CREATE TABLE `auctions` (
  `auction_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `starting_price` int(10) UNSIGNED NOT NULL,
  `reserve_price` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `auction_status` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
***  
  `auction_status` ENUM('active', 'closed') NOT NULL, -- Changed data type from TEXT to ENUM.
  PRIMARY KEY (`auction_id`), -- Added PRIMARY KEY for the `auction_id`.
  FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`), -- Added foreign key constraint to link to `items`.
  FOREIGN KEY (`username`) REFERENCES `users` (`username`) -- Added foreign key constraint to link to `users`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `bid_id` int(10) UNSIGNED DEFAULT NULL,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `bid_amount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bid_time` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
  ***
  `bid_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Changed `varchar(30)` to `TIMESTAMP` to better represent time data.
  PRIMARY KEY (`bid_id`), -- Added PRIMARY KEY for the `bid_id`.
  FOREIGN KEY (`auction_id`) REFERENCES `auctions` (`auction_id`), -- Added foreign key constraint to link to `auctions`.
  FOREIGN KEY (`username`) REFERENCES `users` (`username`) -- Added foreign key constraint to link to `users`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `categoryName` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `categoryDescription` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, -- Increased length from `varchar(30)` to `varchar(100)` to allow more description text.
  PRIMARY KEY (`category_id`) -- Added PRIMARY KEY for `category_id`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `closed auctions`
--

CREATE TABLE `closed auctions` (
  `win_id` int(10) UNSIGNED NOT NULL,
  `auction_id` int(10) UNSIGNED NOT NULL,
  `bid_id` int(10) UNSIGNED NOT NULL,
  `seller_rating` int(10) UNSIGNED NOT NULL,
  `buyer_rating` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`win_id`), -- Added PRIMARY KEY for `win_id`.
  FOREIGN KEY (`auction_id`) REFERENCES `auctions` (`auction_id`), -- Added foreign key constraint to link to `auctions`.
  FOREIGN KEY (`bid_id`) REFERENCES `bids` (`bid_id`) -- Added foreign key constraint to link to `bids`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `item_description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, -- Changed length from `varchar(30)` to `varchar(250)` to allow more detailed descriptions.
  `category_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(30) UNSIGNED NOT NULL
  PRIMARY KEY (`item_id`), -- Added PRIMARY KEY for `item_id`.
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`), -- Added foreign key constraint to link to `categories`.
  FOREIGN KEY (`username`) REFERENCES `users` (`username`) -- Added foreign key constraint to link to `users`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, -- Increased length from `varchar(30)` to `varchar(50)` to accommodate longer emails.
  `password` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `average_rating` int(100) UNSIGNED NOT NULL DEFAULT 0,
  `items_sold` int(100) UNSIGNED NOT NULL DEFAULT 0,
  `items_bought` int(100) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`username`) -- Added PRIMARY KEY for `username`.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

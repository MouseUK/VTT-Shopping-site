-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 10, 2025 at 07:33 PM
-- Server version: 8.0.40-cll-lve
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loginsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `base_price` int NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `rarity` enum('Common','Uncommon','Rare','Very Rare','Legendary') DEFAULT 'Common'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `base_price`, `type`, `rarity`) VALUES
(1, 'Herb Pouch', 'Pouch containing Herbs used in Medicine.', 100, 'Pouch', 'Common'),
(15, 'Knife', 'Common every day knife', 100, 'Weapon', 'Common'),
(20, 'Studded Leather', 'Armor', 4500, 'Light Armor', 'Common'),
(21, 'Chain Shirt', 'Armor', 5000, 'Medium Armor', 'Common'),
(22, 'Scale Mail', 'Armor', 5000, 'Medium Armor', 'Common'),
(23, 'Breastplate', 'Armor', 40000, 'Medium Armor', 'Uncommon'),
(24, 'Half Plate', 'Armor', 75000, 'Medium Armor', 'Rare'),
(25, 'Ring Mail', 'Armor', 3000, 'Heavy Armor', 'Common'),
(26, 'Chain Mail', 'Armor', 7500, 'Heavy Armor', 'Common'),
(27, 'Splint', 'Armor', 20000, 'Heavy Armor', 'Uncommon'),
(28, 'Plate', 'Armor', 15000, 'Heavy Armor', 'Rare'),
(29, 'Shield', 'Shield', 1000, 'Shield', 'Common'),
(30, 'Dagger', 'Simple Dagger', 200, 'Simple Melee Weapon', 'Common'),
(31, 'Hand Axe', 'Simple Hand Axe', 500, 'Simple Melee Weapon', 'Common'),
(32, 'Javelin', 'Simple Javelin', 500, 'Simple Melee Weapon', 'Uncommon'),
(33, 'Light Hammer', 'Hammer', 200, 'Simple Melee Weapon', 'Uncommon'),
(34, 'Mace', 'Simple Mace', 500, 'Simple Melee Weapon', 'Uncommon'),
(35, 'Sickle', 'Sickle', 100, 'Simple Melee Weapon', 'Uncommon'),
(36, 'Spear', 'Spear', 100, 'Simple Melee Weapon', 'Common');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `description`, `image_url`) VALUES
(1, 'Never Winter', 'Neverwinter, also known as the City of Skilled Hands and the Jewel of the North, is a bustling, cultured, and cosmopolitan city-state in northwest Faerun.', 'https://www.worldanvil.com/uploads/images/b3fe13433f5ed3e74d080855d06c7068.jpg'),
(3, 'Waters Deep', 'Waterdeep, also known as the City of Splendors or the Crown of the North, is the most important and influential city in the North and perhaps in all Faerun.', 'https://i.redd.it/6huyds6gmch71.png'),
(4, 'Baldur’s Gate', 'Baldur\'s Gate, the Halfway to Everywhere, the City of Blood, also simply called the Gate, is one of the largest metropolises and city-states on the Sword Coast, within the greater Western Heartlands. ', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0pZN8vSRDJQKCrPLG0omSY2oS7CljUbfPTg&s'),
(5, 'Phandalin', 'Nestled in the northern region of the Sword Coast lies the frontier town of Phandalin, a rough-and-tumble settlement of hardy folk, located just south of Neverwinter.', 'https://static.wikia.nocookie.net/song-of-the-nightingale-dd/images/a/a8/Phandalin.jpg/revision/latest?cb=20191124053048');

-- --------------------------------------------------------

--
-- Table structure for table `player_inventory`
--

CREATE TABLE `player_inventory` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `player_inventory`
--

INSERT INTO `player_inventory` (`id`, `user_id`, `item_id`, `quantity`) VALUES
(3, 2, 21, 1),
(4, 2, 22, 1),
(12, 2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `sale_price` int NOT NULL,
  `sale_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `removed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `location_id` int NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text,
  `price_modifier` float NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`id`, `name`, `location_id`, `image_url`, `description`, `price_modifier`) VALUES
(1, 'Magical Delights', 3, 'https://as1.ftcdn.net/v2/jpg/08/83/22/92/1000_F_883229298_nWa3BrWmjplDekv2gN37mlwtRSITXv3n.jpg', 'An small odd looking plain wooden building on the outside, once inside the name makes more sense as you\'re eyes are assaulted with multiple coloured lights from magic lamps, as your eyes become accustom to the light you notice the shelves, items, there is items everywhere.\r\nThe wonder of how so much can fit inside what looks like such a small shop from the outside is enough to boggle the mind.', 1),
(4, 'Madame Ortho\'s Oddities', 1, 'https://letsrollpress.com/wp-content/uploads/2022/10/LabStore-1.jpg', 'A curiosity shop that sells useful (sometimes cursed) trinkets.', 1),
(5, 'Armored Saints', 3, 'https://www.dndspeak.com/wp-content/uploads/2021/04/Shop-1.jpg', 'The Armored Saints is a two storey stone-walled building, with several stained glass windows and tall elf-wrought wooden racks of weapons and armor. It has a single massive chimney on the far wall.', 1),
(6, 'The Best Defense', 4, 'https://static1.thegamerimages.com/wordpress/wp-content/uploads/2020/03/titleeee-Cropped.jpg', 'The shop is a two-storey timber and brick building, with a smooth stone floor. A large collection of mundane armor hangs from the walls.', 1),
(7, 'Forge Ahead', 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSL1aSkiSzrkJ9M67jKO12-vkDVQ_sdPTxUhg&s', 'Brick built building with metal work used at every opportunity, you can tell the proprietor enjoys metal work a little too much.', 2);

-- --------------------------------------------------------

--
-- Table structure for table `shop_inventory`
--

CREATE TABLE `shop_inventory` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `item_id` int NOT NULL,
  `stock` int DEFAULT '0',
  `price_modifier` float NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `shop_inventory`
--

INSERT INTO `shop_inventory` (`id`, `shop_id`, `item_id`, `stock`, `price_modifier`) VALUES
(3, 1, 1, 10, 1),
(6, 4, 15, 18, 2),
(7, 7, 1, 5, 1),
(9, 7, 15, 333, 1),
(10, 5, 20, 1, 1.5),
(11, 5, 21, 7, 1.2),
(12, 5, 22, 6, 1),
(13, 5, 23, 5, 1),
(14, 5, 24, 7, 1),
(15, 5, 25, 6, 1),
(16, 5, 25, 7, 1.2),
(17, 5, 26, 12, 0.8),
(18, 5, 27, 6, 1),
(19, 5, 28, 3, 1.3),
(20, 5, 29, 16, 1);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantity` int NOT NULL,
  `total_price` int NOT NULL,
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `added` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','pc') NOT NULL,
  `location_id` int DEFAULT NULL,
  `currency` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `location_id`, `currency`) VALUES
(2, 'pc', '$2y$10$l0eqmB6.bPLftCdhPd/KDOWLtu3/cMTNKdaprDCjv/ttJbjkGh1a2', 'pc', 1, 2190142),
(3, 'admin', '$2y$10$5EOe6K2anJ8DcWgc3Ha5TuAmKx/OIQUbp5iyOkmYXccPh5NQ.Wg4e', 'admin', NULL, 1000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `player_inventory`
--
ALTER TABLE `player_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `shop_inventory`
--
ALTER TABLE `shop_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `location_id` (`location_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `player_inventory`
--
ALTER TABLE `player_inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `shop_inventory`
--
ALTER TABLE `shop_inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `player_inventory`
--
ALTER TABLE `player_inventory`
  ADD CONSTRAINT `player_inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_inventory_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `shops_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_inventory`
--
ALTER TABLE `shop_inventory`
  ADD CONSTRAINT `shop_inventory_ibfk_1` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_inventory_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

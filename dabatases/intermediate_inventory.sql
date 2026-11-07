-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 07, 2026 at 06:41 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `intermediate_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `parent_id`, `name`, `description`) VALUES
(1, NULL, 'Electronics', 'Main electronics category'),
(2, 1, 'Computer Peripherals', 'Mice, keyboards, and monitors'),
(3, NULL, 'Office Furniture', 'Desks, chairs, and storage');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int NOT NULL,
  `customer_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `email`, `phone`, `shipping_address`, `created_at`) VALUES
(1, 'Apex Software Solutions', 'procurement@apexsoft.com', '+880-1911-444555', 'Banani, Road 11, Dhaka', '2026-11-07 18:01:50'),
(2, 'Delta Retail Store', 'info@deltaretail.com', '+880-1611-777888', 'Zindabazar, Sylhet', '2026-11-07 18:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int DEFAULT NULL,
  `preferred_supplier_id` int DEFAULT NULL,
  `unit_of_measure` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `cost_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `selling_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `reorder_point` int NOT NULL DEFAULT '10',
  `reorder_quantity` int NOT NULL DEFAULT '50',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `sku`, `barcode`, `product_name`, `category_id`, `preferred_supplier_id`, `unit_of_measure`, `cost_price`, `selling_price`, `tax_rate`, `reorder_point`, `reorder_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PER-MOU-001', '8901112223331', 'Logitech Wireless Mouse M185', 2, 1, 'pcs', 8.50, 14.00, 5.00, 20, 100, 1, '2026-11-07 18:01:51', '2026-11-07 18:01:51'),
(2, 'PER-KEY-002', '8901112223332', 'Mechanical RGB Keyboard', 2, 1, 'pcs', 32.00, 55.00, 5.00, 15, 50, 1, '2026-11-07 18:01:51', '2026-11-07 18:01:51'),
(3, 'FUR-CHR-001', '8901112223333', 'Ergonomic Mesh Office Chair', 3, 2, 'pcs', 75.00, 130.00, 7.50, 5, 20, 1, '2026-11-07 18:01:51', '2026-11-07 18:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int NOT NULL,
  `po_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `status` enum('DRAFT','ORDERED','PARTIAL','RECEIVED','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `order_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`po_id`, `po_number`, `supplier_id`, `warehouse_id`, `status`, `order_date`, `expected_date`, `total_amount`, `created_by`, `created_at`) VALUES
(1, 'PO-2026-0001', 1, 1, 'RECEIVED', '2026-11-08', '2026-11-08', 2025.00, 2, '2026-11-07 18:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `po_item_id` int NOT NULL,
  `po_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity_ordered` int NOT NULL,
  `quantity_received` int NOT NULL DEFAULT '0',
  `unit_cost` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`po_item_id`, `po_id`, `product_id`, `quantity_ordered`, `quantity_received`, `unit_cost`) VALUES
(1, 1, 1, 50, 50, 8.50),
(2, 1, 2, 50, 50, 32.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `so_id` int NOT NULL,
  `so_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `status` enum('PENDING','CONFIRMED','SHIPPED','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `order_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `so_item_id` int NOT NULL,
  `so_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` bigint NOT NULL,
  `product_id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `movement_type` enum('PURCHASE_RECEIPT','SALE_SHIPMENT','TRANSFER_IN','TRANSFER_OUT','ADJUSTMENT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_change` int NOT NULL,
  `reference_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_by` int DEFAULT NULL,
  `movement_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`movement_id`, `product_id`, `warehouse_id`, `movement_type`, `quantity_change`, `reference_type`, `reference_id`, `notes`, `performed_by`, `movement_date`) VALUES
(1, 1, 1, 'PURCHASE_RECEIPT', 50, 'PO', 1, 'Initial PO receipt into Central Warehouse', 3, '2026-11-07 18:01:51'),
(2, 2, 1, 'PURCHASE_RECEIPT', 50, 'PO', 1, 'Initial PO receipt into Central Warehouse', 3, '2026-11-07 18:01:51'),
(3, 3, 1, 'ADJUSTMENT', 4, 'MANUAL', NULL, 'Opening stock count (Below reorder point for testing)', 2, '2026-11-07 18:01:51'),
(4, 1, 1, 'SALE_SHIPMENT', -10, 'SO', 1, 'Shipped 10 mice to Apex Software', 3, '2026-11-07 18:01:51');

--
-- Triggers `stock_movements`
--
DELIMITER $$
CREATE TRIGGER `trg_sync_warehouse_stock` AFTER INSERT ON `stock_movements` FOR EACH ROW BEGIN
    INSERT INTO warehouse_stock (warehouse_id, product_id, quantity_on_hand)
    VALUES (NEW.warehouse_id, NEW.product_id, NEW.quantity_change)
    ON DUPLICATE KEY UPDATE 
        quantity_on_hand = quantity_on_hand + NEW.quantity_change;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int NOT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `company_name`, `contact_person`, `email`, `phone`, `tax_id`, `address`, `is_active`, `created_at`) VALUES
(1, 'Global Tech Supply Ltd.', 'Rahim Uddin', 'orders@globaltech.com', '+880-1711-000111', 'TAX-99201', 'Gulshan-2, Dhaka', 1, '2026-11-07 18:01:50'),
(2, 'WorkSpace Furnishings', 'Nadia Islam', 'sales@workspace.com', '+880-1811-222333', 'TAX-44510', 'Agrabad, Chattogram', 1, '2026-11-07 18:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('ADMIN','MANAGER','STAFF') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'STAFF',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin_user', 'admin@inventory.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1, '2026-11-07 18:01:50'),
(2, 'manager_dhaka', 'manager@inventory.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MANAGER', 1, '2026-11-07 18:01:50'),
(3, 'staff_clerk', 'staff@inventory.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', 1, '2026-11-07 18:01:50'),
(4, 'admin', 'admin@gmai.com', '$2y$10$5QDiRe10CbaYvSWKkK8TPOkZYcVkha9lKsoseu39jxISiIqdctC7W', 'STAFF', 1, '2026-11-07 18:35:39'),
(5, 'admin1', 'admin1@gmail.com', '$2y$10$sr1D4kzNWz.JFOgNOSKeD.4jKbGErPVtT2n6/SIsOMNUX9hHdRFJq', 'ADMIN', 1, '2026-11-07 18:36:58'),
(6, 'admin2', 'admin2@gmail.com', '$2y$10$huadY7RUuXJGNwEI1AC4neG8HPhs4CJx54ehrlUovgbzgndY1HYB6', 'MANAGER', 1, '2026-11-07 18:37:25');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_inventory_valuation`
-- (See below for the actual view)
--
CREATE TABLE `vw_inventory_valuation` (
`product_id` int
,`sku` varchar(50)
,`product_name` varchar(200)
,`category_name` varchar(100)
,`cost_price` decimal(12,2)
,`selling_price` decimal(12,2)
,`unit_margin` decimal(13,2)
,`total_on_hand` decimal(32,0)
,`total_reserved` decimal(32,0)
,`available_to_sell` decimal(33,0)
,`total_inventory_cost` decimal(44,2)
,`reorder_point` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_reorder_alerts`
-- (See below for the actual view)
--
CREATE TABLE `vw_reorder_alerts` (
`product_id` int
,`sku` varchar(50)
,`product_name` varchar(200)
,`category_name` varchar(100)
,`total_on_hand` decimal(32,0)
,`available_to_sell` decimal(33,0)
,`reorder_point` int
);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `warehouse_id` int NOT NULL,
  `warehouse_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_address` text COLLATE utf8mb4_unicode_ci,
  `manager_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`warehouse_id`, `warehouse_code`, `warehouse_name`, `location_address`, `manager_id`, `is_active`) VALUES
(1, 'WH-DAC-01', 'Central Dhaka Warehouse', 'Tejgaon Industrial Area, Dhaka', 2, 1),
(2, 'WH-CGP-02', 'Chattogram Port Hub', 'CEPZ, Chattogram', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_stock`
--

CREATE TABLE `warehouse_stock` (
  `warehouse_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity_on_hand` int NOT NULL DEFAULT '0',
  `quantity_reserved` int NOT NULL DEFAULT '0',
  `aisle_bin_location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouse_stock`
--

INSERT INTO `warehouse_stock` (`warehouse_id`, `product_id`, `quantity_on_hand`, `quantity_reserved`, `aisle_bin_location`, `updated_at`) VALUES
(1, 1, 40, 0, 'Aisle 1 - Bin A1', '2026-11-07 18:01:51'),
(1, 2, 50, 0, 'Aisle 1 - Bin A2', '2026-11-07 18:01:51'),
(1, 3, 4, 0, 'Aisle 4 - Bulk Zone', '2026-11-07 18:01:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `preferred_supplier_id` (`preferred_supplier_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`po_item_id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`so_id`),
  ADD UNIQUE KEY `so_number` (`so_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`so_item_id`),
  ADD KEY `so_id` (`so_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`warehouse_id`),
  ADD UNIQUE KEY `warehouse_code` (`warehouse_code`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD PRIMARY KEY (`warehouse_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `po_item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `so_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  MODIFY `so_item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `warehouse_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- --------------------------------------------------------

--
-- Structure for view `vw_inventory_valuation`
--
DROP TABLE IF EXISTS `vw_inventory_valuation`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_inventory_valuation`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`sku` AS `sku`, `p`.`product_name` AS `product_name`, `c`.`name` AS `category_name`, `p`.`cost_price` AS `cost_price`, `p`.`selling_price` AS `selling_price`, (`p`.`selling_price` - `p`.`cost_price`) AS `unit_margin`, coalesce(sum(`ws`.`quantity_on_hand`),0) AS `total_on_hand`, coalesce(sum(`ws`.`quantity_reserved`),0) AS `total_reserved`, coalesce(sum((`ws`.`quantity_on_hand` - `ws`.`quantity_reserved`)),0) AS `available_to_sell`, (coalesce(sum(`ws`.`quantity_on_hand`),0) * `p`.`cost_price`) AS `total_inventory_cost`, `p`.`reorder_point` AS `reorder_point` FROM ((`products` `p` left join `categories` `c` on((`p`.`category_id` = `c`.`category_id`))) left join `warehouse_stock` `ws` on((`p`.`product_id` = `ws`.`product_id`))) GROUP BY `p`.`product_id` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_reorder_alerts`
--
DROP TABLE IF EXISTS `vw_reorder_alerts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_reorder_alerts`  AS SELECT `vw_inventory_valuation`.`product_id` AS `product_id`, `vw_inventory_valuation`.`sku` AS `sku`, `vw_inventory_valuation`.`product_name` AS `product_name`, `vw_inventory_valuation`.`category_name` AS `category_name`, `vw_inventory_valuation`.`total_on_hand` AS `total_on_hand`, `vw_inventory_valuation`.`available_to_sell` AS `available_to_sell`, `vw_inventory_valuation`.`reorder_point` AS `reorder_point` FROM `vw_inventory_valuation` WHERE (`vw_inventory_valuation`.`available_to_sell` <= `vw_inventory_valuation`.`reorder_point`) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`preferred_supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`),
  ADD CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD CONSTRAINT `sales_order_items_ibfk_1` FOREIGN KEY (`so_id`) REFERENCES `sales_orders` (`so_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`),
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD CONSTRAINT `warehouse_stock_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_stock_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Purchasing & Maintenance Module Schema
-- Author: Antigravity
-- Date: 2026-01-01

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- 1. Master Data: Suppliers
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Master Data: Item Categories
CREATE TABLE IF NOT EXISTS `item_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('Sparepart','Oil','Tyre','Asset','Office','Consumable') NOT NULL DEFAULT 'Sparepart',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Master Data: Items (Spareparts & Consumables)
CREATE TABLE IF NOT EXISTS `items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'Pcs',
  `min_stock` int(11) DEFAULT 0,
  `current_stock` int(11) DEFAULT 0,
  `last_purchase_price` double DEFAULT 0,
  `location` varchar(100) DEFAULT NULL COMMENT 'Rack Number/Shelf',
  `compatible_models` text DEFAULT NULL COMMENT 'JSON: ["Hiace", "Bus"]',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `item_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Purchasing: Requests (PR)
CREATE TABLE IF NOT EXISTS `purchase_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `requester_id` bigint(20) DEFAULT NULL COMMENT 'User ID',
  `request_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected','Ordered','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Purchasing: PR Items
CREATE TABLE IF NOT EXISTS `pr_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pr_id` bigint(20) NOT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL COMMENT 'If item not in DB yet',
  `qty` int(11) NOT NULL,
  `urgency` enum('Normal','Urgent','Critical') DEFAULT 'Normal',
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Purchasing: Purchase Orders (PO)
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL UNIQUE,
  `supplier_id` bigint(20) DEFAULT NULL,
  `pr_id` bigint(20) DEFAULT NULL,
  `status` enum('Draft','Sent','Partial','Closed','Cancelled') DEFAULT 'Draft',
  `total_amount` double DEFAULT 0,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Purchasing: PO Items
CREATE TABLE IF NOT EXISTS `po_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `po_id` bigint(20) NOT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `price_per_unit` double NOT NULL,
  `total_price` double NOT NULL,
  `received_qty` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`po_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Inventory: Transactions (Stock Card)
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) NOT NULL,
  `type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `qty` int(11) NOT NULL,
  `current_stock_snapshot` int(11) NOT NULL,
  `ref_type` enum('PO','SERVICE','ADJUSTMENT','RETURN') DEFAULT NULL,
  `ref_id` varchar(50) DEFAULT NULL COMMENT 'PO ID or Service Log ID',
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Maintenance: Service Logs
CREATE TABLE IF NOT EXISTS `maintenance_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fleet_id` bigint(20) NOT NULL,
  `service_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `current_km` int(11) NOT NULL,
  `service_type` enum('Routine','Repair','Accident','Tire','Overhaul') NOT NULL,
  `mechanic_name` varchar(100) DEFAULT NULL,
  `workshop_name` varchar(100) DEFAULT 'Internal' COMMENT 'Internal or External Vendor',
  `status` enum('In Progress','Completed','Pending Parts') DEFAULT 'In Progress',
  `notes` text DEFAULT NULL,
  `total_cost` double DEFAULT 0,
  `next_service_km` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`fleet_id`) REFERENCES `fleet`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Maintenance: Service Items (Parts Used)
CREATE TABLE IF NOT EXISTS `maintenance_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_id` bigint(20) NOT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `cost_per_unit` double NOT NULL COMMENT 'Historical cost at time of usage',
  `total_cost` double NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`log_id`) REFERENCES `maintenance_logs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Assets: Registry (Non-consumables)
CREATE TABLE IF NOT EXISTS `assets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(50) UNIQUE,
  `name` varchar(255) NOT NULL,
  `category` enum('Vehicle_Equipment','Office','Workshop','IT') NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` double DEFAULT 0,
  `condition` enum('Good','Damaged','Lost','Repair') DEFAULT 'Good',
  `assigned_to_type` enum('Fleet','User','Location') DEFAULT NULL,
  `assigned_to_id` varchar(50) DEFAULT NULL COMMENT 'Fleet ID or User ID',
  `last_audit_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Assets: Audits (Stock Opname)
CREATE TABLE IF NOT EXISTS `asset_audits` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `audit_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `auditor_name` varchar(100) DEFAULT NULL,
  `target_type` enum('Fleet','Office','Warehouse') NOT NULL,
  `target_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Compliant','Variance') DEFAULT 'Compliant',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

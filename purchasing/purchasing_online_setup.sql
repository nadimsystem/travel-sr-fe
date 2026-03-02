-- ================================================================
-- PURCHASING MODULE - ONLINE DATABASE SETUP
-- Sutan Raya Fleet Management System
-- ================================================================
-- This file sets up all required tables for the Purchasing Module
-- Upload and run this SQL file on your online database
-- ================================================================

-- 1. PURCHASING ITEMS TABLE
-- Stores all spare parts and inventory items
CREATE TABLE IF NOT EXISTS `purchasing_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100),
    `stock` INT DEFAULT 0,
    `min_stock` INT DEFAULT 5,
    `unit` VARCHAR(50),
    `last_price` DECIMAL(15,2),
    `compatibility` VARCHAR(255),
    `location` VARCHAR(100),
    `condition_status` VARCHAR(50) DEFAULT 'Baru',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (`category`),
    INDEX idx_code (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PURCHASING ASSETS TABLE
-- Stores company assets (vehicles, equipment, property)
CREATE TABLE IF NOT EXISTS `purchasing_assets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100),
    `value` DECIMAL(15,2),
    `location` VARCHAR(100),
    `pic` VARCHAR(100),
    `status` VARCHAR(50) DEFAULT 'Active',
    `purchase_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (`category`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PURCHASING REQUESTS TABLE
-- Main table for purchase requests
CREATE TABLE IF NOT EXISTS `purchasing_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `requester_id` INT,
    `notes` TEXT,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PURCHASING REQUEST ITEMS TABLE
-- Detail items for each request
CREATE TABLE IF NOT EXISTS `purchasing_request_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `item_id` INT NULL,
    `item_name` VARCHAR(255) NOT NULL,
    `qty` INT NOT NULL,
    `unit` VARCHAR(50),
    `urgency` VARCHAR(50),
    `bus_id` VARCHAR(50),
    `notes` TEXT,
    FOREIGN KEY (`request_id`) REFERENCES `purchasing_requests`(`id`) ON DELETE CASCADE,
    INDEX idx_request (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PURCHASING DEPLOYMENTS TABLE
-- Track when items are deployed/issued to vehicles
CREATE TABLE IF NOT EXISTS `purchasing_deployments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT NOT NULL,
    `qty_deployed` INT NOT NULL,
    `deployed_to_fleet_id` INT NULL,
    `deployed_to_name` VARCHAR(255),
    `deployed_by` VARCHAR(100),
    `reason` TEXT,
    `deployment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `photo_proof` VARCHAR(255),
    `notes` TEXT,
    `status` VARCHAR(50) DEFAULT 'Deployed',
    FOREIGN KEY (`item_id`) REFERENCES `purchasing_items`(`id`) ON DELETE CASCADE,
    INDEX idx_item (`item_id`),
    INDEX idx_deployment_date (`deployment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. PURCHASING RECEIVING TABLE
-- Track item receipts from suppliers with validation
CREATE TABLE IF NOT EXISTS `purchasing_receiving` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `po_id` INT NULL,
    `item_id` INT NOT NULL,
    `qty_received` INT NOT NULL,
    `received_by` VARCHAR(100),
    `received_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `receipt_photo` VARCHAR(255),
    `supplier_invoice` VARCHAR(255),
    `notes` TEXT,
    `validated` BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`item_id`) REFERENCES `purchasing_items`(`id`) ON DELETE CASCADE,
    INDEX idx_item (`item_id`),
    INDEX idx_po (`po_id`),
    INDEX idx_received_date (`received_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SUPPLIERS TABLE
-- Store supplier information
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100),
    `contact_person` VARCHAR(100),
    `phone` VARCHAR(20),
    `email` VARCHAR(100),
    `address` TEXT,
    `city` VARCHAR(100),
    `rating` DECIMAL(3,2) DEFAULT 0.00,
    `payment_terms` VARCHAR(100),
    `notes` TEXT,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (`name`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. PURCHASE ORDERS TABLE
-- Track purchase orders to suppliers
CREATE TABLE IF NOT EXISTS `purchasing_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `po_number` VARCHAR(50) UNIQUE NOT NULL,
    `supplier_id` INT,
    `total_amount` DECIMAL(15,2) DEFAULT 0,
    `status` VARCHAR(50) DEFAULT 'Draft',
    `order_date` DATE,
    `expected_delivery` DATE,
    `notes` TEXT,
    `created_by` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
    INDEX idx_po_number (`po_number`),
    INDEX idx_status (`status`),
    INDEX idx_supplier (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. PURCHASE ORDER ITEMS TABLE
-- Detail items for each PO
CREATE TABLE IF NOT EXISTS `purchasing_order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `po_id` INT NOT NULL,
    `item_id` INT NULL,
    `item_name` VARCHAR(255) NOT NULL,
    `qty` INT NOT NULL,
    `unit` VARCHAR(50),
    `unit_price` DECIMAL(15,2),
    `total_price` DECIMAL(15,2),
    `notes` TEXT,
    FOREIGN KEY (`po_id`) REFERENCES `purchasing_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `purchasing_items`(`id`) ON DELETE SET NULL,
    INDEX idx_po (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- SEED DATA - SAMPLE ITEMS
-- ================================================================

INSERT IGNORE INTO `purchasing_items` (`code`, `name`, `category`, `stock`, `unit`, `last_price`, `compatibility`, `location`) VALUES
-- ENGINE PARTS
('ENG-001', 'Filter Oli (Oil Filter) DX', 'Sparepart', 15, 'Pcs', 120000, 'Hino R260 / RK8', 'Rak A-01'),
('ENG-002', 'Filter Solar Bawah (Fuel Filter)', 'Sparepart', 20, 'Pcs', 85000, 'Hino R260', 'Rak A-02'),
('ENG-003', 'Filter Udara (Air Cleaner)', 'Sparepart', 8, 'Pcs', 450000, 'Hino R260', 'Rak A-03'),
('ENG-004', 'V-Belt AC (Tali Kipas)', 'Sparepart', 10, 'Pcs', 150000, 'Denso System', 'Rak A-04'),
('ENG-005', 'Packing Set Overhaul (Full Set)', 'Sparepart', 2, 'Set', 3500000, 'Hino J08E', 'Gudang Mesin'),
('ENG-006', 'Turbocharger Assembly', 'Sparepart', 1, 'Unit', 15000000, 'Hino R260', 'Gudang Mesin'),
('ENG-007', 'Alternator 24V 60A', 'Elektrikal', 2, 'Unit', 2800000, 'Universal Bus', 'Rak E-10'),
('ENG-008', 'Motor Starter (Dinamo Starter)', 'Elektrikal', 2, 'Unit', 3200000, 'Hino RK8', 'Rak E-11'),

-- CHASSIS & SUSPENSION
('SUS-001', 'Air Suspension Bellow (Balon Udara)', 'Kaki-Kaki & Sasis', 6, 'Pcs', 2500000, 'Hino R260 / Merc', 'Gudang K-01'),
('SUS-002', 'Shock Absorber Depan', 'Kaki-Kaki & Sasis', 4, 'Pcs', 1200000, 'Hino RK8', 'Rak K-02'),
('BRK-001', 'Kampas Rem Depan (Brake Lining)', 'Kaki-Kaki & Sasis', 12, 'Set', 850000, 'Hino R260', 'Rak K-03'),
('BRK-002', 'Tromol Rem (Brake Drum)', 'Kaki-Kaki & Sasis', 2, 'Pcs', 1800000, 'Hino R260', 'Gudang K-02'),
('WHL-001', 'Baut Roda (Wheel Stud) Belakang', 'Kaki-Kaki & Sasis', 50, 'Pcs', 45000, 'Universal Hino', 'Rak K-05'),
('WHL-002', 'Bearing Roda Luar', 'Kaki-Kaki & Sasis', 8, 'Pcs', 350000, 'Hino RK8', 'Rak K-06'),

-- BODY & ELECTRICAL
('LGT-001', 'Headlamp Jetbus 3+ (Kanan)', 'Body & Glass', 1, 'Pcs', 3500000, 'Adiputro Jetbus 3', 'Gudang B-01'),
('LGT-002', 'Stop Lamp LED Running (Belakang)', 'Body & Glass', 2, 'Set', 1200000, 'Adiputro Jetbus 3', 'Rak B-02'),
('EL-001', 'Relay 24V 5 Pin', 'Elektrikal', 30, 'Pcs', 35000, 'Universal', 'Rak E-01'),
('EL-002', 'Sikring Tancep (Fuse) 10A-30A', 'Elektrikal', 100, 'Pcs', 2000, 'Universal', 'Rak E-02'),
('EL-003', 'Aki N200 (Battery 200Ah)', 'Elektrikal', 4, 'Pcs', 3800000, 'Bus Besar', 'Gudang Aki'),

-- INTERIOR & AC
('INT-005', 'Jok Rimba Kencana (Captain Seat)', 'Interior & AC', 0, 'Pcs', 4500000, 'Hiace Luxury', 'Indent'),
('INT-006', 'Karpet Lantai Vinyl Kayu (Per Meter)', 'Interior & AC', 15, 'Meter', 120000, 'Universal', 'Gudang I-01'),
('AC-005', 'Kompresor AC Denso Bus Big', 'Interior & AC', 1, 'Unit', 8500000, 'Big Bus', 'Gudang AC'),
('AC-006', 'Freon R134a (Tabung 13kg)', 'Oli & Kimia', 5, 'Tabung', 1800000, 'Universal AC', 'Gudang Kimia'),

-- OIL & CHEMICALS
('OIL-010', 'Oli Mesin Meditran SX 15W-40', 'Oli & Kimia', 40, 'Galon', 320000, 'Diesel Engine', 'Gudang Oli'),
('OIL-011', 'Oli Transmisi Rored 90', 'Oli & Kimia', 20, 'Galon', 280000, 'Manual Trans', 'Gudang Oli'),
('CHE-001', 'AdBlue (Cairan Exhaust Diesel) 10L', 'Oli & Kimia', 30, 'Jerigen', 150000, 'Euro 4', 'Gudang O-01'),

-- TIRES
('TYR-001', 'Ban Bridgestone 11R22.5 (Bus)', 'Ban', 8, 'Pcs', 4200000, 'Big Bus', 'Gudang Ban'),
('TYR-002', 'Ban Dunlop 195/R15 (Hiace)', 'Ban', 12, 'Pcs', 1100000, 'Toyota Hiace', 'Gudang Ban'),

-- TOOLS & SAFETY
('TOL-001', 'Dongkrak Botol 20 Ton', 'Tools', 4, 'Pcs', 850000, 'Universal', 'Gudang Tools'),
('SAF-001', 'APAR 3kg Dry Chemical Powder', 'Safety & Emergency', 12, 'Tabung', 350000, 'Universal', 'Rak S-01');

-- ================================================================
-- SEED DATA - SAMPLE ASSETS
-- ================================================================

INSERT IGNORE INTO `purchasing_assets` (`code`, `name`, `category`, `value`, `location`, `pic`, `status`) VALUES
('AST-VH-001', 'Toyota Hiace Luxury B 7789 SDA', 'Kendaraan', 650000000, 'Pool Utama', 'Budi (Driver)', 'Active'),
('AST-VH-002', 'Hino R260 Jetbus 3+ HDD', 'Kendaraan', 1850000000, 'Pool Utama', 'Operational Mgr', 'Maintenance'),
('AST-EQ-010', 'Genset Silent 50kVA', 'Mesin', 120000000, 'Gudang Teknik', 'Kepala Mekanik', 'Active'),
('AST-IT-005', 'Laptop Admin Purchasing', 'Elektronik', 15000000, 'Kantor Lt. 2', 'Admin Purchasing', 'Active'),
('AST-PR-001', 'Gedung Workshop & Mess', 'Properti', 2500000000, 'Jl. Raya Bogor', 'Direktur', 'Active'),
('AST-EQ-015', 'Hydraulic Bus Lift', 'Mesin', 85000000, 'Workshop', 'Kepala Mekanik', 'Broken');

-- ================================================================
-- SEED DATA - SAMPLE SUPPLIERS
-- ================================================================

INSERT IGNORE INTO `suppliers` (`code`, `name`, `category`, `contact_person`, `phone`, `email`, `city`, `rating`) VALUES
('SUP-001', 'Toko Maju Jaya', 'Sparepart', 'Pak Budi', '081234567890', 'budimaju@example.com', 'Jakarta', 4.5),
('SUP-002', 'CV Sentosa Parts', 'Sparepart', 'Bu Ani', '081298765432', 'sentosa.parts@example.com', 'Bandung', 4.8),
('SUP-003', 'PT Oli Nusantara', 'Oli & Kimia', 'Pak Hendra', '081212341234', 'hendra.oli@example.com', 'Surabaya', 4.3),
('SUP-004', 'Bengkel Ban Jaya', 'Ban', 'Pak Agus', '081298761234', 'banjaya@example.com', 'Jakarta', 4.6),
('SUP-005', 'Toko Elektrik Motor', 'Elektrikal', 'Bu Siti', '081234123456', 'elektrik.motor@example.com', 'Bogor', 4.4),
('SUP-006', 'CV Karoseri Indah', 'Body & Glass', 'Pak Rudi', '081245678901', 'karoseri.indah@example.com', 'Bekasi', 4.7),
('SUP-007', 'AC Solution Indonesia', 'Interior & AC', 'Pak Dedi', '081267890123', 'acsolution@example.com', 'Tangerang', 4.5),
('SUP-008', 'Hino Parts Official', 'Sparepart', 'Customer Service', '02112345678', 'parts@hino.co.id', 'Jakarta', 5.0);

-- ================================================================
-- SUCCESS MESSAGE
-- ================================================================
-- Database setup completed successfully!
-- All tables have been created and seeded with sample data.
-- You can now use the Purchasing Module.
-- ================================================================

-- Payment Management Schema
-- Tabel untuk tracking multiple payments per booking

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `booking_id` bigint(20) NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_method` varchar(50) NOT NULL COMMENT 'Cash, Transfer, DP',
  `amount` double NOT NULL,
  `payment_location` varchar(100) DEFAULT NULL,
  `payment_receiver` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded proof image',
  `notes` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT 'system',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add new columns to bookings table for better payment tracking
ALTER TABLE `bookings` 
ADD COLUMN IF NOT EXISTS `payment_remaining` double DEFAULT 0 COMMENT 'Sisa tagihan yang belum dibayar',
ADD COLUMN IF NOT EXISTS `payment_type` varchar(50) DEFAULT 'single' COMMENT 'single, split, installment',
ADD COLUMN IF NOT EXISTS `last_payment_date` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `is_fully_paid` tinyint(1) DEFAULT 0 COMMENT '1 = Lunas, 0 = Belum lunas';

-- Create view for easy outstanding bookings query
CREATE OR REPLACE VIEW `v_outstanding_bookings` AS
SELECT 
  b.id,
  b.passengerName,
  b.passengerPhone,
  b.date,
  b.time,
  b.routeId,
  b.serviceType,
  b.totalPrice,
  b.seatCount,
  (b.totalPrice * b.seatCount) as total_bill,
  b.downPaymentAmount,
  COALESCE(b.payment_remaining, (b.totalPrice * b.seatCount) - b.downPaymentAmount) as remaining_amount,
  b.paymentMethod,
  b.paymentStatus,
  b.validationStatus,
  b.last_payment_date,
  b.is_fully_paid,
  DATEDIFF(CURDATE(), b.date) as days_overdue
FROM bookings b
WHERE b.is_fully_paid = 0 
  AND b.validationStatus != 'Valid'
  AND (b.totalPrice * b.seatCount) > COALESCE(b.downPaymentAmount, 0)
ORDER BY b.date ASC;

-- Create view for payment summary per booking
CREATE OR REPLACE VIEW `v_payment_summary` AS
SELECT 
  b.id as booking_id,
  b.passengerName,
  b.totalPrice * b.seatCount as total_bill,
  COALESCE(SUM(pt.amount), 0) as total_paid,
  (b.totalPrice * b.seatCount) - COALESCE(SUM(pt.amount), 0) as remaining,
  COUNT(pt.id) as payment_count,
  MAX(pt.payment_date) as last_payment_date,
  GROUP_CONCAT(CONCAT(pt.payment_method, ':', pt.amount) ORDER BY pt.payment_date SEPARATOR '; ') as payment_details
FROM bookings b
LEFT JOIN payment_transactions pt ON b.id = pt.booking_id
GROUP BY b.id, b.passengerName, b.totalPrice, b.seatCount;

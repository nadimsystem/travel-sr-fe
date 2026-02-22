-- Add deleted_at column for Soft Delete functionality
ALTER TABLE doorprize_coupons ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;

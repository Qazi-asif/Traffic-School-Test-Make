-- Add insurance_discount_only column to users table
-- This migration adds support for insurance discount only enrollments

ALTER TABLE `users` 
ADD COLUMN `insurance_discount_only` TINYINT(1) NOT NULL DEFAULT 0 
AFTER `license_class`;

-- Verify the column was added
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME = 'insurance_discount_only';

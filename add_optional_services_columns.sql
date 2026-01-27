-- Add Optional Services Columns to user_course_enrollments Table
-- Run this SQL script in your database to add the required columns

USE `nelly-elearning`;

-- Add optional_services column (JSON type to store selected services)
ALTER TABLE `user_course_enrollments` 
ADD COLUMN `optional_services` JSON NULL AFTER `reminder_count`;

-- Add optional_services_total column (DECIMAL to store total cost)
ALTER TABLE `user_course_enrollments` 
ADD COLUMN `optional_services_total` DECIMAL(8,2) DEFAULT 0.00 AFTER `optional_services`;

-- Verify the columns were added
DESCRIBE `user_course_enrollments`;

-- Test data insertion (optional - for testing)
-- UPDATE user_course_enrollments 
-- SET optional_services = '[{"id":"certverify","name":"CertVerify Service","price":10.00}]',
--     optional_services_total = 10.00
-- WHERE id = 1;

-- Check if the update worked
SELECT id, amount_paid, optional_services, optional_services_total 
FROM user_course_enrollments 
WHERE optional_services IS NOT NULL 
LIMIT 5;
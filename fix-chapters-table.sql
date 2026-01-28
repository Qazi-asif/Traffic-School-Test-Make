-- Fix chapters table by adding missing columns
-- Run this SQL directly in your database (phpMyAdmin, MySQL Workbench, etc.)

-- Add duration column if it doesn't exist
ALTER TABLE `chapters` 
ADD COLUMN IF NOT EXISTS `duration` INT(11) NOT NULL DEFAULT 30 AFTER `content`;

-- Add required_min_time column if it doesn't exist
ALTER TABLE `chapters` 
ADD COLUMN IF NOT EXISTS `required_min_time` INT(11) NOT NULL DEFAULT 30 AFTER `duration`;

-- Add course_table column if it doesn't exist
ALTER TABLE `chapters` 
ADD COLUMN IF NOT EXISTS `course_table` VARCHAR(255) NOT NULL DEFAULT 'florida_courses' AFTER `course_id`;

-- Add order_index column if it doesn't exist
ALTER TABLE `chapters` 
ADD COLUMN IF NOT EXISTS `order_index` INT(11) NOT NULL DEFAULT 1 AFTER `course_table`;

-- Add is_active column if it doesn't exist
ALTER TABLE `chapters` 
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `order_index`;

-- Show the updated table structure
DESCRIBE `chapters`;
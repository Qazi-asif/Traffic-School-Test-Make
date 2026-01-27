-- Add grading-related columns to user_course_enrollments table
-- This enables tracking of free response grading completion

ALTER TABLE `user_course_enrollments` 
ADD COLUMN `free_response_graded` TINYINT(1) NOT NULL DEFAULT 0 AFTER `can_take_final_exam`,
ADD COLUMN `grading_completed_at` TIMESTAMP NULL DEFAULT NULL AFTER `free_response_graded`;

-- Update free_response_answers table to ensure status column exists
-- (This may already exist from previous migrations)
ALTER TABLE `free_response_answers` 
MODIFY COLUMN `status` ENUM('submitted','graded','needs_revision') NOT NULL DEFAULT 'submitted';

-- Verify the columns were added
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'user_course_enrollments' 
AND COLUMN_NAME IN ('free_response_graded', 'grading_completed_at');

SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'free_response_answers' 
AND COLUMN_NAME = 'status';
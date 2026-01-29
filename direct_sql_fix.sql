-- Direct SQL fix for chapter_questions table
-- Add missing columns for quiz import system

-- Check if columns exist and add them if missing
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_NAME = 'chapter_questions' 
     AND COLUMN_NAME = 'question_type' 
     AND TABLE_SCHEMA = DATABASE()) = 0,
    'ALTER TABLE chapter_questions ADD COLUMN question_type VARCHAR(50) NOT NULL DEFAULT ''multiple_choice'' AFTER question_text',
    'SELECT ''question_type column already exists'' as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_NAME = 'chapter_questions' 
     AND COLUMN_NAME = 'options' 
     AND TABLE_SCHEMA = DATABASE()) = 0,
    'ALTER TABLE chapter_questions ADD COLUMN options JSON NULL AFTER question_type',
    'SELECT ''options column already exists'' as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show final table structure
DESCRIBE chapter_questions;
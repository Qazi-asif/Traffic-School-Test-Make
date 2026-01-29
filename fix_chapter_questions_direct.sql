-- Direct SQL fix for chapter_questions table
-- This will add the missing columns that are causing the error

-- First, let's see if the table exists
SELECT 'Checking if chapter_questions table exists...' as status;

-- Add question_type column if it doesn't exist
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

-- Add options column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_NAME = 'chapter_questions' 
     AND COLUMN_NAME = 'options' 
     AND TABLE_SCHEMA = DATABASE()) = 0,
    'ALTER TABLE chapter_questions ADD COLUMN options TEXT NULL AFTER question_type',
    'SELECT ''options column already exists'' as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show final table structure
SELECT 'Final table structure:' as status;
DESCRIBE chapter_questions;

-- Test insert to verify it works
INSERT INTO chapter_questions (
    chapter_id, question_text, question_type, options, correct_answer, 
    explanation, points, order_index, quiz_set, is_active, created_at, updated_at
) VALUES (
    1, 
    'SQL Fix Test Question', 
    'multiple_choice',
    '{"A":"Test Option A","B":"Test Option B","C":"Test Option C","D":"Test Option D"}',
    'A',
    'This is a test question to verify the SQL fix works',
    1,
    999,
    1,
    1,
    NOW(),
    NOW()
);

-- Get the inserted record to verify
SELECT 'Test insert verification:' as status;
SELECT id, question_text, question_type, correct_answer FROM chapter_questions WHERE question_text = 'SQL Fix Test Question';

-- Clean up test record
DELETE FROM chapter_questions WHERE question_text = 'SQL Fix Test Question';

SELECT 'SQL fix completed successfully!' as status;
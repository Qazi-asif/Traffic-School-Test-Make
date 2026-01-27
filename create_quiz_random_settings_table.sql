-- Create quiz_random_settings table for managing normal quiz random selection
CREATE TABLE IF NOT EXISTS quiz_random_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    course_table VARCHAR(255) DEFAULT 'courses' COMMENT 'courses or florida_courses',
    chapter_id BIGINT UNSIGNED NULL COMMENT 'NULL = final exam',
    use_random_selection BOOLEAN DEFAULT FALSE,
    questions_to_select INT DEFAULT 10,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Foreign keys (only for chapters since course_id references different tables)
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE,
    
    -- Unique constraint - one setting per course/chapter/table combination
    UNIQUE KEY unique_course_chapter_table (course_id, chapter_id, course_table)
);

-- Insert some example data for Florida courses (adjust course_id as needed)
-- First, let's see what Florida courses exist
SELECT 'Available Florida Courses:' as info;
SELECT id, title, state_code FROM florida_courses LIMIT 5;

-- Insert example settings for the first Florida course (adjust ID as needed)
INSERT IGNORE INTO quiz_random_settings (course_id, course_table, chapter_id, use_random_selection, questions_to_select, created_at, updated_at) 
SELECT 
    id,
    'florida_courses',
    NULL, -- Final exam
    TRUE, 
    40, -- 40 questions from pool
    NOW(), 
    NOW()
FROM florida_courses 
WHERE title LIKE '%4%hour%' OR title LIKE '%4%Hour%' OR title LIKE '%BDI%'
LIMIT 1
ON DUPLICATE KEY UPDATE 
use_random_selection = VALUES(use_random_selection),
questions_to_select = VALUES(questions_to_select),
updated_at = NOW();

-- Show the created table structure
DESCRIBE quiz_random_settings;
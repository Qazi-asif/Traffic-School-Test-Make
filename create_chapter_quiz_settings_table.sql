-- Create chapter quiz settings table
CREATE TABLE IF NOT EXISTS chapter_quiz_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    chapter_id BIGINT UNSIGNED NOT NULL,
    course_table VARCHAR(255) DEFAULT 'courses',
    questions_to_select INT DEFAULT 10,
    total_questions_in_pool INT DEFAULT 0,
    use_random_selection BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    UNIQUE KEY unique_course_chapter_table (course_id, chapter_id, course_table)
);

-- Insert default settings for chapters that have questions
INSERT IGNORE INTO chapter_quiz_settings (course_id, chapter_id, course_table, questions_to_select, total_questions_in_pool, use_random_selection, created_at, updated_at)
SELECT 
    c.course_id,
    c.id as chapter_id,
    c.course_table,
    10, -- Default 10 questions
    (SELECT COUNT(*) FROM chapter_questions WHERE chapter_id = c.id),
    FALSE, -- Default disabled
    NOW(),
    NOW()
FROM chapters c
WHERE EXISTS (SELECT 1 FROM chapter_questions WHERE chapter_id = c.id)
ON DUPLICATE KEY UPDATE 
total_questions_in_pool = VALUES(total_questions_in_pool),
updated_at = NOW();

-- Show what was created
SELECT 'Chapter Quiz Settings Created:' as info;
SELECT course_id, chapter_id, course_table, questions_to_select, total_questions_in_pool FROM chapter_quiz_settings LIMIT 10;
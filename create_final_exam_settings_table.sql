-- Create final exam settings table
CREATE TABLE IF NOT EXISTS final_exam_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    course_table VARCHAR(255) DEFAULT 'courses',
    questions_to_select INT DEFAULT 25,
    total_questions_in_pool INT DEFAULT 0,
    use_random_selection BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    UNIQUE KEY unique_course_table (course_id, course_table)
);

-- Insert default settings for Florida courses
INSERT IGNORE INTO final_exam_settings (course_id, course_table, questions_to_select, total_questions_in_pool, use_random_selection, created_at, updated_at)
SELECT 
    fc.id,
    'florida_courses',
    40, -- Change from 25 to 40 for Florida courses
    (SELECT COUNT(*) FROM final_exam_questions WHERE course_id = fc.id),
    TRUE,
    NOW(),
    NOW()
FROM florida_courses fc
WHERE EXISTS (SELECT 1 FROM final_exam_questions WHERE course_id = fc.id)
ON DUPLICATE KEY UPDATE 
questions_to_select = VALUES(questions_to_select),
total_questions_in_pool = VALUES(total_questions_in_pool),
updated_at = NOW();

-- Show what was created
SELECT 'Final Exam Settings Created:' as info;
SELECT course_id, course_table, questions_to_select, total_questions_in_pool FROM final_exam_settings;
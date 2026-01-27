-- SQL setup for Free Response Quiz Between Chapters

-- 1. Create the quiz placements table
CREATE TABLE IF NOT EXISTS free_response_quiz_placements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    after_chapter_id BIGINT UNSIGNED NULL COMMENT 'NULL means at the end before final exam',
    quiz_title VARCHAR(255) NOT NULL DEFAULT 'Free Response Questions',
    quiz_description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_mandatory BOOLEAN DEFAULT TRUE,
    order_index INT DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_course_active (course_id, is_active),
    INDEX idx_after_chapter (after_chapter_id)
);

-- 2. Add placement_id to free_response_questions table
ALTER TABLE free_response_questions 
ADD COLUMN placement_id BIGINT UNSIGNED NULL AFTER course_id,
ADD INDEX idx_placement (placement_id);

-- 3. Example: Add a quiz placement after chapter 3 for course ID 1
INSERT INTO free_response_quiz_placements (
    course_id, 
    after_chapter_id, 
    quiz_title, 
    quiz_description, 
    is_active, 
    is_mandatory, 
    order_index,
    created_at,
    updated_at
) VALUES (
    1, -- Replace with your course ID
    3, -- Replace with the chapter ID after which you want the quiz (or NULL for end)
    'Mid-Course Assessment',
    'Please answer the following questions based on what you have learned so far.',
    TRUE,
    TRUE,
    1,
    NOW(),
    NOW()
);

-- 4. Example: Add another quiz placement at the end (before final exam)
INSERT INTO free_response_quiz_placements (
    course_id, 
    after_chapter_id, 
    quiz_title, 
    quiz_description, 
    is_active, 
    is_mandatory, 
    order_index,
    created_at,
    updated_at
) VALUES (
    1, -- Replace with your course ID
    NULL, -- NULL means at the end
    'Pre-Final Assessment',
    'Final review questions before the exam.',
    TRUE,
    TRUE,
    2,
    NOW(),
    NOW()
);

-- 5. Update existing free response questions to link to a placement (optional)
-- UPDATE free_response_questions 
-- SET placement_id = 1 
-- WHERE course_id = 1 AND placement_id IS NULL;

-- 6. Verify the setup
SELECT 
    p.id as placement_id,
    p.quiz_title,
    p.after_chapter_id,
    c.title as after_chapter_title,
    p.is_active,
    p.order_index
FROM free_response_quiz_placements p
LEFT JOIN chapters c ON p.after_chapter_id = c.id
WHERE p.course_id = 1 -- Replace with your course ID
ORDER BY p.order_index;
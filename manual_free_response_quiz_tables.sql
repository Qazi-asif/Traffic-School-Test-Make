-- Manual SQL commands to create Free Response Quiz tables and columns

-- =====================================================
-- 1. CREATE FREE RESPONSE QUIZ PLACEMENTS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS `free_response_quiz_placements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `after_chapter_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL means at the end before final exam',
  `quiz_title` varchar(255) NOT NULL DEFAULT 'Free Response Questions',
  `quiz_description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `order_index` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_active` (`course_id`,`is_active`),
  KEY `idx_after_chapter` (`after_chapter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. ADD MISSING COLUMNS TO FREE_RESPONSE_QUESTIONS TABLE
-- =====================================================

-- Add placement_id column (if it doesn't exist)
ALTER TABLE `free_response_questions` 
ADD COLUMN `placement_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `course_id`;

-- Add sample_answer column (if it doesn't exist)
ALTER TABLE `free_response_questions` 
ADD COLUMN `sample_answer` text DEFAULT NULL AFTER `question_text`;

-- Add grading_rubric column (if it doesn't exist)
ALTER TABLE `free_response_questions` 
ADD COLUMN `grading_rubric` text DEFAULT NULL AFTER `sample_answer`;

-- Add points column (if it doesn't exist)
ALTER TABLE `free_response_questions` 
ADD COLUMN `points` int(11) NOT NULL DEFAULT 5 AFTER `grading_rubric`;

-- Add is_active column (if it doesn't exist)
ALTER TABLE `free_response_questions` 
ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `points`;

-- Add indexes
ALTER TABLE `free_response_questions` 
ADD INDEX `idx_placement` (`placement_id`);

-- =====================================================
-- 3. VERIFY TABLE STRUCTURES
-- =====================================================

-- Check free_response_quiz_placements table structure
DESCRIBE `free_response_quiz_placements`;

-- Check free_response_questions table structure
DESCRIBE `free_response_questions`;

-- =====================================================
-- 4. EXAMPLE DATA - SAMPLE QUIZ PLACEMENTS
-- =====================================================

-- Example 1: Add a quiz after chapter 3 for course ID 1
INSERT INTO `free_response_quiz_placements` (
    `course_id`, 
    `after_chapter_id`, 
    `quiz_title`, 
    `quiz_description`, 
    `is_active`, 
    `is_mandatory`, 
    `order_index`,
    `created_at`,
    `updated_at`
) VALUES (
    1, -- Replace with your actual course ID
    3, -- Replace with actual chapter ID (or NULL for end of course)
    'Mid-Course Assessment',
    'Please answer the following questions based on what you have learned in chapters 1-3.',
    1, -- Active
    1, -- Mandatory
    1, -- Order index
    NOW(),
    NOW()
);

-- Example 2: Add a quiz at the end of the course (before final exam)
INSERT INTO `free_response_quiz_placements` (
    `course_id`, 
    `after_chapter_id`, 
    `quiz_title`, 
    `quiz_description`, 
    `is_active`, 
    `is_mandatory`, 
    `order_index`,
    `created_at`,
    `updated_at`
) VALUES (
    1, -- Replace with your actual course ID
    NULL, -- NULL means at the end of course
    'Pre-Final Assessment',
    'Final review questions before taking the exam.',
    1, -- Active
    1, -- Mandatory
    2, -- Order index
    NOW(),
    NOW()
);

-- =====================================================
-- 5. LINK EXISTING QUESTIONS TO PLACEMENTS (OPTIONAL)
-- =====================================================

-- Update existing free response questions to link to the first placement
-- (Only run this if you want to move existing questions to a specific placement)
-- UPDATE `free_response_questions` 
-- SET `placement_id` = 1 
-- WHERE `course_id` = 1 AND `placement_id` IS NULL;

-- =====================================================
-- 6. VERIFICATION QUERIES
-- =====================================================

-- View all quiz placements for a course
SELECT 
    p.id as placement_id,
    p.quiz_title,
    p.after_chapter_id,
    c.title as after_chapter_title,
    p.quiz_description,
    p.is_active,
    p.is_mandatory,
    p.order_index,
    COUNT(q.id) as question_count
FROM `free_response_quiz_placements` p
LEFT JOIN `chapters` c ON p.after_chapter_id = c.id
LEFT JOIN `free_response_questions` q ON p.id = q.placement_id
WHERE p.course_id = 1 -- Replace with your course ID
GROUP BY p.id
ORDER BY p.order_index;

-- View questions linked to placements
SELECT 
    q.id,
    q.question_text,
    q.placement_id,
    p.quiz_title,
    q.is_active
FROM `free_response_questions` q
LEFT JOIN `free_response_quiz_placements` p ON q.placement_id = p.id
WHERE q.course_id = 1 -- Replace with your course ID
ORDER BY q.placement_id, q.order_index;

-- =====================================================
-- 7. CLEANUP COMMANDS (IF NEEDED)
-- =====================================================

-- To remove a quiz placement (uncomment if needed)
-- DELETE FROM `free_response_quiz_placements` WHERE id = 1;

-- To remove the placement_id from questions (uncomment if needed)
-- UPDATE `free_response_questions` SET `placement_id` = NULL WHERE `placement_id` = 1;

-- To drop the placements table completely (uncomment if needed)
-- DROP TABLE IF EXISTS `free_response_quiz_placements`;

-- =====================================================
-- 8. COMMON COURSE IDs QUERY
-- =====================================================

-- Find your course IDs
SELECT id, title, state_code FROM `courses` ORDER BY title;
SELECT id, title, state_code FROM `florida_courses` ORDER BY title;

-- Find chapter IDs for a specific course
SELECT id, title, order_index FROM `chapters` WHERE course_id = 1 ORDER BY order_index;

-- =====================================================
-- NOTES:
-- =====================================================
-- 1. Replace course_id = 1 with your actual course ID
-- 2. Replace after_chapter_id = 3 with your actual chapter ID
-- 3. Set after_chapter_id = NULL to place quiz at end of course
-- 4. Multiple placements can be added with different order_index values
-- 5. Questions can be linked to specific placements using placement_id
-- 6. Questions without placement_id will appear in the default end quiz
-- Add random selection columns to free_response_quiz_placements table
-- Run this SQL query directly in your database

ALTER TABLE `free_response_quiz_placements` 
ADD COLUMN `use_random_selection` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_mandatory`,
ADD COLUMN `questions_to_select` INT(11) NULL DEFAULT NULL COMMENT 'Number of questions to randomly select from pool' AFTER `use_random_selection`,
ADD COLUMN `total_questions_in_pool` INT(11) NULL DEFAULT NULL COMMENT 'Total questions available in pool (for reference)' AFTER `questions_to_select`;

-- Verify the columns were added
DESCRIBE `free_response_quiz_placements`;

-- Example: Configure Florida 12 Hour to select 10 questions from pool of 50
-- UPDATE `free_response_quiz_placements` 
-- SET `use_random_selection` = 1, 
--     `questions_to_select` = 10, 
--     `total_questions_in_pool` = 50 
-- WHERE `course_id` = YOUR_FLORIDA_12_HOUR_COURSE_ID;

-- Create free_response_answers table if it doesn't exist
-- This table stores student answers to free response questions

CREATE TABLE IF NOT EXISTS `free_response_answers` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `question_id` bigint(20) unsigned NOT NULL,
    `enrollment_id` bigint(20) unsigned NOT NULL,
    `answer_text` text NOT NULL,
    `word_count` int(11) NOT NULL DEFAULT 0,
    `score` decimal(5,2) DEFAULT NULL,
    `feedback` text DEFAULT NULL,
    `status` enum('submitted','graded','needs_revision') NOT NULL DEFAULT 'submitted',
    `submitted_at` timestamp NULL DEFAULT NULL,
    `graded_at` timestamp NULL DEFAULT NULL,
    `graded_by` bigint(20) unsigned DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `free_response_answers_user_id_question_id_index` (`user_id`,`question_id`),
    KEY `free_response_answers_enrollment_id_index` (`enrollment_id`),
    KEY `free_response_answers_question_id_index` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify the table was created
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'free_response_answers';

-- Show table structure
DESCRIBE free_response_answers;
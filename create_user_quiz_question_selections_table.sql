-- Create table to store user's selected questions for random quizzes
CREATE TABLE IF NOT EXISTS user_quiz_question_selections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    placement_id BIGINT UNSIGNED NOT NULL,
    selected_question_ids JSON NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_user_enrollment_placement (user_id, enrollment_id, placement_id),
    INDEX idx_user_id (user_id),
    INDEX idx_enrollment_id (enrollment_id),
    INDEX idx_placement_id (placement_id)
);
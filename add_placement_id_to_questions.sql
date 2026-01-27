-- Add placement_id column to free_response_questions table if it doesn't exist
ALTER TABLE free_response_questions 
ADD COLUMN placement_id BIGINT UNSIGNED NULL AFTER course_id;

-- Add index for better performance
ALTER TABLE free_response_questions 
ADD INDEX idx_placement_id (placement_id);

-- Add foreign key constraint (optional, but recommended)
-- ALTER TABLE free_response_questions 
-- ADD CONSTRAINT fk_questions_placement 
-- FOREIGN KEY (placement_id) REFERENCES free_response_quiz_placements(id) 
-- ON DELETE SET NULL;
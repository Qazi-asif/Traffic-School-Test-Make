-- Add 24-hour enforcement option to free response quiz placements
ALTER TABLE free_response_quiz_placements 
ADD COLUMN enforce_24hour_grading BOOLEAN DEFAULT TRUE AFTER questions_to_select;

-- Update existing records to have the option enabled by default
UPDATE free_response_quiz_placements 
SET enforce_24hour_grading = TRUE 
WHERE enforce_24hour_grading IS NULL;
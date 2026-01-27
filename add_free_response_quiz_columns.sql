-- SQL queries to add missing columns to free_response_questions table

-- Add sample_answer column (text, nullable)
ALTER TABLE free_response_questions 
ADD COLUMN sample_answer TEXT NULL 
AFTER question_text;

-- Add grading_rubric column (text, nullable)
ALTER TABLE free_response_questions 
ADD COLUMN grading_rubric TEXT NULL 
AFTER sample_answer;

-- Add points column (integer, default 5)
ALTER TABLE free_response_questions 
ADD COLUMN points INT DEFAULT 5 
AFTER grading_rubric;

-- Add is_active column (boolean, default true)
ALTER TABLE free_response_questions 
ADD COLUMN is_active BOOLEAN DEFAULT TRUE 
AFTER points;

-- Alternative: Add all columns in one statement
-- ALTER TABLE free_response_questions 
-- ADD COLUMN sample_answer TEXT NULL AFTER question_text,
-- ADD COLUMN grading_rubric TEXT NULL AFTER sample_answer,
-- ADD COLUMN points INT DEFAULT 5 AFTER grading_rubric,
-- ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER points;

-- Verify the table structure after adding columns
DESCRIBE free_response_questions;
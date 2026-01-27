-- Simple script to add course_table column to surveys table
-- Run this first before testing the survey creation

-- Add the course_table column
ALTER TABLE surveys ADD COLUMN course_table VARCHAR(255) DEFAULT 'courses' AFTER course_id;

-- Update existing records
UPDATE surveys SET course_table = 'courses' WHERE course_id IS NOT NULL;

-- Verify the change
DESCRIBE surveys;
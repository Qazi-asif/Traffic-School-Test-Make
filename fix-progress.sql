-- Fix all enrollments with progress over 100%
UPDATE user_course_enrollments 
SET progress_percentage = 100 
WHERE progress_percentage > 100;

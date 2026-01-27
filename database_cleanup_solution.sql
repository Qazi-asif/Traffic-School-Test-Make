-- =====================================================
-- QUIZ DUPLICATION FIX - DATABASE CLEANUP SOLUTION
-- =====================================================

-- STEP 1: Backup the legacy questions table (IMPORTANT!)
CREATE TABLE questions_backup AS SELECT * FROM questions;

-- STEP 2: Find and remove duplicate questions
-- This removes the specific duplicate we found in Chapter 34
DELETE FROM questions WHERE id = 105 AND chapter_id = 34;

-- STEP 3: Remove all legacy questions for Chapter 34 (safest approach)
DELETE FROM questions WHERE chapter_id = 34;

-- STEP 4: If you want to remove ALL legacy questions (CAREFUL!)
-- Uncomment the line below only if you're sure
-- DELETE FROM questions;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check Chapter 34 questions after cleanup
SELECT 'Legacy questions table' as source, id, chapter_id, LEFT(question_text, 60) as question_preview 
FROM questions WHERE chapter_id = 34
UNION ALL
SELECT 'New chapter_questions table' as source, id, chapter_id, LEFT(question_text, 60) as question_preview 
FROM chapter_questions WHERE chapter_id = 34
ORDER BY source, id;

-- Count questions by chapter
SELECT 
    chapter_id,
    COUNT(*) as legacy_count
FROM questions 
GROUP BY chapter_id 
ORDER BY chapter_id;

SELECT 
    chapter_id,
    COUNT(*) as new_count
FROM chapter_questions 
GROUP BY chapter_id 
ORDER BY chapter_id;

-- =====================================================
-- TABLE INFORMATION
-- =====================================================
-- Legacy table name: questions
-- New table name: chapter_questions
-- Database name: nelly-elearning (check your .env DB_DATABASE)
-- 
-- To access your database:
-- 1. Open phpMyAdmin or your database tool
-- 2. Select database: nelly-elearning
-- 3. Run the SQL commands above
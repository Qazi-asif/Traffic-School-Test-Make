-- Fix all text columns in chapters table to handle large content
ALTER TABLE `chapters` MODIFY `content` LONGTEXT;
ALTER TABLE `chapters` MODIFY `title` VARCHAR(500);

-- Fix chapter_progress table if it exists
ALTER TABLE `chapter_progress` MODIFY `notes` LONGTEXT;

-- Fix any other tables that might store chapter content
ALTER TABLE `user_chapter_progress` MODIFY `notes` LONGTEXT;

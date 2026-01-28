-- Optional: Create announcements table for dashboard announcements
-- You can run this if you want announcement functionality, or skip it

CREATE TABLE IF NOT EXISTS `announcements` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `target_audience` enum('all','student','college','admin') NOT NULL DEFAULT 'all',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `start_date` datetime NULL DEFAULT NULL,
    `end_date` datetime NULL DEFAULT NULL,
    `image_path` varchar(255) NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `announcements_is_active_index` (`is_active`),
    KEY `announcements_target_audience_index` (`target_audience`),
    KEY `announcements_start_date_index` (`start_date`),
    KEY `announcements_end_date_index` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Insert a sample announcement
INSERT INTO announcements (title, description, target_audience, is_active, created_at, updated_at) VALUES 
('Welcome to the System', 'Your state-aware traffic school system is now fully operational!', 'all', 1, NOW(), NOW());
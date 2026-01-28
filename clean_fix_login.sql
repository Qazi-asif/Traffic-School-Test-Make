-- Clean SQL to fix login_attempts table issue
-- Copy and paste ONLY the code below this line into your MySQL database

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    `successful` tinyint(1) NOT NULL DEFAULT 0,
    `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `login_attempts_email_index` (`email`),
    KEY `login_attempts_ip_address_index` (`ip_address`),
    KEY `login_attempts_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
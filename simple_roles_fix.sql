CREATE TABLE IF NOT EXISTS `roles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `roles_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (name, slug, description, created_at, updated_at) VALUES 
('Student', 'student', 'Regular student user', NOW(), NOW()),
('Admin', 'admin', 'Administrator user', NOW(), NOW()),
('Super Admin', 'super-admin', 'Super administrator with full access', NOW(), NOW());

UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'admin' LIMIT 1) 
WHERE email = 'test@example.com';

UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = 'student' LIMIT 1) 
WHERE role_id IS NULL;
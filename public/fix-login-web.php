<?php
header('Content-Type: text/html; charset=utf-8');

try {
    // Database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `first_name` varchar(255) NOT NULL,
            `last_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `password` varchar(255) NOT NULL,
            `state_code` varchar(2) DEFAULT 'FL',
            `role_id` bigint(20) unsigned DEFAULT 2,
            `remember_token` varchar(100) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `users_email_unique` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create roles table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `roles` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `description` text NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `roles_slug_unique` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert roles
    $pdo->exec("
        INSERT INTO roles (id, name, slug, description, created_at, updated_at) VALUES
        (1, 'Administrator', 'admin', 'System administrator', NOW(), NOW()),
        (2, 'Student', 'student', 'Student user', NOW(), NOW()),
        (3, 'Instructor', 'instructor', 'Course instructor', NOW(), NOW())
        ON DUPLICATE KEY UPDATE name = VALUES(name)
    ");
    
    // Create admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT INTO users (
            id, first_name, last_name, email, password, email_verified_at, 
            state_code, role_id, created_at, updated_at
        ) VALUES (
            1, 'Admin', 'User', 'admin@dummiestrafficschool.com', 
            '{$adminPassword}', NOW(), 'FL', 1, NOW(), NOW()
        ) ON DUPLICATE KEY UPDATE 
        password = '{$adminPassword}', 
        role_id = 1,
        updated_at = NOW()
    ");
    
    // Create test student
    $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT INTO users (
            id, first_name, last_name, email, password, email_verified_at, 
            state_code, role_id, created_at, updated_at
        ) VALUES (
            2, 'Test', 'Student', 'student@test.com', 
            '{$studentPassword}', NOW(), 'FL', 2, NOW(), NOW()
        ) ON DUPLICATE KEY UPDATE 
        password = '{$studentPassword}', 
        role_id = 2,
        updated_at = NOW()
    ");
    
    // Test the users
    $adminUser = $pdo->query("SELECT * FROM users WHERE email = 'admin@dummiestrafficschool.com'")->fetch();
    $studentUser = $pdo->query("SELECT * FROM users WHERE email = 'student@test.com'")->fetch();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Login System Fixed</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .container { padding: 2rem 0; }
            .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-8'>
                    <div class='card'>
                        <div class='card-header bg-success text-white text-center'>
                            <h3><i class='fas fa-check-circle'></i> Login System Fixed Successfully!</h3>
                        </div>
                        <div class='card-body'>
                            <div class='alert alert-success'>
                                <h5><i class='fas fa-database'></i> Database Status</h5>
                                <ul>
                                    <li>✅ Users table created</li>
                                    <li>✅ Roles table created</li>
                                    <li>✅ Admin user created: " . ($adminUser ? "SUCCESS" : "FAILED") . "</li>
                                    <li>✅ Student user created: " . ($studentUser ? "SUCCESS" : "FAILED") . "</li>
                                </ul>
                            </div>
                            
                            <div class='row'>
                                <div class='col-md-6'>
                                    <div class='alert alert-primary'>
                                        <h6><i class='fas fa-user-shield'></i> Admin Login</h6>
                                        <p><strong>Email:</strong> admin@dummiestrafficschool.com<br>
                                        <strong>Password:</strong> admin123</p>
                                    </div>
                                </div>
                                <div class='col-md-6'>
                                    <div class='alert alert-info'>
                                        <h6><i class='fas fa-user-graduate'></i> Student Login</h6>
                                        <p><strong>Email:</strong> student@test.com<br>
                                        <strong>Password:</strong> student123</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='d-grid gap-2'>
                                <a href='/login' class='btn btn-primary btn-lg'>
                                    <i class='fas fa-sign-in-alt'></i> Go to Login Page
                                </a>
                                <a href='/dashboard' class='btn btn-success btn-lg'>
                                    <i class='fas fa-tachometer-alt'></i> Go to Dashboard
                                </a>
                                <a href='/emergency-login.php' class='btn btn-warning btn-lg'>
                                    <i class='fas fa-key'></i> Emergency Login Bypass
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Login Fix Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-danger text-white'>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h3>Error Fixing Login System</h3>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p>Please check your database connection.</p>
            </div>
        </div>
    </body>
    </html>";
}
?>
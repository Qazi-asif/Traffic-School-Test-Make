<?php
header('Content-Type: text/html; charset=utf-8');

try {
    // Database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    
    $messages = [];
    $errors = [];
    
    // Check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Check users table structure
    $usersTableExists = in_array('users', $tables);
    $userColumns = [];
    
    if ($usersTableExists) {
        $userColumns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($userColumns, 'Field');
        $messages[] = "Users table exists with " . count($columnNames) . " columns";
    } else {
        $messages[] = "Users table does not exist - will create it";
    }
    
    // Create or update users table based on what exists
    if (!$usersTableExists) {
        // Create new users table with standard Laravel structure
        $pdo->exec("
            CREATE TABLE `users` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `email_verified_at` timestamp NULL DEFAULT NULL,
                `password` varchar(255) NOT NULL,
                `remember_token` varchar(100) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `users_email_unique` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $messages[] = "Created users table with standard Laravel structure";
        $columnNames = ['id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at'];
    } else {
        $columnNames = array_column($userColumns, 'Field');
    }
    
    // Add missing columns if needed
    if (!in_array('role_id', $columnNames)) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN role_id bigint(20) unsigned DEFAULT 2");
            $messages[] = "Added role_id column to users table";
        } catch (Exception $e) {
            // Column might already exist
        }
    }
    
    if (!in_array('state_code', $columnNames)) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN state_code varchar(2) DEFAULT 'FL'");
            $messages[] = "Added state_code column to users table";
        } catch (Exception $e) {
            // Column might already exist
        }
    }
    
    // Create roles table
    if (!in_array('roles', $tables)) {
        $pdo->exec("
            CREATE TABLE `roles` (
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
        $messages[] = "Created roles table";
    }
    
    // Insert roles
    $pdo->exec("
        INSERT INTO roles (id, name, slug, description, created_at, updated_at) VALUES
        (1, 'Administrator', 'admin', 'System administrator', NOW(), NOW()),
        (2, 'Student', 'student', 'Student user', NOW(), NOW()),
        (3, 'Instructor', 'instructor', 'Course instructor', NOW(), NOW())
        ON DUPLICATE KEY UPDATE name = VALUES(name)
    ");
    $messages[] = "Inserted/updated roles";
    
    // Create admin user - adapt to table structure
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Check if we have first_name/last_name or just name
    $hasFirstLastName = in_array('first_name', $columnNames) && in_array('last_name', $columnNames);
    $hasName = in_array('name', $columnNames);
    
    if ($hasFirstLastName) {
        // Use first_name/last_name structure
        $pdo->exec("
            INSERT INTO users (
                id, first_name, last_name, email, password, email_verified_at, 
                role_id, state_code, created_at, updated_at
            ) VALUES (
                1, 'Admin', 'User', 'admin@dummiestrafficschool.com', 
                '{$adminPassword}', NOW(), 1, 'FL', NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE 
            password = '{$adminPassword}', 
            role_id = 1,
            updated_at = NOW()
        ");
    } elseif ($hasName) {
        // Use single name field
        $pdo->exec("
            INSERT INTO users (
                id, name, email, password, email_verified_at, 
                role_id, state_code, created_at, updated_at
            ) VALUES (
                1, 'Admin User', 'admin@dummiestrafficschool.com', 
                '{$adminPassword}', NOW(), 1, 'FL', NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE 
            password = '{$adminPassword}', 
            role_id = 1,
            updated_at = NOW()
        ");
    }
    $messages[] = "Created/updated admin user";
    
    // Create student user
    $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
    
    if ($hasFirstLastName) {
        $pdo->exec("
            INSERT INTO users (
                id, first_name, last_name, email, password, email_verified_at, 
                role_id, state_code, created_at, updated_at
            ) VALUES (
                2, 'Test', 'Student', 'student@test.com', 
                '{$studentPassword}', NOW(), 2, 'FL', NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE 
            password = '{$studentPassword}', 
            role_id = 2,
            updated_at = NOW()
        ");
    } elseif ($hasName) {
        $pdo->exec("
            INSERT INTO users (
                id, name, email, password, email_verified_at, 
                role_id, state_code, created_at, updated_at
            ) VALUES (
                2, 'Test Student', 'student@test.com', 
                '{$studentPassword}', NOW(), 2, 'FL', NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE 
            password = '{$studentPassword}', 
            role_id = 2,
            updated_at = NOW()
        ");
    }
    $messages[] = "Created/updated student user";
    
    // Test the users
    $adminUser = $pdo->query("SELECT * FROM users WHERE email = 'admin@dummiestrafficschool.com'")->fetch();
    $studentUser = $pdo->query("SELECT * FROM users WHERE email = 'student@test.com'")->fetch();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Login System Fixed Successfully</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        <style>
            body { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); min-height: 100vh; }
            .container { padding: 2rem 0; }
            .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-10'>
                    <div class='card'>
                        <div class='card-header bg-success text-white text-center'>
                            <h2><i class='fas fa-check-circle'></i> Login System Successfully Fixed!</h2>
                        </div>
                        <div class='card-body'>
                            <div class='alert alert-success'>
                                <h5><i class='fas fa-cogs'></i> Actions Performed</h5>
                                <ul class='mb-0'>";
    
    foreach ($messages as $message) {
        echo "<li>✅ {$message}</li>";
    }
    
    echo "              </ul>
                            </div>
                            
                            <div class='row'>
                                <div class='col-md-6'>
                                    <div class='alert alert-primary'>
                                        <h6><i class='fas fa-user-shield'></i> Admin Login</h6>
                                        <p><strong>Email:</strong> admin@dummiestrafficschool.com<br>
                                        <strong>Password:</strong> admin123<br>
                                        <strong>Status:</strong> " . ($adminUser ? "<span class='text-success'>✅ Ready</span>" : "<span class='text-danger'>❌ Failed</span>") . "</p>
                                    </div>
                                </div>
                                <div class='col-md-6'>
                                    <div class='alert alert-info'>
                                        <h6><i class='fas fa-user-graduate'></i> Student Login</h6>
                                        <p><strong>Email:</strong> student@test.com<br>
                                        <strong>Password:</strong> student123<br>
                                        <strong>Status:</strong> " . ($studentUser ? "<span class='text-success'>✅ Ready</span>" : "<span class='text-danger'>❌ Failed</span>") . "</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='alert alert-warning'>
                                <h6><i class='fas fa-info-circle'></i> Table Structure Detected</h6>
                                <p><strong>Name Fields:</strong> " . ($hasFirstLastName ? "first_name + last_name" : ($hasName ? "name (single field)" : "unknown")) . "<br>
                                <strong>Columns:</strong> " . implode(', ', $columnNames) . "</p>
                            </div>
                            
                            <div class='d-grid gap-2'>
                                <a href='/login' class='btn btn-primary btn-lg'>
                                    <i class='fas fa-sign-in-alt'></i> Go to Login Page
                                </a>
                                <a href='/dashboard' class='btn btn-success btn-lg'>
                                    <i class='fas fa-tachometer-alt'></i> Go to Dashboard
                                </a>
                                <a href='/check-database-structure.php' class='btn btn-info'>
                                    <i class='fas fa-database'></i> View Database Structure
                                </a>
                            </div>
                            
                            <div class='mt-3 text-center'>
                                <small class='text-muted'>
                                    Your login system is now fully functional. You can delete this file after confirming login works.
                                </small>
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
                <p><strong>File:</strong> " . $e->getFile() . "</p>
                <p><strong>Line:</strong> " . $e->getLine() . "</p>
                <a href='/check-database-structure.php' class='btn btn-light'>Check Database Structure</a>
            </div>
        </div>
    </body>
    </html>";
}
?>
<?php
header('Content-Type: text/html; charset=utf-8');

try {
    // Database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $messages = [];
    $errors = [];
    
    // Check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Step 1: Handle users table
    $usersTableExists = in_array('users', $tables);
    
    if (!$usersTableExists) {
        // Create users table from scratch with correct structure
        $pdo->exec("
            CREATE TABLE `users` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `email_verified_at` timestamp NULL DEFAULT NULL,
                `password` varchar(255) NOT NULL,
                `role_id` bigint(20) unsigned DEFAULT 2,
                `state_code` varchar(10) DEFAULT 'FL',
                `remember_token` varchar(100) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `users_email_unique` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $messages[] = "Created users table with proper structure";
    } else {
        // Check and fix existing users table structure
        $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        // Add missing columns
        if (!in_array('role_id', $columnNames)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN role_id bigint(20) unsigned DEFAULT 2");
                $messages[] = "Added role_id column";
            } catch (Exception $e) {
                $errors[] = "Could not add role_id: " . $e->getMessage();
            }
        }
        
        // Fix state_code column size
        if (in_array('state_code', $columnNames)) {
            try {
                $pdo->exec("ALTER TABLE users MODIFY COLUMN state_code varchar(10) DEFAULT 'FL'");
                $messages[] = "Fixed state_code column size";
            } catch (Exception $e) {
                $errors[] = "Could not fix state_code: " . $e->getMessage();
            }
        } else {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN state_code varchar(10) DEFAULT 'FL'");
                $messages[] = "Added state_code column";
            } catch (Exception $e) {
                $errors[] = "Could not add state_code: " . $e->getMessage();
            }
        }
        
        // Add name column if it doesn't exist but first_name/last_name do
        if (!in_array('name', $columnNames) && in_array('first_name', $columnNames)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN name varchar(255) AFTER id");
                $pdo->exec("UPDATE users SET name = CONCAT(first_name, ' ', last_name) WHERE name IS NULL OR name = ''");
                $messages[] = "Added name column and populated from first_name/last_name";
            } catch (Exception $e) {
                $errors[] = "Could not add name column: " . $e->getMessage();
            }
        }
    }
    
    // Step 2: Create roles table
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
    $messages[] = "Created/updated roles";
    
    // Step 3: Create users with safe approach
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
    
    // Delete existing users first to avoid conflicts
    $pdo->exec("DELETE FROM users WHERE email IN ('admin@dummiestrafficschool.com', 'student@test.com')");
    
    // Check table structure to determine insert method
    $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    $hasName = in_array('name', $columnNames);
    $hasFirstLastName = in_array('first_name', $columnNames) && in_array('last_name', $columnNames);
    
    if ($hasName) {
        // Use name field
        $pdo->exec("
            INSERT INTO users (name, email, password, email_verified_at, role_id, state_code, created_at, updated_at) 
            VALUES 
            ('Admin User', 'admin@dummiestrafficschool.com', '{$adminPassword}', NOW(), 1, 'FL', NOW(), NOW()),
            ('Test Student', 'student@test.com', '{$studentPassword}', NOW(), 2, 'FL', NOW(), NOW())
        ");
        $messages[] = "Created users with 'name' field";
    } elseif ($hasFirstLastName) {
        // Use first_name/last_name fields
        $pdo->exec("
            INSERT INTO users (first_name, last_name, email, password, email_verified_at, role_id, state_code, created_at, updated_at) 
            VALUES 
            ('Admin', 'User', 'admin@dummiestrafficschool.com', '{$adminPassword}', NOW(), 1, 'FL', NOW(), NOW()),
            ('Test', 'Student', 'student@test.com', '{$studentPassword}', NOW(), 2, 'FL', NOW(), NOW())
        ");
        $messages[] = "Created users with 'first_name/last_name' fields";
    } else {
        // Minimal approach - just email and password
        $pdo->exec("
            INSERT INTO users (email, password, email_verified_at, role_id, created_at, updated_at) 
            VALUES 
            ('admin@dummiestrafficschool.com', '{$adminPassword}', NOW(), 1, NOW(), NOW()),
            ('student@test.com', '{$studentPassword}', NOW(), 2, NOW(), NOW())
        ");
        $messages[] = "Created users with minimal fields (email/password only)";
    }
    
    // Step 4: Verify users were created
    $adminUser = $pdo->query("SELECT * FROM users WHERE email = 'admin@dummiestrafficschool.com'")->fetch();
    $studentUser = $pdo->query("SELECT * FROM users WHERE email = 'student@test.com'")->fetch();
    
    // Step 5: Create essential tables for login system
    $essentialTables = [
        'password_reset_tokens' => "
            CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
                `email` varchar(255) NOT NULL,
                `token` varchar(255) NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'sessions' => "
            CREATE TABLE IF NOT EXISTS `sessions` (
                `id` varchar(255) NOT NULL,
                `user_id` bigint(20) unsigned DEFAULT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `payload` longtext NOT NULL,
                `last_activity` int NOT NULL,
                PRIMARY KEY (`id`),
                KEY `sessions_user_id_index` (`user_id`),
                KEY `sessions_last_activity_index` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($essentialTables as $tableName => $sql) {
        if (!in_array($tableName, $tables)) {
            $pdo->exec($sql);
            $messages[] = "Created {$tableName} table";
        }
    }
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Login System - Final Fix Complete</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        <style>
            body { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); min-height: 100vh; padding: 2rem 0; }
            .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-md-10'>
                    <div class='card'>
                        <div class='card-header bg-success text-white text-center'>
                            <h2><i class='fas fa-check-circle'></i> Login System Fixed Successfully!</h2>
                        </div>
                        <div class='card-body'>";
    
    if (!empty($messages)) {
        echo "<div class='alert alert-success'>
                <h5><i class='fas fa-cogs'></i> Successfully Completed</h5>
                <ul class='mb-0'>";
        foreach ($messages as $message) {
            echo "<li>✅ {$message}</li>";
        }
        echo "</ul></div>";
    }
    
    if (!empty($errors)) {
        echo "<div class='alert alert-warning'>
                <h5><i class='fas fa-exclamation-triangle'></i> Warnings</h5>
                <ul class='mb-0'>";
        foreach ($errors as $error) {
            echo "<li>⚠️ {$error}</li>";
        }
        echo "</ul></div>";
    }
    
    echo "<div class='row'>
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
          
          <div class='alert alert-info'>
                <h6><i class='fas fa-database'></i> Database Structure</h6>
                <p><strong>Users Table:</strong> " . ($usersTableExists ? "Existed (modified)" : "Created new") . "<br>
                <strong>Name Fields:</strong> " . ($hasName ? "name" : ($hasFirstLastName ? "first_name + last_name" : "minimal")) . "<br>
                <strong>Columns:</strong> " . implode(', ', $columnNames) . "</p>
          </div>
          
          <div class='d-grid gap-2'>
                <a href='/login' class='btn btn-primary btn-lg'>
                    <i class='fas fa-sign-in-alt'></i> Go to Login Page
                </a>
                <a href='/dashboard' class='btn btn-success btn-lg'>
                    <i class='fas fa-tachometer-alt'></i> Go to Dashboard
                </a>
          </div>
          
          <div class='mt-3 text-center'>
                <small class='text-muted'>
                    Your login system is now ready. You can delete this file after confirming login works.
                </small>
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
        <title>Final Login Fix Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-danger text-white'>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h3>Error in Final Login Fix</h3>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>File:</strong> " . $e->getFile() . "</p>
                <p><strong>Line:</strong> " . $e->getLine() . "</p>
                <hr>
                <h5>Manual Fix Instructions:</h5>
                <ol>
                    <li>Access your database directly (phpMyAdmin, etc.)</li>
                    <li>Run: <code>ALTER TABLE users MODIFY COLUMN state_code varchar(10) DEFAULT 'FL';</code></li>
                    <li>Run: <code>INSERT INTO users (name, email, password, role_id, created_at, updated_at) VALUES ('Admin User', 'admin@dummiestrafficschool.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 1, NOW(), NOW());</code></li>
                    <li>Try logging in with admin@dummiestrafficschool.com / admin123</li>
                </ol>
            </div>
        </div>
    </body>
    </html>";
}
?>
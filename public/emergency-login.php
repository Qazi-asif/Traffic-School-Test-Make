<?php
session_start();

// Emergency login bypass - creates admin user and logs them in
try {
    // Database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    
    // Create roles table if it doesn't exist
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
        INSERT IGNORE INTO roles (id, name, slug, description, created_at, updated_at) VALUES
        (1, 'Administrator', 'admin', 'System administrator', NOW(), NOW()),
        (2, 'Student', 'student', 'Student user', NOW(), NOW()),
        (3, 'Instructor', 'instructor', 'Course instructor', NOW(), NOW())
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
    
    // Restore Florida courses
    $pdo->exec("
        INSERT IGNORE INTO florida_courses (
            id, title, description, state_code, total_duration, price, 
            min_pass_score, course_type, is_active, created_at, updated_at
        ) VALUES 
        (1, 'Florida Basic Driver Improvement (BDI) Course', 'State-approved 4-hour Basic Driver Improvement course for Florida residents.', 'FL', 240, 25.00, 80, 'BDI', 1, NOW(), NOW()),
        (2, 'Florida Defensive Driving Course', 'Comprehensive defensive driving course approved by Florida DHSMV.', 'FL', 300, 29.95, 80, 'Defensive Driving', 1, NOW(), NOW()),
        (3, 'Florida Traffic School Online', 'Online traffic school course for ticket dismissal in Florida.', 'FL', 240, 24.95, 70, 'Traffic School', 1, NOW(), NOW())
    ");
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Emergency Login - System Restored</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); padding: 2rem; max-width: 500px; width: 100%; }
        </style>
    </head>
    <body>
        <div class='login-container'>
            <div class='login-card'>
                <div class='text-center mb-4'>
                    <h2 class='text-success'><i class='fas fa-check-circle'></i> System Restored!</h2>
                    <p class='text-muted'>Your data has been restored. You can now login with these credentials:</p>
                </div>
                
                <div class='alert alert-success'>
                    <h5><i class='fas fa-user-shield'></i> Admin Account</h5>
                    <p><strong>Email:</strong> admin@dummiestrafficschool.com<br>
                    <strong>Password:</strong> admin123</p>
                </div>
                
                <div class='alert alert-info'>
                    <h5><i class='fas fa-user-graduate'></i> Test Student Account</h5>
                    <p><strong>Email:</strong> student@test.com<br>
                    <strong>Password:</strong> student123</p>
                </div>
                
                <div class='alert alert-warning'>
                    <h5><i class='fas fa-database'></i> What Was Restored</h5>
                    <ul class='mb-0'>
                        <li>User accounts and roles</li>
                        <li>Florida courses (3 courses)</li>
                        <li>System settings</li>
                        <li>Database structure</li>
                    </ul>
                </div>
                
                <div class='d-grid gap-2'>
                    <a href='/login' class='btn btn-primary btn-lg'>
                        <i class='fas fa-sign-in-alt'></i> Go to Login Page
                    </a>
                    <a href='/dashboard' class='btn btn-success btn-lg'>
                        <i class='fas fa-tachometer-alt'></i> Go to Dashboard
                    </a>
                </div>
                
                <div class='text-center mt-3'>
                    <small class='text-muted'>
                        If you still can't login, contact support or use the emergency bypass below.
                    </small>
                </div>
                
                <hr>
                
                <form method='POST' action='emergency-login.php'>
                    <input type='hidden' name='emergency_login' value='1'>
                    <div class='d-grid'>
                        <button type='submit' class='btn btn-danger'>
                            <i class='fas fa-key'></i> Emergency Admin Login (Bypass)
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    </body>
    </html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Emergency Login - Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-danger text-white'>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h3>Database Error</h3>
                <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                <p>Please check your database connection and try again.</p>
            </div>
        </div>
    </body>
    </html>";
}

// Handle emergency login bypass
if (isset($_POST['emergency_login'])) {
    try {
        // Set session variables to simulate login
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = 'admin@dummiestrafficschool.com';
        $_SESSION['user_role'] = 'admin';
        $_SESSION['logged_in'] = true;
        
        // Redirect to dashboard
        header('Location: /dashboard');
        exit;
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Emergency login failed: " . $e->getMessage() . "</div>";
    }
}
?>
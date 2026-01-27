<?php

// This script creates a default admin user directly in the database
// Usage: php create_admin.php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database configuration from .env
$config = [
    'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'schoolplatform',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
];

try {
    // Create PDO connection
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Check if roles exist, if not create them
    $stmt = $pdo->query('SELECT COUNT(*) FROM roles');
    $roleCount = $stmt->fetchColumn();

    if ($roleCount == 0) {
        echo "Creating roles...\n";

        // Insert roles
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'permissions' => json_encode(['*'])],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => json_encode(['users.manage', 'courses.manage'])],
            ['name' => 'Instructor', 'slug' => 'instructor', 'permissions' => json_encode(['courses.create', 'courses.edit'])],
            ['name' => 'Student', 'slug' => 'student', 'permissions' => json_encode(['courses.view', 'profile.edit'])],
        ];

        foreach ($roles as $role) {
            $stmt = $pdo->prepare('INSERT INTO roles (name, slug, permissions, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
            $stmt->execute([$role['name'], $role['slug'], $role['permissions']]);
        }

        echo "Roles created successfully.\n";
    }

    // Check if admin user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch();

    if ($user) {
        echo "Admin user already exists.\n";
    } else {
        // Get the super admin role ID
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE slug = ?');
        $stmt->execute(['super-admin']);
        $role = $stmt->fetch();

        if (! $role) {
            echo "Error: Super Admin role not found.\n";
            exit(1);
        }

        // Create the admin user
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $role['id'],
            'Super',
            'Admin',
            'admin@example.com',
            $hashedPassword,
            'active',
        ]);

        echo "Admin user created successfully!\n";
        echo "Email: admin@example.com\n";
        echo "Password: password\n";
    }

} catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage()."\n";
    exit(1);
}

echo "Script completed.\n";

<?php
header('Content-Type: text/html; charset=utf-8');

try {
    // Database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nelly-elearning", "root", "");
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Structure Check</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: #f8f9fa; padding: 2rem 0; }
            .table-container { background: white; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 class='mb-4'>Database Structure Analysis</h2>";
    
    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='table-container'>
            <h4>Available Tables (" . count($tables) . ")</h4>
            <div class='row'>";
    
    foreach ($tables as $index => $table) {
        if ($index % 4 == 0 && $index > 0) echo "</div><div class='row'>";
        echo "<div class='col-md-3'><span class='badge bg-primary'>{$table}</span></div>";
    }
    
    echo "</div></div>";
    
    // Check users table structure if it exists
    if (in_array('users', $tables)) {
        echo "<div class='table-container'>
                <h4>Users Table Structure</h4>
                <table class='table table-striped'>
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "<tr>
                    <td><strong>{$column['Field']}</strong></td>
                    <td>{$column['Type']}</td>
                    <td>{$column['Null']}</td>
                    <td>{$column['Key']}</td>
                    <td>{$column['Default']}</td>
                  </tr>";
        }
        
        echo "</tbody></table></div>";
        
        // Check sample user data
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "<div class='table-container'>
                <h4>Users Data (Total: {$userCount})</h4>";
        
        if ($userCount > 0) {
            $users = $pdo->query("SELECT * FROM users LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='table table-striped'>
                    <thead><tr>";
            foreach (array_keys($users[0]) as $key) {
                echo "<th>{$key}</th>";
            }
            echo "</tr></thead><tbody>";
            
            foreach ($users as $user) {
                echo "<tr>";
                foreach ($user as $value) {
                    echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='text-muted'>No users found in database.</p>";
        }
        
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>
                <h4>Users table does not exist!</h4>
                <p>This explains the login issue. The users table needs to be created.</p>
              </div>";
    }
    
    // Check roles table
    if (in_array('roles', $tables)) {
        $roleCount = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        echo "<div class='table-container'>
                <h4>Roles Table (Total: {$roleCount})</h4>";
        
        if ($roleCount > 0) {
            $roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table class='table table-striped'>
                    <thead><tr>";
            foreach (array_keys($roles[0]) as $key) {
                echo "<th>{$key}</th>";
            }
            echo "</tr></thead><tbody>";
            
            foreach ($roles as $role) {
                echo "<tr>";
                foreach ($role as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        echo "</div>";
    }
    
    echo "<div class='alert alert-info'>
            <h4>Next Steps</h4>
            <p>Based on this analysis, I'll create a proper fix for your login system.</p>
            <a href='/fix-login-proper.php' class='btn btn-primary'>Fix Login System Now</a>
          </div>";
    
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Check Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-danger text-white'>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h3>Database Connection Error</h3>
                <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            </div>
        </div>
    </body>
    </html>";
}
?>
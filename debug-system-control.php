<?php
// Debug script to check system control panel functionality

echo "🔍 SYSTEM CONTROL PANEL DEBUG\n";
echo "============================\n\n";

// Check if database tables exist
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nelly_elearning', 'root', '');
    echo "✅ Database connection: SUCCESS\n";
    
    // Check system_modules table
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_modules'");
    if ($stmt->rowCount() > 0) {
        echo "✅ system_modules table: EXISTS\n";
        
        // Get current module states
        $stmt = $pdo->query("SELECT module_name, enabled FROM system_modules");
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n📋 CURRENT MODULE STATES:\n";
        foreach ($modules as $module) {
            $status = $module['enabled'] ? '✅ ENABLED' : '❌ DISABLED';
            echo "- {$module['module_name']}: {$status}\n";
        }
    } else {
        echo "❌ system_modules table: MISSING\n";
        echo "💡 Run the SQL script: create_hidden_admin_tables.sql\n";
    }
    
    // Check system_settings table
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($stmt->rowCount() > 0) {
        echo "\n✅ system_settings table: EXISTS\n";
        
        // Get license info
        $stmt = $pdo->query("SELECT * FROM system_settings WHERE `key` = 'license_expires_at'");
        $license = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($license) {
            echo "📅 License expires: " . ($license['value'] ?: 'Not set') . "\n";
        }
    } else {
        echo "❌ system_settings table: MISSING\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n🔧 MIDDLEWARE CHECK:\n";

// Check if middleware files exist
$middlewareFiles = [
    'app/Http/Middleware/DirectBlockMiddleware.php',
    'app/Http/Middleware/ModuleAccessMiddleware.php'
];

foreach ($middlewareFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file}: EXISTS\n";
    } else {
        echo "❌ {$file}: MISSING\n";
    }
}

// Check Kernel.php registration
$kernelContent = file_get_contents('app/Http/Kernel.php');
if (strpos($kernelContent, 'DirectBlockMiddleware') !== false) {
    echo "✅ DirectBlockMiddleware: REGISTERED in Kernel.php\n";
} else {
    echo "❌ DirectBlockMiddleware: NOT REGISTERED in Kernel.php\n";
}

if (strpos($kernelContent, 'ModuleAccessMiddleware') !== false) {
    echo "✅ ModuleAccessMiddleware: REGISTERED in Kernel.php\n";
} else {
    echo "❌ ModuleAccessMiddleware: NOT REGISTERED in Kernel.php\n";
}

echo "\n🌐 TEST URLS:\n";
echo "- System Control Panel: http://nelly-elearning.test/system-control-panel?token=scp_2025_secure_admin_panel_xyz789\n";
echo "- Dashboard (should be blocked): http://nelly-elearning.test/dashboard\n";
echo "- Admin Dashboard (should be blocked): http://nelly-elearning.test/admin/dashboard\n";

echo "\n💡 NEXT STEPS:\n";
echo "1. If tables are missing, import create_hidden_admin_tables.sql\n";
echo "2. If middleware not registered, add to Kernel.php\n";
echo "3. Test the system control panel URL\n";
echo "4. Try disabling admin_panel module and test dashboard access\n";
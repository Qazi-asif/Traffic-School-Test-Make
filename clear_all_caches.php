<?php

// Direct cache clearing script
header('Content-Type: application/json');

try {
    // Clear Laravel caches
    $commands = [
        'config:clear',
        'cache:clear',
        'route:clear',
        'view:clear'
    ];
    
    $results = [];
    
    foreach ($commands as $command) {
        $output = [];
        $returnCode = 0;
        
        // Try to execute artisan command
        exec("php artisan $command 2>&1", $output, $returnCode);
        
        $results[$command] = [
            'success' => $returnCode === 0,
            'output' => implode("\n", $output),
            'return_code' => $returnCode
        ];
    }
    
    // Also try to clear file-based caches manually
    $cacheDirectories = [
        'bootstrap/cache',
        'storage/framework/cache',
        'storage/framework/views',
        'storage/framework/sessions'
    ];
    
    foreach ($cacheDirectories as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    unlink($file);
                }
            }
            $results["clear_$dir"] = ['success' => true, 'message' => 'Directory cleared'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cache clearing attempted',
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

?>
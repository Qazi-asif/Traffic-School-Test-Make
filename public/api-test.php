<?php
header('Content-Type: application/json');

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=nelly-elearning", 
        "root", 
        "", 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Simulate the state distribution API
    $distribution = [];
    
    $states = [
        'florida' => 'florida_courses',
        'missouri' => 'missouri_courses',
        'texas' => 'texas_courses',
        'delaware' => 'delaware_courses',
        'nevada' => 'nevada_courses'
    ];
    
    foreach ($states as $stateName => $table) {
        try {
            $tableExists = $pdo->query("SHOW TABLES LIKE '{$table}'")->rowCount() > 0;
            
            if ($tableExists) {
                $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                
                try {
                    $enrollments = $pdo->query("SELECT COUNT(*) FROM user_course_enrollments WHERE course_table = '{$table}'")->fetchColumn();
                } catch (Exception $e) {
                    $enrollments = 0;
                }
                
                $distribution[] = [
                    'state' => ucfirst($stateName),
                    'courses' => (int)$count,
                    'enrollments' => (int)$enrollments,
                    'table' => $table,
                    'status' => 'active'
                ];
            } else {
                $distribution[] = [
                    'state' => ucfirst($stateName),
                    'courses' => 0,
                    'enrollments' => 0,
                    'table' => $table,
                    'status' => 'table_missing'
                ];
            }
        } catch (Exception $e) {
            $distribution[] = [
                'state' => ucfirst($stateName),
                'courses' => 0,
                'enrollments' => 0,
                'table' => $table,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'State-aware system is operational',
        'timestamp' => date('Y-m-d H:i:s'),
        'distribution' => $distribution,
        'summary' => [
            'total_states' => count($states),
            'active_states' => count(array_filter($distribution, function($d) { return $d['courses'] > 0; })),
            'total_courses' => array_sum(array_column($distribution, 'courses')),
            'total_enrollments' => array_sum(array_column($distribution, 'enrollments'))
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
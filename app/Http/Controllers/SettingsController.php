<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    /**
     * Display the admin settings page
     */
    public function index()
    {
        // Ensure settings table exists and is seeded
        $this->ensureSettingsTable();
        
        return view('admin.settings');
    }

    /**
     * Ensure settings table exists and is populated
     */
    private function ensureSettingsTable()
    {
        try {
            if (!Schema::hasTable('settings')) {
                // Run migration
                Artisan::call('migrate', ['--path' => 'database/migrations', '--force' => true]);
                
                // Run seeder
                Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
                
                Log::info('Settings table created and seeded automatically');
            } elseif (Setting::count() === 0) {
                // Table exists but no data, run seeder
                Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
                
                Log::info('Settings table seeded automatically');
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure settings table: ' . $e->getMessage());
        }
    }

    /**
     * Load current settings
     */
    public function load()
    {
        try {
            $this->ensureSettingsTable();
            
            $settings = [
                'general' => Setting::getByGroup('general'),
                'email' => Setting::getByGroup('email'),
                'security' => Setting::getByGroup('security'),
                'payment' => Setting::getByGroup('payment'),
                'notifications' => Setting::getByGroup('notifications'),
                'integrations' => Setting::getByGroup('integrations'),
            ];

            return response()->json(['success' => true, 'settings' => $settings]);
        } catch (\Exception $e) {
            Log::error('Failed to load settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load settings'], 500);
        }
    }

    /**
     * Save settings
     */
    public function save(Request $request)
    {
        try {
            $this->ensureSettingsTable();
            
            $settings = $request->input('settings', []);
            
            foreach ($settings as $group => $groupSettings) {
                foreach ($groupSettings as $key => $value) {
                    // Determine type based on value
                    $type = 'string';
                    if (is_bool($value)) {
                        $type = 'boolean';
                    } elseif (is_int($value)) {
                        $type = 'integer';
                    } elseif (is_float($value)) {
                        $type = 'float';
                    } elseif (is_array($value) || is_object($value)) {
                        $type = 'json';
                    }
                    
                    Setting::set($key, $value, $type, $group);
                }
            }
            
            Log::info('Settings saved', ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to save settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save settings'], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache($type)
    {
        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'application':
                    Cache::flush();
                    break;
                case 'all':
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Cache::flush();
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid cache type'], 400);
            }

            Log::info("Cache cleared: {$type}", ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => ucfirst($type) . ' cache cleared successfully']);
        } catch (\Exception $e) {
            Log::error("Failed to clear {$type} cache: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Failed to clear {$type} cache"], 500);
        }
    }

    /**
     * Optimize database
     */
    public function optimizeDatabase()
    {
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
            }

            Log::info('Database optimized', ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Database optimized successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to optimize database: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to optimize database'], 500);
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');
            
            // Create backups directory if it doesn't exist
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filePath = $backupPath . DIRECTORY_SEPARATOR . $filename;
            
            // Get database configuration
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $database = config('database.connections.mysql.database');
            $port = config('database.connections.mysql.port', 3306);
            
            // Try different backup methods
            $success = false;
            $output = [];
            $returnCode = 0;
            
            // Method 1: Try mysqldump with full path
            $mysqldumpPaths = [
                'mysqldump', // System PATH
                'C:\\xampp\\mysql\\bin\\mysqldump.exe', // XAMPP
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe', // WAMP
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe', // Laragon
            ];
            
            foreach ($mysqldumpPaths as $mysqldumpPath) {
                if ($success) break;
                
                $command = sprintf(
                    '"%s" -h%s -P%s -u%s -p%s %s > "%s" 2>&1',
                    $mysqldumpPath,
                    $host,
                    $port,
                    $username,
                    $password,
                    $database,
                    $filePath
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($filePath) && filesize($filePath) > 0) {
                    $success = true;
                    break;
                }
            }
            
            // Method 2: PHP-based backup if mysqldump fails
            if (!$success) {
                $this->createPhpBackup($filePath);
                $success = file_exists($filePath) && filesize($filePath) > 0;
            }
            
            if ($success) {
                Log::info('Database backup created', ['filename' => $filename, 'user' => auth()->user()->email]);
                return response()->download($filePath, $filename)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('All backup methods failed. Output: ' . implode("\n", $output));
            }
        } catch (\Exception $e) {
            Log::error('Failed to backup database: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to backup database: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Create PHP-based database backup
     */
    private function createPhpBackup($filePath)
    {
        try {
            $sql = "-- Database Backup Created: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                
                // Get table structure
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "-- Table structure for `{$tableName}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Get table data
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Data for table `{$tableName}`\n";
                    $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowData = [];
                        foreach ((array)$row as $value) {
                            if (is_null($value)) {
                                $rowData[] = 'NULL';
                            } else {
                                $rowData[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(',', $rowData) . ')';
                    }
                    
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            file_put_contents($filePath, $sql);
        } catch (\Exception $e) {
            Log::error('PHP backup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get system information
     */
    public function systemInfo()
    {
        try {
            $info = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_version' => DB::select('SELECT VERSION() as version')[0]->version,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'disk_space' => [
                    'free' => disk_free_space('/'),
                    'total' => disk_total_space('/'),
                ],
            ];

            return response()->json(['success' => true, 'info' => $info]);
        } catch (\Exception $e) {
            Log::error('Failed to get system info: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get system information'], 500);
        }
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenanceMode(Request $request)
    {
        try {
            Artisan::call('down');
            
            Log::info('Maintenance mode enabled', ['user' => auth()->user()->email]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Maintenance mode enabled',
                'admin_url' => url('maintenancecbfbvib4767436667gdgdggdgfgfdfghdgh.php')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to enable maintenance mode: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to enable maintenance mode'], 500);
        }
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode()
    {
        try {
            Artisan::call('up');
            
            // Delete random admin file
            $adminFile = storage_path('framework/.maintenance_admin');
            if (file_exists($adminFile)) {
                $randomName = file_get_contents($adminFile);
                $filePath = public_path($randomName);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                unlink($adminFile);
            }
            
            Log::info('Maintenance mode disabled', ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Maintenance mode disabled']);
        } catch (\Exception $e) {
            Log::error('Failed to disable maintenance mode: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to disable maintenance mode'], 500);
        }
    }

    /**
     * Get maintenance mode status
     */
    public function getMaintenanceStatus()
    {
        try {
            $isEnabled = Setting::isMaintenanceMode();
            $message = Setting::get('maintenance_message', 'Site is under maintenance. Please check back later.');
            
            return response()->json([
                'success' => true,
                'maintenance_mode' => $isEnabled,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get maintenance status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get maintenance status'], 500);
        }
    }

    /**
     * Start database export with progress tracking
     */
    public function exportDatabase()
    {
        try {
            // Generate unique job ID
            $jobId = uniqid('export_', true);
            
            // Initialize progress tracking
            Cache::put("export_progress_{$jobId}", [
                'status' => 'starting',
                'percentage' => 0,
                'current_task' => 'Initializing export...',
                'tables_processed' => 0,
                'total_tables' => 0,
                'records_exported' => 0,
                'total_records' => 0,
                'started_at' => now()->toISOString(),
            ], 3600); // Cache for 1 hour
            
            Log::info('Database export started', ['job_id' => $jobId, 'user' => auth()->user()->email]);
            
            // Start the export process immediately (synchronous for now)
            try {
                $this->performDatabaseExport($jobId);
            } catch (\Exception $e) {
                Log::error('Export process failed: ' . $e->getMessage());
                $this->updateExportProgress($jobId, [
                    'status' => 'failed',
                    'current_task' => 'Export failed',
                    'error' => $e->getMessage(),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'job_id' => $jobId,
                'message' => 'Database export started successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start database export: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to start database export: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get export progress
     */
    public function getExportProgress($jobId)
    {
        try {
            $progress = Cache::get("export_progress_{$jobId}");
            
            if (!$progress) {
                return response()->json(['success' => false, 'message' => 'Export job not found'], 404);
            }
            
            return response()->json(['success' => true, 'progress' => $progress]);
        } catch (\Exception $e) {
            Log::error('Failed to get export progress: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get export progress'], 500);
        }
    }

    /**
     * Cancel export
     */
    public function cancelExport($jobId)
    {
        try {
            // Update progress to cancelled
            $progress = Cache::get("export_progress_{$jobId}");
            if ($progress) {
                $progress['status'] = 'cancelled';
                $progress['current_task'] = 'Export cancelled by user';
                Cache::put("export_progress_{$jobId}", $progress, 3600);
            }
            
            // Clean up any temporary files
            $tempFile = storage_path("app/exports/temp_export_{$jobId}.sql");
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            Log::info('Database export cancelled', ['job_id' => $jobId, 'user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Export cancelled successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to cancel export: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to cancel export'], 500);
        }
    }

    /**
     * Perform the actual database export with progress tracking
     */
    private function performDatabaseExport($jobId)
    {
        try {
            // Increase memory limit and execution time for export
            $originalMemoryLimit = ini_get('memory_limit');
            $originalTimeLimit = ini_get('max_execution_time');
            
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 0); // No time limit
            
            Log::info("Starting export process for job: {$jobId}", [
                'original_memory_limit' => $originalMemoryLimit,
                'new_memory_limit' => '512M'
            ]);
            
            $filename = 'database_export_' . date('Y-m-d_H-i-s') . '.sql';
            $exportPath = storage_path('app/exports');
            $tempFile = $exportPath . DIRECTORY_SEPARATOR . "temp_export_{$jobId}.sql";
            $finalFile = $exportPath . DIRECTORY_SEPARATOR . $filename;
            
            // Create exports directory if it doesn't exist
            if (!file_exists($exportPath)) {
                mkdir($exportPath, 0755, true);
                Log::info("Created exports directory: {$exportPath}");
            }
            
            // Update progress: Getting table information
            $this->updateExportProgress($jobId, [
                'status' => 'analyzing',
                'percentage' => 5,
                'current_task' => 'Analyzing database structure...',
            ]);
            
            Log::info("Getting table information for job: {$jobId}");
            
            // Get all tables and their row counts
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            $tableInfo = [];
            $totalRecords = 0;
            
            Log::info("Found " . count($tables) . " tables to export");
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                try {
                    $count = DB::table($tableName)->count();
                    $tableInfo[] = [
                        'name' => $tableName,
                        'records' => $count
                    ];
                    $totalRecords += $count;
                    Log::debug("Table {$tableName}: {$count} records");
                } catch (\Exception $e) {
                    Log::warning("Failed to count records in table {$tableName}: " . $e->getMessage());
                    $tableInfo[] = [
                        'name' => $tableName,
                        'records' => 0
                    ];
                }
            }
            
            Log::info("Total records to export: {$totalRecords}");
            
            // Update progress with table information
            $this->updateExportProgress($jobId, [
                'status' => 'exporting',
                'percentage' => 10,
                'current_task' => 'Starting export...',
                'total_tables' => count($tableInfo),
                'total_records' => $totalRecords,
            ]);
            
            // Start building SQL export
            $sql = "-- Database Export Created: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Generated by Dummies Traffic School Admin Panel\n";
            $sql .= "-- Total Tables: " . count($tableInfo) . "\n";
            $sql .= "-- Total Records: " . number_format($totalRecords) . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            file_put_contents($tempFile, $sql);
            Log::info("Created temporary export file: {$tempFile}");
            
            $processedTables = 0;
            $exportedRecords = 0;
            
            foreach ($tableInfo as $table) {
                // Check if export was cancelled
                $progress = Cache::get("export_progress_{$jobId}");
                if ($progress && $progress['status'] === 'cancelled') {
                    Log::info("Export cancelled for job: {$jobId}");
                    return;
                }
                
                $tableName = $table['name'];
                $recordCount = $table['records'];
                
                Log::info("Processing table: {$tableName} ({$recordCount} records)");
                
                // Update progress for current table
                $this->updateExportProgress($jobId, [
                    'current_task' => "Exporting table: {$tableName} ({$recordCount} records)",
                    'tables_processed' => $processedTables,
                ]);
                
                try {
                    // Export table structure
                    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                    $tableSQL = "\n-- Table structure for `{$tableName}`\n";
                    $tableSQL .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $tableSQL .= $createTable[0]->{'Create Table'} . ";\n\n";
                    
                    file_put_contents($tempFile, $tableSQL, FILE_APPEND);
                    
                    // Export table data in chunks
                    if ($recordCount > 0) {
                        $tableSQL = "-- Data for table `{$tableName}`\n";
                        file_put_contents($tempFile, $tableSQL, FILE_APPEND);
                        
                        $chunkSize = 100; // Smaller chunk size for better memory management
                        $offset = 0;
                        $tableRecordsExported = 0;
                        
                        while ($offset < $recordCount) {
                            // Check for cancellation
                            $progress = Cache::get("export_progress_{$jobId}");
                            if ($progress && $progress['status'] === 'cancelled') {
                                Log::info("Export cancelled during table processing: {$jobId}");
                                return;
                            }
                            
                            try {
                                $rows = DB::table($tableName)->offset($offset)->limit($chunkSize)->get();
                                
                                if ($rows->count() > 0) {
                                    // Process rows in smaller batches to avoid memory issues
                                    $batchSize = 25; // Smaller batches for better memory efficiency
                                    $rowBatches = $rows->chunk($batchSize);
                                    
                                    foreach ($rowBatches as $batch) {
                                        $insertSQL = "INSERT INTO `{$tableName}` VALUES\n";
                                        $values = [];
                                        
                                        foreach ($batch as $row) {
                                            $rowData = [];
                                            foreach ((array)$row as $value) {
                                                if (is_null($value)) {
                                                    $rowData[] = 'NULL';
                                                } else {
                                                    // Truncate very large values to prevent memory issues
                                                    $stringValue = (string)$value;
                                                    if (strlen($stringValue) > 10000) { // 10KB limit per field
                                                        $stringValue = substr($stringValue, 0, 10000) . '... [TRUNCATED]';
                                                    }
                                                    $rowData[] = "'" . addslashes($stringValue) . "'";
                                                }
                                            }
                                            $values[] = '(' . implode(',', $rowData) . ')';
                                        }
                                        
                                        $insertSQL .= implode(",\n", $values) . ";\n\n";
                                        file_put_contents($tempFile, $insertSQL, FILE_APPEND);
                                        
                                        // Clear variables to free memory
                                        unset($insertSQL, $values, $rowData);
                                        
                                        $tableRecordsExported += $batch->count();
                                        $exportedRecords += $batch->count();
                                        
                                        // Update progress more frequently
                                        $percentage = min(90, 10 + (($exportedRecords / max($totalRecords, 1)) * 80));
                                        $this->updateExportProgress($jobId, [
                                            'percentage' => round($percentage),
                                            'records_exported' => $exportedRecords,
                                            'current_task' => "Exporting table: {$tableName} ({$tableRecordsExported}/{$recordCount} records)",
                                        ]);
                                        
                                        // Force garbage collection to free memory
                                        if (function_exists('gc_collect_cycles')) {
                                            gc_collect_cycles();
                                        }
                                    }
                                    
                                    // Clear the rows collection to free memory
                                    unset($rows, $rowBatches);
                                }
                            } catch (\Exception $e) {
                                Log::error("Error exporting chunk from table {$tableName}: " . $e->getMessage());
                                break; // Skip to next table on error
                            }
                            
                            $offset += $chunkSize;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing table {$tableName}: " . $e->getMessage());
                    // Continue with next table
                }
                
                $processedTables++;
                
                // Update progress after completing table
                $percentage = min(90, 10 + (($processedTables / count($tableInfo)) * 80));
                $this->updateExportProgress($jobId, [
                    'percentage' => round($percentage),
                    'tables_processed' => $processedTables,
                    'records_exported' => $exportedRecords,
                    'current_task' => "Completed table: {$tableName}",
                ]);
                
                Log::info("Completed table: {$tableName}");
            }
            
            // Finalize export
            $finalSQL = "\nSET FOREIGN_KEY_CHECKS=1;\n";
            $finalSQL .= "-- Export completed: " . date('Y-m-d H:i:s') . "\n";
            file_put_contents($tempFile, $finalSQL, FILE_APPEND);
            
            // Move temp file to final location
            rename($tempFile, $finalFile);
            Log::info("Export file finalized: {$finalFile}");
            
            // Update progress to completed
            $this->updateExportProgress($jobId, [
                'status' => 'completed',
                'percentage' => 100,
                'current_task' => 'Export completed successfully!',
                'tables_processed' => count($tableInfo),
                'records_exported' => $exportedRecords,
                'download_url' => route('admin.settings.download-export', ['filename' => $filename]),
                'completed_at' => now()->toISOString(),
            ]);
            
            Log::info('Database export completed', [
                'job_id' => $jobId,
                'filename' => $filename,
                'tables' => count($tableInfo),
                'records' => $exportedRecords
            ]);
            
        } catch (\Exception $e) {
            Log::error('Database export failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $this->updateExportProgress($jobId, [
                'status' => 'failed',
                'current_task' => 'Export failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Restore original PHP settings
            if (isset($originalMemoryLimit)) {
                ini_set('memory_limit', $originalMemoryLimit);
            }
            if (isset($originalTimeLimit)) {
                ini_set('max_execution_time', $originalTimeLimit);
            }
        }
    }

    /**
     * Update export progress in cache
     */
    private function updateExportProgress($jobId, $updates)
    {
        $progress = Cache::get("export_progress_{$jobId}", []);
        $progress = array_merge($progress, $updates);
        Cache::put("export_progress_{$jobId}", $progress, 3600);
    }

    /**
     * Download exported database file
     */
    public function downloadExport($filename)
    {
        try {
            $filePath = storage_path('app/exports/' . $filename);
            
            if (!file_exists($filePath)) {
                abort(404, 'Export file not found');
            }
            
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Failed to download export: ' . $e->getMessage());
            abort(500, 'Failed to download export file');
        }
    }
}

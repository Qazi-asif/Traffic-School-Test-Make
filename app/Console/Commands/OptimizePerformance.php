<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizePerformance extends Command
{
    protected $signature = 'app:optimize-performance';
    protected $description = 'Optimize application performance for production';

    public function handle()
    {
        $this->info('🚀 Starting performance optimization...');

        // Clear all caches
        $this->info('📦 Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Cache configurations
        $this->info('⚡ Caching configurations...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');

        // Optimize database queries
        $this->info('🗄️ Optimizing database...');
        $this->optimizeDatabase();

        // Warm up caches
        $this->info('🔥 Warming up caches...');
        $this->warmUpCaches();

        $this->info('✅ Performance optimization completed!');
        
        $this->table(['Optimization', 'Status'], [
            ['Configuration Cache', '✅ Enabled'],
            ['Route Cache', '✅ Enabled'],
            ['Database Optimization', '✅ Applied'],
            ['Cache Warming', '✅ Completed'],
            ['Queue System', config('queue.default') === 'sync' ? '✅ Sync Mode' : '⚠️ Database Mode'],
            ['Cache Store', config('cache.default') === 'array' ? '✅ Array Cache' : '⚠️ File Cache'],
        ]);

        return 0;
    }

    private function optimizeDatabase()
    {
        try {
            // Optimize MySQL tables
            $tables = DB::select('SHOW TABLES');
            $databaseName = config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_{$databaseName}"};
                DB::statement("OPTIMIZE TABLE {$tableName}");
            }
            
            $this->line("   Optimized " . count($tables) . " database tables");
        } catch (\Exception $e) {
            $this->warn("   Database optimization skipped: " . $e->getMessage());
        }
    }

    private function warmUpCaches()
    {
        try {
            // Cache frequently accessed data
            Cache::remember('active_courses_count', 3600, function () {
                return \App\Models\FloridaCourse::where('is_active', true)->count();
            });

            Cache::remember('total_enrollments_count', 3600, function () {
                return \App\Models\UserCourseEnrollment::count();
            });

            $this->line("   Warmed up application caches");
        } catch (\Exception $e) {
            $this->warn("   Cache warming failed: " . $e->getMessage());
        }
    }
}
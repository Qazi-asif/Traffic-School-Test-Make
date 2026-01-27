<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HiddenAdminController extends Controller
{
    private $modules = [
        'user_registration' => 'User Registration',
        'course_enrollment' => 'Course Enrollment',
        'payment_processing' => 'Payment Processing',
        'certificate_generation' => 'Certificate Generation',
        'state_transmissions' => 'State Transmissions',
        'admin_panel' => 'Admin Panel',
        'announcements' => 'Announcements',
        'course_content' => 'Course Content Management',
        'student_feedback' => 'Student Feedback',
        'final_exams' => 'Final Exams',
        'reports' => 'Reports & Analytics',
        'email_system' => 'Email System',
        'support_tickets' => 'Support Tickets',
        'booklet_orders' => 'Booklet Orders',
    ];

    public function index(Request $request)
    {
        // Verify secret token
        if ($request->get('token') !== config('app.hidden_admin_token')) {
            abort(404);
        }

        // Get current module statuses
        $moduleStatuses = [];
        foreach ($this->modules as $key => $name) {
            $moduleStatuses[$key] = [
                'name' => $name,
                'enabled' => $this->isModuleEnabled($key)
            ];
        }

        // Get system info
        $systemInfo = [
            'total_users' => \App\Models\User::count(),
            'active_enrollments' => \App\Models\UserCourseEnrollment::where('status', 'active')->count(),
            'total_revenue' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
            'last_activity' => \App\Models\User::latest('updated_at')->first()?->updated_at,
            'license_expires' => $this->getLicenseExpiry(),
        ];

        return view('hidden-admin.index', compact('moduleStatuses', 'systemInfo'));
    }

    public function toggleModule(Request $request)
    {
        // Verify secret token
        if ($request->get('token') !== config('app.hidden_admin_token')) {
            abort(404);
        }

        $request->validate([
            'module' => 'required|string',
            'enabled' => 'required|boolean'
        ]);

        $module = $request->input('module');
        $enabled = $request->input('enabled');

        // Don't allow disabling the hidden admin itself
        if ($module === 'hidden_admin') {
            return response()->json(['error' => 'Cannot disable hidden admin'], 400);
        }

        try {
            // Update module status in database
            DB::table('system_modules')->updateOrInsert(
                ['module_name' => $module],
                [
                    'enabled' => $enabled,
                    'updated_at' => now(),
                    'updated_by' => 'hidden_admin'
                ]
            );

            // Clear cache
            Cache::forget("module_enabled_{$module}");

            // Log the change
            \Log::info("Module {$module} " . ($enabled ? 'enabled' : 'disabled') . " via hidden admin");

            return response()->json([
                'success' => true,
                'message' => "Module {$module} " . ($enabled ? 'enabled' : 'disabled')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database tables not created yet. Please run the SQL script first.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function setLicenseExpiry(Request $request)
    {
        // Verify secret token
        if ($request->get('token') !== config('app.hidden_admin_token')) {
            abort(404);
        }

        $request->validate([
            'expires_at' => 'required|date|after:today'
        ]);

        // Update license expiry in database
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'license_expires_at'],
            [
                'value' => $request->input('expires_at'),
                'updated_at' => now()
            ]
        );

        Cache::forget('license_expires_at');

        return response()->json([
            'success' => true,
            'message' => 'License expiry updated successfully'
        ]);
    }

    public function emergencyDisable(Request $request)
    {
        // Verify secret token
        if ($request->get('token') !== config('app.hidden_admin_token')) {
            abort(404);
        }

        // Disable all modules except hidden admin
        foreach ($this->modules as $module => $name) {
            if ($module !== 'hidden_admin') {
                DB::table('system_modules')->updateOrInsert(
                    ['module_name' => $module],
                    [
                        'enabled' => false,
                        'updated_at' => now(),
                        'updated_by' => 'emergency_disable'
                    ]
                );
                Cache::forget("module_enabled_{$module}");
            }
        }

        \Log::critical("Emergency disable activated via hidden admin");

        return response()->json([
            'success' => true,
            'message' => 'All modules disabled - Emergency mode activated'
        ]);
    }

    public function systemInfo(Request $request)
    {
        // Verify secret token
        if ($request->get('token') !== config('app.hidden_admin_token')) {
            abort(404);
        }

        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => DB::connection()->getPdo() ? 'Connected' : 'Disconnected',
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'disk_space' => disk_free_space('.'),
            'memory_usage' => memory_get_usage(true),
            'uptime' => $this->getSystemUptime(),
        ]);
    }

    private function isModuleEnabled($module)
    {
        return Cache::remember("module_enabled_{$module}", 3600, function () use ($module) {
            try {
                $setting = DB::table('system_modules')
                            ->where('module_name', $module)
                            ->first();
                
                return $setting ? (bool) $setting->enabled : true; // Default to enabled
            } catch (\Exception $e) {
                // If table doesn't exist, return true (enabled) by default
                \Log::warning("System modules table not found: " . $e->getMessage());
                return true;
            }
        });
    }

    private function getSystemUptime()
    {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        return 'N/A';
    }

    public function clearCache(Request $request)
    {
        // Verify secret token
        if ($request->get('token') !== config('app.hidden_admin_token')) {
            abort(404);
        }

        try {
            // Clear module cache
            $modules = array_keys($this->modules);
            foreach ($modules as $module) {
                Cache::forget("module_enabled_{$module}");
            }
            
            // Clear license cache
            Cache::forget('license_expires_at');
            
            // Clear application cache
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getLicenseExpiry()
    {
        try {
            $setting = DB::table('system_settings')
                        ->where('key', 'license_expires_at')
                        ->first();
            
            return $setting ? $setting->value : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
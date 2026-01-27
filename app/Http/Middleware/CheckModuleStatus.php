<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckModuleStatus
{
    /**
     * Module to route mapping
     */
    private $moduleRoutes = [
        'user_registration' => [
            'register*', 'signup*', 'auth/register*'
        ],
        'course_enrollment' => [
            'enroll*', 'course*', 'enrollment*', 'web/enrollments*'
        ],
        'payment_processing' => [
            'payment*', 'checkout*', 'stripe*', 'paypal*'
        ],
        'certificate_generation' => [
            'certificate*', 'cert*', 'download-certificate*'
        ],
        'state_transmissions' => [
            'transmission*', 'state-api*', 'florida*', 'dicds*'
        ],
        'admin_panel' => [
            'admin*', 'dashboard*'
        ],
        'announcements' => [
            'announcement*', 'news*'
        ],
        'course_content' => [
            'course-player*', 'chapter*', 'content*'
        ],
        'student_feedback' => [
            'feedback*', 'review*', 'rating*'
        ],
        'final_exams' => [
            'final-exam*', 'exam*', 'test*'
        ],
        'reports' => [
            'report*', 'analytics*', 'stats*'
        ],
        'email_system' => [
            'email*', 'mail*', 'notification*'
        ],
        'support_tickets' => [
            'support*', 'ticket*', 'help*'
        ],
        'booklet_orders' => [
            'booklet*', 'order*'
        ],
    ];

    public function handle(Request $request, Closure $next)
    {
        // Debug logging
        \Log::info('CheckModuleStatus middleware triggered', [
            'path' => $request->path(),
            'url' => $request->url()
        ]);

        // Skip check for system control panel and maintenance routes
        $path = $request->path();
        if (
            str_contains($path, 'system-control-panel') ||
            str_contains($path, 'maintenance') ||
            str_contains($path, 'api/csrf-token') ||
            $path === '/' ||
            $path === 'login' ||
            $path === 'logout'
        ) {
            \Log::info('Skipping module check for path: ' . $path);
            return $next($request);
        }

        // Check each module
        foreach ($this->moduleRoutes as $module => $routes) {
            foreach ($routes as $routePattern) {
                if ($this->matchesPattern($path, $routePattern)) {
                    \Log::info('Path matches pattern', [
                        'path' => $path,
                        'pattern' => $routePattern,
                        'module' => $module
                    ]);
                    
                    $isEnabled = $this->isModuleEnabled($module);
                    \Log::info('Module status check', [
                        'module' => $module,
                        'enabled' => $isEnabled
                    ]);
                    
                    if (!$isEnabled) {
                        \Log::warning('Blocking access - module disabled', [
                            'module' => $module,
                            'path' => $path
                        ]);
                        return $this->getDisabledResponse($module, $request);
                    }
                }
            }
        }

        \Log::info('No module restrictions found, allowing access');
        return $next($request);
    }

    private function matchesPattern($path, $pattern)
    {
        // Convert pattern to regex
        $regex = str_replace('*', '.*', $pattern);
        return preg_match('/^' . $regex . '/', $path);
    }

    private function isModuleEnabled($module)
    {
        // Skip cache for debugging
        try {
            $setting = DB::table('system_modules')
                        ->where('module_name', $module)
                        ->first();
            
            $enabled = $setting ? (bool) $setting->enabled : true; // Default to enabled
            
            \Log::info('Module status from database', [
                'module' => $module,
                'setting_found' => $setting ? 'yes' : 'no',
                'enabled' => $enabled
            ]);
            
            return $enabled;
        } catch (\Exception $e) {
            \Log::error('Error checking module status', [
                'module' => $module,
                'error' => $e->getMessage()
            ]);
            // If table doesn't exist, return true (enabled) by default
            return true;
        }
    }

    private function getDisabledResponse($module, Request $request)
    {
        $moduleName = ucwords(str_replace('_', ' ', $module));
        
        if ($request->expectsJson()) {
            return response()->json([
                'error' => "The {$moduleName} module is currently disabled.",
                'module' => $module,
                'status' => 'disabled'
            ], 503);
        }

        return response()->view('errors.module-disabled', [
            'module' => $module,
            'moduleName' => $moduleName
        ], 503);
    }
}
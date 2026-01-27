<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleAccessMiddleware
{
    private $routeModuleMap = [
        // User Registration & Authentication
        'register' => 'user_registration',
        'register/*' => 'user_registration',
        'login' => 'user_registration',
        'login/*' => 'user_registration',
        
        // Course Enrollment
        'courses' => 'course_enrollment',
        'courses/*' => 'course_enrollment',
        'course-player' => 'course_enrollment',
        'course-player/*' => 'course_enrollment',
        'enroll' => 'course_enrollment',
        'enroll/*' => 'course_enrollment',
        'my-enrollments' => 'course_enrollment',
        
        // Payment Processing
        'payment' => 'payment_processing',
        'payment/*' => 'payment_processing',
        'checkout' => 'payment_processing',
        'checkout/*' => 'payment_processing',
        'my-payments' => 'payment_processing',
        
        // Certificate Generation
        'certificates' => 'certificate_generation',
        'certificates/*' => 'certificate_generation',
        'certificate' => 'certificate_generation',
        'certificate/*' => 'certificate_generation',
        'my-certificates' => 'certificate_generation',
        
        // Admin Panel (ACTUAL ROUTES)
        'dashboard' => 'admin_panel',
        'admin/dashboard' => 'admin_panel',
        'admin/*' => 'admin_panel',
        'web/admin/*' => 'admin_panel',
        
        // Announcements
        'announcements' => 'announcements',
        'announcements/*' => 'announcements',
        
        // Course Content Management
        'admin/course-content' => 'course_content',
        'admin/course-content/*' => 'course_content',
        
        // Student Feedback
        'admin/student-feedback' => 'student_feedback',
        'admin/student-feedback/*' => 'student_feedback',
        
        // Final Exams
        'final-exam' => 'final_exams',
        'final-exam/*' => 'final_exams',
        'admin/final-exam-attempts' => 'final_exams',
        'admin/final-exam-attempts/*' => 'final_exams',
        
        // Reports
        'admin/reports' => 'reports',
        'admin/reports/*' => 'reports',
        'web/admin/reports' => 'reports',
        'web/admin/reports/*' => 'reports',
        
        // Support Tickets
        'support' => 'support_tickets',
        'support/*' => 'support_tickets',
        'open-ticket' => 'support_tickets',
        'admin/support-tickets' => 'support_tickets',
        'admin/support-tickets/*' => 'support_tickets',
        
        // Booklet Orders
        'booklets' => 'booklet_orders',
        'booklets/*' => 'booklet_orders',
        'admin/booklet-orders' => 'booklet_orders',
        'admin/booklet-orders/*' => 'booklet_orders',
        
        // API Routes
        'api/transmissions' => 'state_transmissions',
        'api/transmissions/*' => 'state_transmissions',
        'api/email' => 'email_system',
        'api/email/*' => 'email_system',
        'api/admin/*' => 'admin_panel',
        'web/admin/*' => 'admin_panel',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Skip check for hidden admin routes
        if ($request->is('system-control-panel*')) {
            return $next($request);
        }

        // Get current path
        $currentPath = $request->path();
        $currentRoute = $request->route() ? $request->route()->getName() : null;
        
        // Check each route pattern
        foreach ($this->routeModuleMap as $pattern => $module) {
            if ($this->matchesPattern($currentPath, $pattern) || 
                ($currentRoute && str_contains($currentRoute, str_replace(['*', '/'], ['', '.'], $pattern)))) {
                
                if (!$this->isModuleEnabled($module)) {
                    Log::info("Module '{$module}' is disabled, blocking access to: {$currentPath}");
                    return $this->moduleDisabledResponse($module);
                }
            }
        }

        // Check license expiry
        if ($this->isLicenseExpired()) {
            Log::info("License expired, blocking access to: {$currentPath}");
            return $this->licenseExpiredResponse();
        }

        return $next($request);
    }

    private function matchesPattern($path, $pattern)
    {
        // Convert pattern to regex
        $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
        $regex = '/^' . $regex . '$/';
        
        return preg_match($regex, $path);
    }

    private function isModuleEnabled($module)
    {
        return Cache::remember("module_enabled_{$module}", 60, function () use ($module) { // Reduced cache time for testing
            try {
                $setting = DB::table('system_modules')
                            ->where('module_name', $module)
                            ->first();
                
                $enabled = $setting ? (bool) $setting->enabled : true;
                Log::debug("Module '{$module}' status: " . ($enabled ? 'enabled' : 'disabled'));
                return $enabled;
            } catch (\Exception $e) {
                Log::warning("Error checking module status for '{$module}': " . $e->getMessage());
                return true; // Default to enabled if error
            }
        });
    }

    private function isLicenseExpired()
    {
        $expiryDate = Cache::remember('license_expires_at', 3600, function () {
            try {
                $setting = DB::table('system_settings')
                            ->where('key', 'license_expires_at')
                            ->first();
                
                return $setting ? $setting->value : null;
            } catch (\Exception $e) {
                return null;
            }
        });

        if (!$expiryDate) {
            return false;
        }

        return now()->gt($expiryDate);
    }

    private function moduleDisabledResponse($module)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Service temporarily unavailable',
                'message' => 'This feature is currently under maintenance.',
                'code' => 'MODULE_DISABLED',
                'module' => $module
            ], 503);
        }

        return response()->view('errors.module-disabled', [
            'module' => $module,
            'title' => 'Service Temporarily Unavailable'
        ], 503);
    }

    private function licenseExpiredResponse()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'License expired',
                'message' => 'Please contact support to renew your license.',
                'code' => 'LICENSE_EXPIRED'
            ], 403);
        }

        return response()->view('errors.license-expired', [
            'title' => 'License Expired'
        ], 403);
    }
}
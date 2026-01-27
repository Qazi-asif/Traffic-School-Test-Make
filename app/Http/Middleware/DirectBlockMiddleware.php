<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DirectBlockMiddleware
{
    private $routeModuleMap = [
        // Test routes
        'test-admin-panel' => 'admin_panel',
        
        // Main Dashboard
        'dashboard' => 'admin_panel',
        
        // Admin Routes
        'admin/dashboard' => 'admin_panel',
        'admin/enrollments' => 'admin_panel',
        'admin/users' => 'admin_panel',
        'admin/certificates' => 'admin_panel',
        'admin/reports' => 'admin_panel',
        'admin/payments' => 'admin_panel',
        'admin/*' => 'admin_panel',
        
        // Course Enrollment
        'courses' => 'course_enrollment',
        'courses/*' => 'course_enrollment',
        'course-player' => 'course_enrollment',
        'course-player/*' => 'course_enrollment',
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
        
        // Final Exams
        'final-exam' => 'final_exams',
        'final-exam/*' => 'final_exams',
        'admin/final-exam-attempts' => 'final_exams',
        
        // Support Tickets
        'support' => 'support_tickets',
        'support/*' => 'support_tickets',
        'open-ticket' => 'support_tickets',
        
        // API Routes
        'api/admin/*' => 'admin_panel',
        'web/admin/*' => 'admin_panel',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Force log to see if middleware is running
        error_log("🔥 DirectBlockMiddleware is running for: " . $request->path());
        
        // Skip check for hidden admin routes
        if ($request->is('system-control-panel*')) {
            return $next($request);
        }

        // Log middleware activity
        Log::debug("🔍 Module middleware checking path: " . $request->path());
        
        // Get current path
        $currentPath = $request->path();
        
        // Check each route pattern
        foreach ($this->routeModuleMap as $pattern => $module) {
            if ($this->matchesPattern($currentPath, $pattern)) {
                if (!$this->isModuleEnabled($module)) {
                    Log::info("🚫 Module '{$module}' is disabled, blocking access to: {$currentPath}");
                    return $this->moduleDisabledResponse($module);
                }
                break; // Found matching pattern, no need to check others
            }
        }

        // Check license expiry
        if ($this->isLicenseExpired()) {
            Log::info("⏰ License expired, blocking access to: {$currentPath}");
            return $this->licenseExpiredResponse();
        }
        
        return $next($request);
    }

    private function matchesPattern($path, $pattern)
    {
        // Exact match
        if ($path === $pattern) {
            return true;
        }
        
        // Wildcard match
        if (str_ends_with($pattern, '/*')) {
            $prefix = substr($pattern, 0, -2);
            return str_starts_with($path, $prefix . '/') || $path === $prefix;
        }
        
        return false;
    }

    private function isModuleEnabled($module)
    {
        return Cache::remember("module_enabled_{$module}", 60, function () use ($module) {
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

        // Generate complete gibberish/unreadable content
        $gibberish = base64_encode(random_bytes(3000)) . 
                    str_repeat('XXXX####@@@@%%%%', 800) . 
                    '<!@#$%^&*()_+{}|:"<>?[]\\;\',./' . 
                    bin2hex(random_bytes(2000)) . 
                    'ERROR_SYSTEM_FAILURE_0xDEADBEEF_CRITICAL_MODULE_CORRUPTION_DETECTED' .
                    str_repeat('####@@@@%%%%XXXX', 500) .
                    base64_encode(random_bytes(1500)) .
                    '!@#$%^&*()_+{}|:<>?[]\\;\',./' .
                    str_repeat('FATAL_ERROR_0xFF_SYSTEM_CORRUPTED', 200) .
                    bin2hex(random_bytes(3000)) .
                    str_repeat('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 100);
        
        return response($gibberish, 503)
            ->header('Content-Type', 'text/plain; charset=utf-8');
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

        // Generate complete gibberish/unreadable content for license expired
        $gibberish = base64_encode(random_bytes(4000)) . 
                    str_repeat('####@@@@%%%%XXXX', 1000) . 
                    'LICENSE_EXPIRED_FATAL_SYSTEM_ERROR_0xDEADBEEF' . 
                    bin2hex(random_bytes(3000)) . 
                    str_repeat('CRITICAL_LICENSE_CORRUPTION_DETECTED', 300) .
                    base64_encode(random_bytes(2000)) .
                    str_repeat('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 200);
        
        return response($gibberish, 403)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
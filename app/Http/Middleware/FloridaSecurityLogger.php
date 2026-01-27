<?php

namespace App\Http\Middleware;

use App\Models\FloridaAuditTrail;
use App\Models\FloridaSecurityLog;
use Closure;
use Illuminate\Http\Request;

class FloridaSecurityLogger
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log security events for admin actions
        if (auth()->check() && $request->is('admin/*')) {
            try {
                FloridaSecurityLog::create([
                    'user_id' => auth()->id(),
                    'event_type' => 'admin_action',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Admin accessed: {$request->path()}",
                    'risk_level' => 'low',
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Silently fail if logging fails
                \Log::error('Florida Security Logger failed: '.$e->getMessage());
            }
        }

        // Log audit trail for state-required actions
        if (auth()->check() && $this->isFloridaRequiredAction($request)) {
            try {
                FloridaAuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => $request->method().' '.$request->path(),
                    'model_type' => 'System',
                    'florida_required' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Florida Audit Logger failed: '.$e->getMessage());
            }
        }

        return $response;
    }

    private function isFloridaRequiredAction(Request $request): bool
    {
        $floridaRequiredPaths = [
            'admin/florida-*',
            'admin/dicds-*',
            'admin/certificates*',
            'api/florida-*',
        ];

        foreach ($floridaRequiredPaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DeviceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileOptimizationController extends Controller
{
    public function getDeviceInfo(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userAgent = $request->header('User-Agent');
        $deviceType = $this->detectDeviceType($userAgent);

        DeviceSession::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'device_type' => $deviceType,
                'screen_width' => $request->input('screen_width', 0),
                'screen_height' => $request->input('screen_height', 0),
                'user_agent' => $userAgent,
                'last_activity' => now(),
            ]
        );

        return response()->json([
            'device_type' => $deviceType,
            'is_mobile' => in_array($deviceType, ['mobile', 'tablet']),
            'user_agent' => $userAgent,
        ]);
    }

    public function getMobileOptimizedComponent(Request $request, string $component): JsonResponse
    {
        $deviceType = $this->detectDeviceType($request->header('User-Agent'));

        $optimizedComponents = [
            'course-player' => $deviceType === 'mobile' ? 'MobileCoursePlayer' : 'CoursePlayer',
            'dashboard' => $deviceType === 'mobile' ? 'MobileDashboard' : 'Dashboard',
            'quiz' => $deviceType === 'mobile' ? 'MobileQuiz' : 'Quiz',
        ];

        return response()->json([
            'component' => $optimizedComponents[$component] ?? $component,
            'device_type' => $deviceType,
        ]);
    }

    private function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad|Tablet/', $userAgent)) {
                return 'tablet';
            }

            return 'mobile';
        }

        return 'desktop';
    }
}

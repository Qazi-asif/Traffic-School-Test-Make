<?php

namespace App\Http\Controllers;

use App\Models\FloridaDeviceSession;
use App\Models\FloridaMobileAnalytics;
use Illuminate\Http\Request;

class FloridaMobileOptimizationController extends Controller
{
    public function getDeviceInfo(Request $request)
    {
        $deviceType = $this->detectDeviceType($request);

        FloridaDeviceSession::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'device_type' => $deviceType,
                'screen_width' => $request->input('screen_width', 1920),
                'screen_height' => $request->input('screen_height', 1080),
                'user_agent' => $request->userAgent(),
                'last_activity' => now(),
            ]
        );

        return response()->json([
            'device_type' => $deviceType,
            'optimizations' => $this->getOptimizations($deviceType),
        ]);
    }

    public function getMobileCourse(Request $request, $courseId)
    {
        $deviceType = $this->detectDeviceType($request);

        // Track mobile course access
        FloridaMobileAnalytics::create([
            'user_id' => auth()->id(),
            'device_type' => $deviceType,
            'course_id' => $courseId,
            'action' => 'course_accessed',
            'mobile_performance_metric' => [
                'load_time' => $request->input('load_time', 0),
                'screen_size' => $request->input('screen_width', 0).'x'.$request->input('screen_height', 0),
            ],
            'created_at' => now(),
        ]);

        return response()->json([
            'course_id' => $courseId,
            'mobile_optimized' => true,
            'device_type' => $deviceType,
        ]);
    }

    public function trackActivity(Request $request)
    {
        FloridaMobileAnalytics::create([
            'user_id' => auth()->id(),
            'device_type' => $this->detectDeviceType($request),
            'course_id' => $request->input('course_id'),
            'action' => $request->input('action'),
            'mobile_performance_metric' => $request->input('metrics', []),
            'created_at' => now(),
        ]);

        return response()->json(['status' => 'tracked']);
    }

    private function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad|Tablet/', $userAgent)) {
                return 'tablet';
            }

            return 'mobile';
        }

        return 'desktop';
    }

    private function getOptimizations(string $deviceType): array
    {
        return match ($deviceType) {
            'mobile' => [
                'touch_targets' => '44px',
                'font_size' => '16px',
                'layout' => 'single_column',
            ],
            'tablet' => [
                'touch_targets' => '40px',
                'font_size' => '14px',
                'layout' => 'two_column',
            ],
            default => [
                'touch_targets' => '32px',
                'font_size' => '14px',
                'layout' => 'multi_column',
            ]
        };
    }
}

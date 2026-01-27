<?php

namespace App\Http\Controllers;

use App\Models\UserAccessibilityPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessibilityController extends Controller
{
    public function getPreferences(): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $preferences = UserAccessibilityPreference::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'font_size' => 'medium',
                'high_contrast_mode' => false,
                'reduced_animations' => false,
                'screen_reader_optimized' => false,
                'keyboard_navigation' => true,
            ]
        );

        return response()->json($preferences);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'font_size' => 'required|in:small,medium,large,xlarge',
            'high_contrast_mode' => 'boolean',
            'reduced_animations' => 'boolean',
            'screen_reader_optimized' => 'boolean',
            'keyboard_navigation' => 'boolean',
        ]);

        $preferences = UserAccessibilityPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            $request->only([
                'font_size',
                'high_contrast_mode',
                'reduced_animations',
                'screen_reader_optimized',
                'keyboard_navigation',
            ])
        );

        return response()->json($preferences);
    }

    public function resetPreferences(): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $preferences = UserAccessibilityPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'font_size' => 'medium',
                'high_contrast_mode' => false,
                'reduced_animations' => false,
                'screen_reader_optimized' => false,
                'keyboard_navigation' => true,
            ]
        );

        return response()->json($preferences);
    }
}

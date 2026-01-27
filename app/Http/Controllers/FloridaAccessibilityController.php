<?php

namespace App\Http\Controllers;

use App\Models\FloridaUserAccessibility;
use Illuminate\Http\Request;

class FloridaAccessibilityController extends Controller
{
    public function getPreferences(Request $request)
    {
        $preferences = FloridaUserAccessibility::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'font_size' => 'medium',
                'high_contrast_mode' => false,
                'reduced_animations' => false,
                'screen_reader_optimized' => false,
                'keyboard_navigation' => true,
                'mobile_optimized' => true,
            ]
        );

        return response()->json($preferences);
    }

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'font_size' => 'required|in:small,medium,large,xlarge',
            'high_contrast_mode' => 'boolean',
            'reduced_animations' => 'boolean',
            'screen_reader_optimized' => 'boolean',
            'keyboard_navigation' => 'boolean',
            'mobile_optimized' => 'boolean',
        ]);

        $preferences = FloridaUserAccessibility::updateOrCreate(
            ['user_id' => auth()->id()],
            $request->only([
                'font_size',
                'high_contrast_mode',
                'reduced_animations',
                'screen_reader_optimized',
                'keyboard_navigation',
                'mobile_optimized',
            ])
        );

        return response()->json($preferences);
    }

    public function resetPreferences()
    {
        FloridaUserAccessibility::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'font_size' => 'medium',
                'high_contrast_mode' => false,
                'reduced_animations' => false,
                'screen_reader_optimized' => false,
                'keyboard_navigation' => true,
                'mobile_optimized' => true,
            ]
        );

        return response()->json(['message' => 'Preferences reset to defaults']);
    }
}

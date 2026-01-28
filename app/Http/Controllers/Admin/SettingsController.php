<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemSetting::with(['updater'])
            ->orderBy('group')
            ->orderBy('key');

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('key', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $settings = $query->paginate(20);
        $groups = SystemSetting::distinct('group')->pluck('group')->sort();

        return view('admin.settings.index', compact('settings', 'groups'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'required|in:string,integer,boolean,array,json',
            'settings.*.group' => 'required|string',
            'settings.*.description' => 'nullable|string',
            'settings.*.is_public' => 'boolean',
        ]);

        foreach ($request->settings as $settingData) {
            $value = $settingData['value'];

            // Convert value based on type
            switch ($settingData['type']) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'array':
                case 'json':
                    if (is_string($value)) {
                        $value = json_decode($value, true) ?? [];
                    }
                    break;
            }

            SystemSetting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'value' => $value,
                    'type' => $settingData['type'],
                    'group' => $settingData['group'],
                    'description' => $settingData['description'],
                    'is_public' => $settingData['is_public'] ?? false,
                    'updated_by' => Auth::guard('admin')->id(),
                ]
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function export(Request $request)
    {
        $query = SystemSetting::query();

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        $settings = $query->get();

        $filename = 'system_settings_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        $exportData = $settings->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ];
        });

        return response()->json($exportData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json',
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getPathname());
            $settings = json_decode($content, true);

            if (!is_array($settings)) {
                throw new \Exception('Invalid JSON format');
            }

            $importedCount = 0;

            foreach ($settings as $settingData) {
                if (!isset($settingData['key']) || !isset($settingData['type'])) {
                    continue;
                }

                SystemSetting::updateOrCreate(
                    ['key' => $settingData['key']],
                    [
                        'value' => $settingData['value'] ?? null,
                        'type' => $settingData['type'],
                        'group' => $settingData['group'] ?? 'general',
                        'description' => $settingData['description'] ?? null,
                        'is_public' => $settingData['is_public'] ?? false,
                        'updated_by' => Auth::guard('admin')->id(),
                    ]
                );

                $importedCount++;
            }

            return redirect()->route('admin.settings.index')
                ->with('success', "{$importedCount} settings imported successfully.");

        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }
}
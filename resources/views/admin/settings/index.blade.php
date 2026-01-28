@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
                <p class="mt-1 text-sm text-gray-600">Configure system-wide settings and preferences</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.settings.export') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md">
                    Export Settings
                </a>
                <button type="button" onclick="document.getElementById('import-form').style.display='block'"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                    Import Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Import Form (Hidden by default) -->
    <div id="import-form" class="bg-white shadow rounded-lg" style="display: none;">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Import Settings</h3>
            <form method="POST" action="{{ route('admin.settings.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center space-x-4">
                    <input type="file" name="settings_file" accept=".json" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md">
                        Import
                    </button>
                    <button type="button" onclick="document.getElementById('import-form').style.display='none'"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-md">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings Form -->
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        
        @php
            $settingGroups = [
                'general' => 'General Settings',
                'email' => 'Email Settings', 
                'files' => 'File Upload Settings',
                'courses' => 'Course Settings',
                'payments' => 'Payment Settings',
                'states' => 'State-Specific Settings',
                'security' => 'Security Settings',
                'notifications' => 'Notification Settings',
                'analytics' => 'Analytics Settings'
            ];
        @endphp

        @foreach($settingGroups as $group => $groupName)
            @php
                $groupSettings = $settings->where('group', $group);
            @endphp
            
            @if($groupSettings->count() > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $groupName }}</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($groupSettings as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700">
                                {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                            </label>
                            <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                            
                            @if($setting->type === 'boolean')
                                <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="1" {{ $setting->value ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !$setting->value ? 'selected' : '' }}>No</option>
                                </select>
                            @elseif($setting->type === 'integer')
                                <input type="number" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @elseif($setting->type === 'decimal')
                                <input type="number" step="0.01" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @elseif(in_array($setting->key, ['allowed_file_types']))
                                <textarea name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Comma separated values">{{ $setting->value }}</textarea>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        <!-- Save Button -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="location.reload()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-md">
                        Reset
                    </button>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- System Information -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">System Information</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Laravel Version</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ app()->version() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">PHP Version</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ PHP_VERSION }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Environment</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ app()->environment() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Debug Mode</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('app.timezone') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Database</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('database.default') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Cache Driver</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('cache.default') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Queue Driver</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ config('queue.default') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
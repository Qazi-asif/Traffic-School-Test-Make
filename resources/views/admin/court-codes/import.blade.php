@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Import Court Codes</h1>
            <a href="{{ route('admin.court-codes.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to List
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">CSV Format Requirements</h2>
            <div class="bg-gray-50 p-4 rounded mb-4">
                <p class="text-sm text-gray-700 mb-2">Your CSV file must include the following columns:</p>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                    <li><strong>court_name</strong> - Must match existing court name exactly</li>
                    <li><strong>code_type</strong> - One of: tvcc, court_id, location_code, branch_code, state_code</li>
                    <li><strong>code_value</strong> - The actual code value</li>
                    <li><strong>code_name</strong> - Human-readable name (optional)</li>
                    <li><strong>is_active</strong> - true/false or 1/0 (optional, defaults to true)</li>
                    <li><strong>effective_date</strong> - YYYY-MM-DD format (optional)</li>
                    <li><strong>expiration_date</strong> - YYYY-MM-DD format (optional)</li>
                    <li><strong>notes</strong> - Additional notes (optional)</li>
                </ul>
            </div>

            <div class="bg-blue-50 p-4 rounded">
                <p class="text-sm text-blue-800">
                    <strong>Example CSV:</strong><br>
                    <code class="text-xs">court_name,code_type,code_value,code_name,is_active<br>
                    "Miami-Dade County Court",tvcc,FL12345,"Miami TVCC",true</code>
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.court-codes.import') }}" enctype="multipart/form-data" 
            class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">State *</label>
                <select name="state" required class="w-full px-3 py-2 border rounded">
                    <option value="">Select State</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-600 mt-1">Only courts from this state will be matched</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">CSV File *</label>
                <input type="file" name="file" accept=".csv,.txt" required 
                    class="w-full px-3 py-2 border rounded">
                <p class="text-sm text-gray-600 mt-1">Maximum file size: 10MB</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.court-codes.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Import Court Codes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

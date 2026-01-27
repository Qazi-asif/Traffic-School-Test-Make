@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Court Codes for {{ $court->court }}</h1>
            <a href="{{ route('admin.court-codes.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to All Codes
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-2">{{ $court->court }}</h2>
            <p class="text-gray-600">{{ $court->county }}, {{ $court->state }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold mb-4">Add New Code</h3>
            <form method="POST" action="{{ route('admin.court-codes.court.store', $court) }}" class="grid grid-cols-4 gap-4">
                @csrf
                <div>
                    <select name="code_type" required class="w-full px-3 py-2 border rounded">
                        <option value="">Select Type</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="text" name="code_value" required placeholder="Code Value" 
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <input type="text" name="code_name" placeholder="Code Name (optional)" 
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Add Code
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($codes as $code)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">{{ $code->code_value }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded bg-gray-100">{{ strtoupper($code->code_type) }}</span>
                            </td>
                            <td class="px-6 py-4">{{ $code->code_name }}</td>
                            <td class="px-6 py-4">
                                @if($code->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $code->effective_date?->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.court-codes.show', $code) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                <a href="{{ route('admin.court-codes.edit', $code) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No codes configured for this court.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

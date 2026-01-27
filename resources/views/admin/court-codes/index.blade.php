@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Court Codes Management</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.court-codes.import') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Import
            </a>
            <a href="{{ route('admin.court-codes.export', request()->query()) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Export
            </a>
            <a href="{{ route('admin.court-codes.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Add New Code
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" name="q" value="{{ $query }}" placeholder="Search codes or courts..." 
                    class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <select name="state" class="w-full px-3 py-2 border rounded">
                    <option value="">All States</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ $filters['state'] == $state ? 'selected' : '' }}>
                            {{ $state }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="type" class="w-full px-3 py-2 border rounded">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ $filters['type'] == $type ? 'selected' : '' }}>
                            {{ strtoupper($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="is_active" class="w-full px-3 py-2 border rounded">
                    <option value="">All Status</option>
                    <option value="1" {{ $filters['is_active'] === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $filters['is_active'] === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Filter
                </button>
                <a href="{{ route('admin.court-codes.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Clear
                </a>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Court</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">State</th>
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
                        <td class="px-6 py-4">{{ $code->court?->court }}</td>
                        <td class="px-6 py-4">{{ $code->court?->state }}</td>
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
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No court codes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $codes->links() }}
    </div>
</div>
@endsection

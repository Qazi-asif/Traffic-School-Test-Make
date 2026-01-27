@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Unmapped Court Codes</h1>
            <a href="{{ route('admin.court-codes.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to List
            </a>
        </div>

        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            These codes don't have any external system mappings configured.
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($codes as $code)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">{{ $code->code_value }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ strtoupper($code->code_type) }}</td>
                            <td class="px-6 py-4">{{ $code->code_name }}</td>
                            <td class="px-6 py-4">{{ $code->court?->court }}</td>
                            <td class="px-6 py-4">{{ $code->court?->state }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.court-codes.mappings', $code) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Add Mappings
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">All codes have mappings configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

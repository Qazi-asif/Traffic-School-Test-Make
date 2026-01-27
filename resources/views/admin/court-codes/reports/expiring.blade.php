@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Expiring Court Codes</h1>
            <a href="{{ route('admin.court-codes.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to List
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Court</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">State</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiration Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Until</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($codes as $code)
                        @php
                            $daysUntil = now()->diffInDays($code->expiration_date, false);
                            $urgency = $daysUntil <= 7 ? 'red' : ($daysUntil <= 14 ? 'yellow' : 'blue');
                        @endphp
                        <tr class="bg-{{ $urgency }}-50">
                            <td class="px-6 py-4 whitespace-nowrap font-mono">{{ $code->code_value }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ strtoupper($code->code_type) }}</td>
                            <td class="px-6 py-4">{{ $code->court?->court }}</td>
                            <td class="px-6 py-4">{{ $code->court?->state }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $code->expiration_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded bg-{{ $urgency }}-200 text-{{ $urgency }}-800">
                                    {{ $daysUntil }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.court-codes.edit', $code) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Extend
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No expiring codes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

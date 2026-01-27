@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Court Code Details</h1>
            <a href="{{ route('admin.court-codes.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to List
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold font-mono">{{ $code->code_value }}</h2>
                    <p class="text-gray-600">{{ $code->code_name }}</p>
                </div>
                <div class="flex gap-2">
                    @if($code->is_active)
                        <form method="POST" action="{{ route('admin.court-codes.deactivate', $code) }}" class="inline">
                            @csrf
                            <input type="text" name="reason" placeholder="Reason" required class="px-2 py-1 border rounded text-sm">
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                Deactivate
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.court-codes.reactivate', $code) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                Reactivate
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.court-codes.edit', $code) }}" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                        Edit
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="font-bold text-gray-700 mb-2">Code Information</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm text-gray-600">Type</dt>
                            <dd class="font-medium">{{ strtoupper($code->code_type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">Status</dt>
                            <dd>
                                @if($code->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">Effective Date</dt>
                            <dd class="font-medium">{{ $code->effective_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">Expiration Date</dt>
                            <dd class="font-medium">{{ $code->expiration_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="font-bold text-gray-700 mb-2">Court Information</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm text-gray-600">Court Name</dt>
                            <dd class="font-medium">{{ $code->court?->court }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">County</dt>
                            <dd class="font-medium">{{ $code->court?->county }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">State</dt>
                            <dd class="font-medium">{{ $code->court?->state }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">Created By</dt>
                            <dd class="font-medium">{{ $code->createdBy?->name ?? 'System' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($code->notes)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-bold text-gray-700 mb-2">Notes</h3>
                    <p class="text-gray-700">{{ $code->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">External System Mappings</h3>
                <a href="{{ route('admin.court-codes.mappings', $code) }}" class="text-indigo-600 hover:text-indigo-900">
                    Manage Mappings
                </a>
            </div>

            @if($code->mappings->isEmpty())
                <p class="text-gray-500">No external mappings configured.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">System</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">External Code</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">External Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($code->mappings as $mapping)
                            <tr>
                                <td class="px-4 py-2">{{ strtoupper($mapping->external_system) }}</td>
                                <td class="px-4 py-2 font-mono">{{ $mapping->external_code }}</td>
                                <td class="px-4 py-2">{{ $mapping->external_name }}</td>
                                <td class="px-4 py-2">
                                    @if($mapping->is_verified)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Verified</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Unverified</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Change History</h3>
            @if($code->history->isEmpty())
                <p class="text-gray-500">No history available.</p>
            @else
                <div class="space-y-4">
                    @foreach($code->history->take(10) as $entry)
                        <div class="border-l-4 border-gray-300 pl-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-bold">{{ ucfirst($entry->action) }}</span>
                                    <span class="text-gray-600">by {{ $entry->changedBy?->name ?? 'System' }}</span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $entry->created_at->diffForHumans() }}</span>
                            </div>
                            @if($entry->reason)
                                <p class="text-sm text-gray-600 mt-1">Reason: {{ $entry->reason }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($code->history->count() > 10)
                    <a href="{{ route('admin.court-codes.history', $code) }}" class="text-indigo-600 hover:text-indigo-900 mt-4 inline-block">
                        View Full History
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

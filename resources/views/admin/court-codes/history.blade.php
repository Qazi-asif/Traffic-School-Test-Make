@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Change History</h1>
            <a href="{{ route('admin.court-codes.show', $code) }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Code
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-2">{{ $code->code_value }}</h2>
            <p class="text-gray-600">{{ $code->code_name }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="space-y-6">
                @forelse($history as $entry)
                    <div class="border-l-4 {{ $entry->action === 'created' ? 'border-green-500' : ($entry->action === 'deactivated' ? 'border-red-500' : 'border-blue-500') }} pl-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-bold text-lg">{{ ucfirst($entry->action) }}</span>
                                <span class="text-gray-600">by {{ $entry->changedBy?->name ?? 'System' }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $entry->created_at->format('M d, Y g:i A') }}</span>
                        </div>

                        @if($entry->reason)
                            <div class="bg-yellow-50 p-3 rounded mb-2">
                                <p class="text-sm"><strong>Reason:</strong> {{ $entry->reason }}</p>
                            </div>
                        @endif

                        @if($entry->old_values || $entry->new_values)
                            <div class="grid grid-cols-2 gap-4 mt-3">
                                @if($entry->old_values)
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-700 mb-1">Old Values</h4>
                                        <div class="bg-red-50 p-2 rounded text-sm">
                                            @foreach($entry->old_values as $key => $value)
                                                <div><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($entry->new_values)
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-700 mb-1">New Values</h4>
                                        <div class="bg-green-50 p-2 rounded text-sm">
                                            @foreach($entry->new_values as $key => $value)
                                                <div><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500">No history available.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $history->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

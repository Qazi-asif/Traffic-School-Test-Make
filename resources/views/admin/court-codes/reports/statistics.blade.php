@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Court Code Statistics</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Total Codes</h3>
                <p class="text-4xl font-bold">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Active Codes</h3>
                <p class="text-4xl font-bold text-green-600">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-600 text-sm font-bold mb-2">Inactive Codes</h3>
                <p class="text-4xl font-bold text-red-600">{{ $stats['inactive'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Codes by Type</h3>
                <div class="space-y-2">
                    @foreach($stats['by_type'] as $type => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">{{ strtoupper($type) }}</span>
                            <span class="font-bold">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Codes by State</h3>
                <div class="space-y-2">
                    @foreach($stats['by_state'] as $state => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">{{ $state }}</span>
                            <span class="font-bold">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

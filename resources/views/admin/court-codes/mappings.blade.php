@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">External System Mappings</h1>
            <a href="{{ route('admin.court-codes.show', $code) }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Code
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-2">{{ $code->code_value }}</h2>
            <p class="text-gray-600">{{ $code->code_name }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" style="background-color: #f4f6f0; border-color: #516425; color: #516425;">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold mb-4">Add New Mapping</h3>
            <form method="POST" action="{{ route('admin.court-codes.mappings.store', $code) }}" class="grid grid-cols-4 gap-4">
                @csrf
                <div>
                    <select name="external_system" required class="w-full px-3 py-2 border rounded">
                        <option value="">Select System</option>
                        @foreach($systems as $system)
                            <option value="{{ $system }}">{{ strtoupper($system) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="text" name="external_code" required placeholder="External Code" 
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <input type="text" name="external_name" placeholder="External Name (optional)" 
                        class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Add Mapping
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">System</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">External Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">External Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($code->mappings as $mapping)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ strtoupper($mapping->external_system) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">{{ $mapping->external_code }}</td>
                            <td class="px-6 py-4">{{ $mapping->external_name }}</td>
                            <td class="px-6 py-4">
                                @if($mapping->is_verified)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800" style="background-color: #f4f6f0; color: #516425;">Verified</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Unverified</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($mapping->is_verified)
                                    {{ $mapping->verified_at->format('M d, Y') }}<br>
                                    <span class="text-gray-500">by {{ $mapping->verifiedBy?->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if(!$mapping->is_verified)
                                    <form method="POST" action="{{ route('admin.court-codes.mappings.verify', $mapping) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900 mr-3" style="color: #516425;" onmouseover="this.style.color='#3d4b1c'" onmouseout="this.style.color='#516425'">Verify</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.court-codes.mappings.destroy', $mapping) }}" class="inline"
                                    onsubmit="return confirm('Remove this mapping?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No mappings configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Create Court Code</h1>
            <a href="{{ route('admin.court-codes.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to List
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.court-codes.store') }}" class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Court *</label>
                <select name="court_id" required class="w-full px-3 py-2 border rounded">
                    <option value="">Select Court</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" {{ old('court_id') == $court->id ? 'selected' : '' }}>
                            {{ $court->state }} - {{ $court->court }} ({{ $court->county }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Code Type *</label>
                    <select name="code_type" required class="w-full px-3 py-2 border rounded">
                        <option value="">Select Type</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ old('code_type') == $type ? 'selected' : '' }}>
                                {{ strtoupper($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Code Value *</label>
                    <input type="text" name="code_value" value="{{ old('code_value') }}" required 
                        class="w-full px-3 py-2 border rounded" placeholder="e.g., FL12345">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Code Name</label>
                <input type="text" name="code_name" value="{{ old('code_name') }}" 
                    class="w-full px-3 py-2 border rounded" placeholder="Human-readable name">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Effective Date</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date') }}" 
                        class="w-full px-3 py-2 border rounded">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Expiration Date</label>
                    <input type="date" name="expiration_date" value="{{ old('expiration_date') }}" 
                        class="w-full px-3 py-2 border rounded">
                </div>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} 
                        class="mr-2">
                    <span class="text-gray-700 font-bold">Active</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded">{{ old('notes') }}</textarea>
            </div>

            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-bold mb-4">External System Mappings (Optional)</h3>
                <div id="mappings-container">
                    <div class="grid grid-cols-3 gap-4 mb-3">
                        <div>
                            <select name="mappings[0][external_system]" class="w-full px-3 py-2 border rounded">
                                <option value="">Select System</option>
                                @foreach($systems as $system)
                                    <option value="{{ $system }}">{{ strtoupper($system) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="text" name="mappings[0][external_code]" placeholder="External Code" 
                                class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <input type="text" name="mappings[0][external_name]" placeholder="External Name" 
                                class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.court-codes.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Create Court Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

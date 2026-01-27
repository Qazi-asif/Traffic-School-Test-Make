@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Edit Court Code</h1>
            <a href="{{ route('admin.court-codes.show', $code) }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Details
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

        <form method="POST" action="{{ route('admin.court-codes.update', $code) }}" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Court *</label>
                <select name="court_id" required class="w-full px-3 py-2 border rounded">
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}" {{ $code->court_id == $court->id ? 'selected' : '' }}>
                            {{ $court->state }} - {{ $court->court }} ({{ $court->county }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Code Type *</label>
                    <select name="code_type" required class="w-full px-3 py-2 border rounded">
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ $code->code_type == $type ? 'selected' : '' }}>
                                {{ strtoupper($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Code Value *</label>
                    <input type="text" name="code_value" value="{{ $code->code_value }}" required 
                        class="w-full px-3 py-2 border rounded">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Code Name</label>
                <input type="text" name="code_name" value="{{ $code->code_name }}" 
                    class="w-full px-3 py-2 border rounded">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Effective Date</label>
                    <input type="date" name="effective_date" value="{{ $code->effective_date?->format('Y-m-d') }}" 
                        class="w-full px-3 py-2 border rounded">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Expiration Date</label>
                    <input type="date" name="expiration_date" value="{{ $code->expiration_date?->format('Y-m-d') }}" 
                        class="w-full px-3 py-2 border rounded">
                </div>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ $code->is_active ? 'checked' : '' }} 
                        class="mr-2">
                    <span class="text-gray-700 font-bold">Active</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded">{{ $code->notes }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Reason for Change</label>
                <input type="text" name="reason" class="w-full px-3 py-2 border rounded" 
                    placeholder="Optional: Explain why you're making this change">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.court-codes.show', $code) }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Update Court Code
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.court-codes.destroy', $code) }}" class="mt-6" 
            onsubmit="return confirm('Are you sure you want to delete this court code?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                Delete Court Code
            </button>
        </form>
    </div>
</div>
@endsection

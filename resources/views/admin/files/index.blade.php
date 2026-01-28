@extends('admin.layouts.app')

@section('title', 'File Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">File Management</h1>
                <p class="mt-1 text-sm text-gray-600">Upload and manage course files, documents, and media</p>
            </div>
            <a href="{{ route('admin.files.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md">
                Upload File
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="File name">
                </div>
                <div>
                    <label for="file_type" class="block text-sm font-medium text-gray-700">File Type</label>
                    <select name="file_type" id="file_type" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Types</option>
                        <option value="video" {{ request('file_type') === 'video' ? 'selected' : '' }}>Video</option>
                        <option value="document" {{ request('file_type') === 'document' ? 'selected' : '' }}>Document</option>
                        <option value="image" {{ request('file_type') === 'image' ? 'selected' : '' }}>Image</option>
                        <option value="audio" {{ request('file_type') === 'audio' ? 'selected' : '' }}>Audio</option>
                        <option value="other" {{ request('file_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                    <select name="state" id="state" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All States</option>
                        @if(auth('admin')->user()->hasStateAccess('florida'))
                        <option value="florida" {{ request('state') === 'florida' ? 'selected' : '' }}>Florida</option>
                        @endif
                        @if(auth('admin')->user()->hasStateAccess('missouri'))
                        <option value="missouri" {{ request('state') === 'missouri' ? 'selected' : '' }}>Missouri</option>
                        @endif
                        @if(auth('admin')->user()->hasStateAccess('texas'))
                        <option value="texas" {{ request('state') === 'texas' ? 'selected' : '' }}>Texas</option>
                        @endif
                        @if(auth('admin')->user()->hasStateAccess('delaware'))
                        <option value="delaware" {{ request('state') === 'delaware' ? 'selected' : '' }}>Delaware</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label for="uploaded_by" class="block text-sm font-medium text-gray-700">Uploaded By</label>
                    <input type="text" name="uploaded_by" id="uploaded_by" value="{{ request('uploaded_by') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Admin name">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Files Grid -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Files ({{ $files->total() ?? 0 }})
            </h3>
            <div class="flex space-x-2">
                <button type="button" id="grid-view" class="p-2 text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button type="button" id="list-view" class="p-2 text-gray-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        
        @if(isset($files) && $files->count() > 0)
        <!-- List View (default) -->
        <div id="list-container">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($files as $file)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($file->isImage())
                                            <img class="h-10 w-10 rounded object-cover" src="{{ $file->getUrl() }}" alt="">
                                        @else
                                            <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $file->original_filename }}</div>
                                        <div class="text-sm text-gray-500">{{ $file->filename }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($file->file_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $file->getFormattedSize() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $file->state ? ucfirst($file->state) : 'General' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $file->uploader->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $file->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.files.show', $file) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <a href="{{ route('admin.files.download', $file) }}" 
                                       class="text-green-600 hover:text-green-900">Download</a>
                                    <a href="{{ route('admin.files.edit', $file) }}" 
                                       class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                    <form method="POST" action="{{ route('admin.files.destroy', $file) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to delete this file?')"
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grid View (hidden by default) -->
        <div id="grid-container" class="hidden p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($files as $file)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        @if($file->isImage())
                            <img src="{{ $file->getUrl() }}" alt="{{ $file->original_filename }}" class="w-full h-32 object-cover">
                        @else
                            <div class="w-full h-32 flex items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $file->original_filename }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $file->getFormattedSize() }} • {{ ucfirst($file->file_type) }}</p>
                        <p class="text-xs text-gray-500">{{ $file->created_at->format('M j, Y') }}</p>
                        <div class="mt-3 flex space-x-2">
                            <a href="{{ route('admin.files.show', $file) }}" 
                               class="text-xs text-indigo-600 hover:text-indigo-900">View</a>
                            <a href="{{ route('admin.files.download', $file) }}" 
                               class="text-xs text-green-600 hover:text-green-900">Download</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $files->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No files found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by uploading your first file.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.files.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Upload File
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('grid-view').addEventListener('click', function() {
    document.getElementById('list-container').classList.add('hidden');
    document.getElementById('grid-container').classList.remove('hidden');
    this.classList.add('text-gray-600');
    this.classList.remove('text-gray-400');
    document.getElementById('list-view').classList.add('text-gray-400');
    document.getElementById('list-view').classList.remove('text-gray-600');
});

document.getElementById('list-view').addEventListener('click', function() {
    document.getElementById('grid-container').classList.add('hidden');
    document.getElementById('list-container').classList.remove('hidden');
    this.classList.add('text-gray-600');
    this.classList.remove('text-gray-400');
    document.getElementById('grid-view').classList.add('text-gray-400');
    document.getElementById('grid-view').classList.remove('text-gray-600');
});
</script>
@endpush
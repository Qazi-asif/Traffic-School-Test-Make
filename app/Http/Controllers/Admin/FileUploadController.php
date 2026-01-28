<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    public function index(Request $request)
    {
        $query = FileUpload::with(['uploader', 'course', 'chapter'])
            ->orderBy('created_at', 'desc');

        // Filter by state if specified
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Filter by file type if specified
        if ($request->filled('type')) {
            $query->where('file_type', $request->type);
        }

        // Search by filename
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('filename', 'like', '%' . $request->search . '%')
                  ->orWhere('original_filename', 'like', '%' . $request->search . '%');
            });
        }

        $files = $query->paginate(20);

        return view('admin.files.index', compact('files'));
    }

    public function create(Request $request)
    {
        $state = $request->get('state');
        $courseId = $request->get('course_id');
        $chapterId = $request->get('chapter_id');

        return view('admin.files.create', compact('state', 'courseId', 'chapterId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:102400', // 100MB max
            'state' => 'required|in:florida,missouri,texas,delaware',
            'file_type' => 'required|in:video,document,image,audio',
            'course_id' => 'nullable|integer',
            'chapter_id' => 'nullable|integer',
        ]);

        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            $uploadedFile = $this->processFileUpload($file, $request);
            $uploadedFiles[] = $uploadedFile;
        }

        if (count($uploadedFiles) === 1) {
            return redirect()->route('admin.files.show', $uploadedFiles[0])
                ->with('success', 'File uploaded successfully.');
        }

        return redirect()->route('admin.files.index')
            ->with('success', count($uploadedFiles) . ' files uploaded successfully.');
    }

    public function show(FileUpload $file)
    {
        $file->load(['uploader', 'course', 'chapter']);
        return view('admin.files.show', compact('file'));
    }

    public function edit(FileUpload $file)
    {
        return view('admin.files.edit', compact('file'));
    }

    public function update(Request $request, FileUpload $file)
    {
        $request->validate([
            'original_filename' => 'required|string|max:255',
            'file_type' => 'required|in:video,document,image,audio',
            'course_id' => 'nullable|integer',
            'chapter_id' => 'nullable|integer',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $file->update([
            'original_filename' => $request->original_filename,
            'file_type' => $request->file_type,
            'course_id' => $request->course_id,
            'chapter_id' => $request->chapter_id,
            'is_active' => $request->boolean('is_active', true),
            'metadata' => $request->metadata ?? [],
        ]);

        return redirect()->route('admin.files.show', $file)
            ->with('success', 'File updated successfully.');
    }

    public function destroy(FileUpload $file)
    {
        $file->delete();

        return redirect()->route('admin.files.index')
            ->with('success', 'File deleted successfully.');
    }

    public function download(FileUpload $file)
    {
        if (!$file->exists()) {
            abort(404, 'File not found on disk.');
        }

        return Storage::download($file->file_path, $file->original_filename);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:file_uploads,id',
        ]);

        $files = FileUpload::whereIn('id', $request->file_ids)->get();
        $deletedCount = 0;

        foreach ($files as $file) {
            $file->delete();
            $deletedCount++;
        }

        return redirect()->route('admin.files.index')
            ->with('success', "{$deletedCount} files deleted successfully.");
    }

    private function processFileUpload($file, Request $request)
    {
        $state = $request->state;
        $fileType = $request->file_type;
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Organize files by state and type
        $directory = "courses/{$state}/{$fileType}s";
        
        // Store the file
        $filePath = $file->storeAs($directory, $filename, 'public');
        
        // Create database record
        return FileUpload::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_type' => $fileType,
            'state' => $state,
            'course_id' => $request->course_id,
            'chapter_id' => $request->chapter_id,
            'uploaded_by' => Auth::guard('admin')->id(),
            'is_active' => true,
            'metadata' => $request->metadata ?? [],
        ]);
    }

    public function apiUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400',
            'state' => 'required|in:florida,missouri,texas,delaware',
            'file_type' => 'required|in:video,document,image,audio',
        ]);

        $uploadedFile = $this->processFileUpload($request->file('file'), $request);

        return response()->json([
            'success' => true,
            'file' => $uploadedFile,
            'url' => $uploadedFile->getUrl(),
        ]);
    }
}
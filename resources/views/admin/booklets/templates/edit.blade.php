<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template: {{ $template->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="mb-4">
            <h2><i class="fas fa-edit"></i> Edit Template: {{ $template->name }}</h2>
            <p class="text-muted">Customize booklet template</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.booklets.templates.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required class="form-control">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" value="{{ ucfirst($template->type) }}" disabled class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Template Content (Blade/HTML) *</label>
                        <textarea name="content" id="content" rows="15" required class="form-control font-monospace">{{ old('content', $template->content) }}</textarea>
                        @error('content')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="css" class="form-label">Custom CSS (Optional)</label>
                        <textarea name="css" id="css" rows="8" class="form-control font-monospace">{{ old('css', $template->css) }}</textarea>
                        @error('css')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="variables" class="form-label">Available Variables (JSON)</label>
                        <textarea name="variables" id="variables" rows="5" class="form-control font-monospace">{{ old('variables', json_encode($template->variables, JSON_PRETTY_PRINT)) }}</textarea>
                        <small class="text-muted">Example: ["course", "student", "title"]</small>
                        @error('variables')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="form-check-input" id="is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.booklets.templates') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Template
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Available Variables for {{ ucfirst($template->type) }} Template:</h5>
                <ul class="list-unstyled">
                    @if($template->type === 'cover')
                        <li><code>$course</code> - Course object</li>
                        <li><code>$title</code> - Course title</li>
                        <li><code>$state</code> - State code</li>
                        <li><code>$student_name</code> - Student name (personalized only)</li>
                    @elseif($template->type === 'toc')
                        <li><code>$course</code> - Course object</li>
                        <li><code>$chapters</code> - Collection of chapters</li>
                    @elseif($template->type === 'chapter')
                        <li><code>$chapter</code> - Chapter object</li>
                        <li><code>$course</code> - Course object</li>
                    @elseif($template->type === 'footer')
                        <li><code>$course</code> - Course object</li>
                        <li><code>$generated_at</code> - Generation date</li>
                        <li><code>$student</code> - Student object (personalized only)</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

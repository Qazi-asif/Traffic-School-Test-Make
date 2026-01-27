<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title }} - Content Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.course-content.index') }}">Course Content</a></li>
                        <li class="breadcrumb-item active">{{ $course->title }}</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-book-open"></i> {{ $course->title }}</h2>
            </div>
            <a href="{{ route('admin.course-content.create-chapter', $course) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Chapter
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary">{{ $course->chapters->count() }}</h3>
                        <p class="mb-0">Total Chapters</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success">{{ $course->total_duration }}</h3>
                        <p class="mb-0">Total Minutes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-info">{{ $course->chapters->where('is_quiz', true)->count() }}</h3>
                        <p class="mb-0">Quiz Chapters</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-warning">{{ $course->min_pass_score }}%</h3>
                        <p class="mb-0">Pass Score</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Course Chapters</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleReorderMode()">
                    <i class="fas fa-sort"></i> Reorder
                </button>
            </div>
            <div class="card-body">
                <div id="chapters-list">
                    @forelse($course->chapters as $chapter)
                        <div class="chapter-item card mb-3" data-chapter-id="{{ $chapter->id }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <div class="chapter-number">
                                            <span class="badge bg-primary fs-6">{{ $chapter->chapter_number }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1">{{ $chapter->title }}</h6>
                                        <div class="d-flex gap-2">
                                            @if($chapter->is_quiz)
                                                <span class="badge bg-warning">Quiz</span>
                                            @endif
                                            @if($chapter->duration_minutes)
                                                <span class="badge bg-info">{{ $chapter->duration_minutes }} min</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            Content: {{ Str::limit(strip_tags($chapter->content), 50) }}
                                        </small>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.course-content.edit-chapter', [$course, $chapter]) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.course-content.destroy-chapter', [$course, $chapter]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this chapter?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="drag-handle d-none">
                                            <i class="fas fa-grip-vertical text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5>No Chapters Yet</h5>
                            <p class="text-muted">Start building your course by adding the first chapter.</p>
                            <a href="{{ route('admin.course-content.create-chapter', $course) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Chapter
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        let sortable = null;
        let reorderMode = false;

        function toggleReorderMode() {
            reorderMode = !reorderMode;
            const chaptersList = document.getElementById('chapters-list');
            const dragHandles = document.querySelectorAll('.drag-handle');
            const actionButtons = document.querySelectorAll('.btn-group');

            if (reorderMode) {
                // Enable reorder mode
                dragHandles.forEach(handle => handle.classList.remove('d-none'));
                actionButtons.forEach(group => group.classList.add('d-none'));
                
                sortable = Sortable.create(chaptersList, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function(evt) {
                        updateChapterOrder();
                    }
                });
                
                document.querySelector('[onclick="toggleReorderMode()"]').innerHTML = 
                    '<i class="fas fa-check"></i> Save Order';
            } else {
                // Disable reorder mode
                dragHandles.forEach(handle => handle.classList.add('d-none'));
                actionButtons.forEach(group => group.classList.remove('d-none'));
                
                if (sortable) {
                    sortable.destroy();
                    sortable = null;
                }
                
                document.querySelector('[onclick="toggleReorderMode()"]').innerHTML = 
                    '<i class="fas fa-sort"></i> Reorder';
            }
        }

        function updateChapterOrder() {
            const chapters = [];
            document.querySelectorAll('.chapter-item').forEach((item, index) => {
                chapters.push({
                    id: item.dataset.chapterId,
                    chapter_number: index + 1
                });
            });

            fetch(`{{ route('admin.course-content.reorder-chapters', $course) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ chapters: chapters })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update chapter numbers in UI
                    document.querySelectorAll('.chapter-item').forEach((item, index) => {
                        const badge = item.querySelector('.badge');
                        badge.textContent = index + 1;
                    });
                }
            })
            .catch(error => {
                console.error('Error updating chapter order:', error);
                alert('Failed to update chapter order');
            });
        }
    </script>
</body>
</html>
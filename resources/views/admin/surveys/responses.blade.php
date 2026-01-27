<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->name }} - Responses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-comments"></i> {{ $survey->name }} - Responses</h2>
            <div class="btn-group">
                <a href="{{ route('admin.surveys.export', $survey) }}" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Course</th>
                                <th>Completed At</th>
                                <th>Answers</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($responses as $response)
                                <tr>
                                    <td>{{ $response->user->name ?? 'N/A' }}</td>
                                    <td>{{ $response->enrollment->course->title ?? 'N/A' }}</td>
                                    <td>{{ $response->completed_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <button onclick="toggleAnswers({{ $response->id }})" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View Answers ({{ $response->answers->count() }})
                                        </button>
                                        <div id="answers-{{ $response->id }}" class="d-none mt-2 p-3 bg-light rounded">
                                            @foreach($response->answers as $answer)
                                                <div class="mb-2">
                                                    <strong class="d-block">{{ $answer->surveyQuestion->question_text }}</strong>
                                                    <p class="mb-0 text-muted">{{ $answer->getFormattedAnswer() }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No responses yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $responses->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAnswers(id) {
            document.getElementById('answers-' + id).classList.toggle('d-none');
        }
    </script>
</body>
</html>

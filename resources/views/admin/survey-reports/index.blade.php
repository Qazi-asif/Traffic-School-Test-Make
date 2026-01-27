<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <h2 class="mb-4"><i class="fas fa-chart-pie"></i> Survey Reports</h2>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Active Surveys</h6>
                        <h2 class="mb-0">{{ $activeSurveys }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Responses</h6>
                        <h2 class="mb-0">{{ $totalResponses }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Average Completion</h6>
                        <h2 class="mb-0">{{ round($surveys->avg(fn($s) => $s->responses_count > 0 ? (($s->responses()->completed()->count() / $s->responses_count) * 100) : 0)) }}%</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Surveys</h5>
            </div>
            <div class="card-body">
                @foreach($surveys as $survey)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>{{ $survey->name }}</h5>
                                    <p class="text-muted mb-0">
                                        {{ $survey->state_code ?? 'All States' }} | 
                                        {{ $survey->questions_count }} questions | 
                                        {{ $survey->responses_count }} responses
                                    </p>
                                </div>
                                <a href="{{ route('admin.survey-reports.by-survey', $survey) }}" class="btn btn-primary">
                                    <i class="fas fa-chart-bar"></i> View Report
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->name }} - Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar"></i> {{ $survey->name }} - Report</h2>
            <div class="btn-group">
                <a href="{{ route('admin.surveys.export', $survey) }}" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="{{ route('admin.survey-reports.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Total Responses:</strong> {{ $statistics['total_responses'] }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date Range:</strong> 
                            {{ $statistics['date_range']['from'] ?? 'All time' }} - 
                            {{ $statistics['date_range']['to'] ?? 'Present' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @foreach($statistics['questions'] as $questionStat)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ $questionStat['question']->question_text }}</h5>
                    <small class="text-muted">
                        Type: {{ $questionStat['question']->getQuestionTypeLabel() }} | 
                        Answers: {{ $questionStat['total_answers'] }}
                    </small>
                </div>
                <div class="card-body">
                    @if(in_array($questionStat['question']->question_type, ['scale_1_5', 'scale_1_10', 'rating']))
                        <div class="mb-3">
                            <h3 class="text-primary">Average: {{ $questionStat['average'] ?? 'N/A' }}</h3>
                        </div>
                        <div class="mb-3">
                            @foreach($questionStat['distribution'] ?? [] as $rating => $count)
                                <div class="d-flex align-items-center mb-2">
                                    <span style="width: 50px;">{{ $rating }}</span>
                                    <div class="progress flex-grow-1 mx-3" style="height: 25px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $questionStat['total_answers'] > 0 ? ($count / $questionStat['total_answers']) * 100 : 0 }}%">
                                            {{ $count }}
                                        </div>
                                    </div>
                                    <span style="width: 80px; text-align: right;">
                                        {{ $questionStat['total_answers'] > 0 ? round(($count / $questionStat['total_answers']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>

                    @elseif(in_array($questionStat['question']->question_type, ['multiple_choice', 'yes_no']))
                        <div class="mb-3">
                            @foreach($questionStat['distribution'] ?? [] as $option => $count)
                                <div class="d-flex align-items-center mb-2">
                                    <span style="width: 150px; overflow: hidden; text-overflow: ellipsis;">{{ $option }}</span>
                                    <div class="progress flex-grow-1 mx-3" style="height: 25px;">
                                        <div class="progress-bar bg-success" style="width: {{ $questionStat['total_answers'] > 0 ? ($count / $questionStat['total_answers']) * 100 : 0 }}%">
                                            {{ $count }}
                                        </div>
                                    </div>
                                    <span style="width: 80px; text-align: right;">
                                        {{ $questionStat['total_answers'] > 0 ? round(($count / $questionStat['total_answers']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>

                    @elseif($questionStat['question']->question_type === 'text')
                        <div style="max-height: 400px; overflow-y: auto;">
                            @foreach($questionStat['answers'] ?? [] as $answer)
                                <div class="border-start border-primary border-4 ps-3 py-2 mb-3">
                                    <p class="mb-1">{{ $answer['text'] }}</p>
                                    <small class="text-muted">{{ $answer['user'] }} - {{ $answer['date'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

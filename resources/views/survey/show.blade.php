<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
        .scale-option {
            cursor: pointer;
            transition: all 0.2s;
        }
        .scale-option:hover {
            transform: scale(1.1);
        }
        .scale-option input[type="radio"]:checked + label {
            background-color: var(--accent) !important;
            color: white !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="card">
            <div class="card-body p-5">
                <h2 class="mb-2"><i class="fas fa-clipboard-list"></i> {{ $survey->name }}</h2>
                @if($survey->description)
                    <p class="text-muted mb-4">{{ $survey->description }}</p>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Please complete this survey before receiving your certificate. Your feedback helps us improve our courses.
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('survey.submit', $enrollment) }}" method="POST">
                    @csrf

                    @foreach($questions as $index => $question)
                        <div class="mb-5 pb-4 border-bottom">
                            <label class="form-label fw-bold fs-5">
                                {{ $index + 1 }}. {{ $question->question_text }}
                                @if($question->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @if($question->question_type === 'scale_1_5')
                                <div class="d-flex gap-3 mt-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="scale-option text-center">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $i }}" 
                                                   id="q{{ $question->id }}_{{ $i }}" 
                                                   {{ old("question_{$question->id}") == $i ? 'checked' : '' }}
                                                   {{ $question->is_required ? 'required' : '' }} class="d-none">
                                            <label for="q{{ $question->id }}_{{ $i }}" class="btn btn-outline-primary">
                                                {{ $i }}
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted">Poor</small>
                                    <small class="text-muted">Excellent</small>
                                </div>

                            @elseif($question->question_type === 'scale_1_10')
                                <div class="d-flex gap-2 mt-3 flex-wrap">
                                    @for($i = 1; $i <= 10; $i++)
                                        <div class="scale-option">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $i }}" 
                                                   id="q{{ $question->id }}_{{ $i }}"
                                                   {{ old("question_{$question->id}") == $i ? 'checked' : '' }}
                                                   {{ $question->is_required ? 'required' : '' }} class="d-none">
                                            <label for="q{{ $question->id }}_{{ $i }}" class="btn btn-outline-primary btn-sm">
                                                {{ $i }}
                                            </label>
                                        </div>
                                    @endfor
                                </div>

                            @elseif($question->question_type === 'rating')
                                <div class="star-rating d-flex flex-row-reverse justify-content-end gap-1 mt-3">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="question_{{ $question->id }}" value="{{ $i }}" 
                                               id="star{{ $question->id }}_{{ $i }}"
                                               {{ old("question_{$question->id}") == $i ? 'checked' : '' }}
                                               {{ $question->is_required ? 'required' : '' }}>
                                        <label for="star{{ $question->id }}_{{ $i }}">★</label>
                                    @endfor
                                </div>

                            @elseif($question->question_type === 'yes_no')
                                <div class="d-flex gap-4 mt-3">
                                    <div class="form-check">
                                        <input type="radio" name="question_{{ $question->id }}" value="Yes" 
                                               id="q{{ $question->id }}_yes"
                                               {{ old("question_{$question->id}") === 'Yes' ? 'checked' : '' }}
                                               {{ $question->is_required ? 'required' : '' }} class="form-check-input">
                                        <label class="form-check-label" for="q{{ $question->id }}_yes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" name="question_{{ $question->id }}" value="No" 
                                               id="q{{ $question->id }}_no"
                                               {{ old("question_{$question->id}") === 'No' ? 'checked' : '' }}
                                               {{ $question->is_required ? 'required' : '' }} class="form-check-input">
                                        <label class="form-check-label" for="q{{ $question->id }}_no">No</label>
                                    </div>
                                </div>

                            @elseif($question->question_type === 'multiple_choice')
                                <div class="mt-3">
                                    @foreach($question->options as $option)
                                        <div class="form-check mb-2">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $option }}" 
                                                   id="q{{ $question->id }}_{{ $loop->index }}"
                                                   {{ old("question_{$question->id}") === $option ? 'checked' : '' }}
                                                   {{ $question->is_required ? 'required' : '' }} class="form-check-input">
                                            <label class="form-check-label" for="q{{ $question->id }}_{{ $loop->index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif($question->question_type === 'text')
                                <textarea name="question_{{ $question->id }}" rows="4" 
                                          class="form-control mt-3" 
                                          {{ $question->is_required ? 'required' : '' }}>{{ old("question_{$question->id}") }}</textarea>
                            @endif
                        </div>
                    @endforeach

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Submit Survey
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

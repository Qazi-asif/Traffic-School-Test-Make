@extends('layouts.admin')

@section('title', 'Enhanced Course Player - Unlimited Content Support')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Course Navigation Sidebar -->
        <div class="col-md-3">
            <div class="card sticky-top">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book"></i>
                        {{ $enrollment->course->title ?? 'Course' }}
                    </h5>
                    <small class="text-muted">
                        Progress: {{ number_format($enrollment->progress_percentage ?? 0, 1) }}%
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($chapters as $chapter)
                        <div class="list-group-item chapter-item {{ $chapter->is_completed ? 'completed' : '' }}" 
                             data-chapter-id="{{ $chapter->id }}"
                             onclick="loadChapter({{ $chapter->id }})">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $chapter->title }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> {{ $chapter->estimated_reading_time }}min
                                        @if($chapter->questions_count > 0)
                                        <i class="fas fa-question-circle ml-2"></i> {{ $chapter->questions_count }} questions
                                        @endif
                                    </small>
                                    @if($chapter->content_stats['size_mb'] > 0.1)
                                    <small class="badge badge-info">{{ $chapter->content_stats['size_mb'] }}MB</small>
                                    @endif
                                </div>
                                <div>
                                    @if($chapter->is_completed)
                                    <i class="fas fa-check-circle text-success"></i>
                                    @else
                                    <i class="fas fa-circle text-muted"></i>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            @if($chapter->progress_percentage > 0)
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $chapter->progress_percentage }}%"></div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 id="chapter-title">Select a Chapter</h4>
                            <div id="chapter-stats" class="text-muted small" style="display: none;">
                                <span id="word-count"></span> |
                                <span id="reading-time"></span> |
                                <span id="content-size"></span>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" onclick="toggleFullscreen()">
                                <i class="fas fa-expand"></i> Fullscreen
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="adjustFontSize(1)">
                                <i class="fas fa-plus"></i> A+
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="adjustFontSize(-1)">
                                <i class="fas fa-minus"></i> A-
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Loading State -->
                    <div id="loading-state" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3">Loading chapter content...</p>
                    </div>

                    <!-- Chapter Content -->
                    <div id="chapter-content" style="display: none;">
                        <div id="content-container" class="chapter-content">
                            <!-- Content will be loaded here -->
                        </div>
                        
                        <!-- Progressive Loading Indicator -->
                        <div id="progressive-loading" style="display: none;" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ml-2">Loading more content...</span>
                        </div>
                        
                        <!-- Chapter Actions -->
                        <div id="chapter-actions" class="mt-4 pt-4 border-top">
                            <div class="row">
                                <div class="col-md-6">
                                    <button id="quiz-btn" class="btn btn-success btn-lg" style="display: none;" onclick="startQuiz()">
                                        <i class="fas fa-question-circle"></i> Take Quiz
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button id="complete-btn" class="btn btn-primary btn-lg" onclick="completeChapter()">
                                        <i class="fas fa-check"></i> Mark Complete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Section -->
                    <div id="quiz-section" style="display: none;">
                        <div class="quiz-header mb-4">
                            <h5><i class="fas fa-question-circle"></i> Chapter Quiz</h5>
                            <div class="quiz-progress">
                                <div class="progress">
                                    <div id="quiz-progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Question <span id="current-question">1</span> of <span id="total-questions">0</span></small>
                            </div>
                        </div>
                        
                        <div id="quiz-container">
                            <!-- Quiz questions will be loaded here -->
                        </div>
                        
                        <div class="quiz-actions mt-4">
                            <button id="prev-question" class="btn btn-outline-secondary" onclick="previousQuestion()" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <button id="next-question" class="btn btn-outline-primary" onclick="nextQuestion()">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                            <button id="submit-quiz" class="btn btn-success" onclick="submitQuiz()" style="display: none;">
                                <i class="fas fa-check"></i> Submit Quiz
                            </button>
                        </div>
                    </div>

                    <!-- Quiz Results -->
                    <div id="quiz-results" style="display: none;">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-trophy"></i> Quiz Complete!</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Score:</strong>
                                    <div class="h4 text-success" id="quiz-score">0%</div>
                                </div>
                                <div class="col-md-3">
                                    <strong>Correct:</strong>
                                    <div class="h5" id="correct-answers">0</div>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total:</strong>
                                    <div class="h5" id="total-quiz-questions">0</div>
                                </div>
                                <div class="col-md-3">
                                    <strong>Performance:</strong>
                                    <div class="h5" id="performance-level">Good</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Optimization Modal -->
<div class="modal fade" id="contentOptimizationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Content Loading Options</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This chapter contains large content. Choose your loading preference:</p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="loadingMode" id="progressive" value="progressive" checked>
                    <label class="form-check-label" for="progressive">
                        <strong>Progressive Loading</strong> - Load content in chunks (Recommended)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="loadingMode" id="full" value="full">
                    <label class="form-check-label" for="full">
                        <strong>Full Loading</strong> - Load all content at once
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="applyLoadingMode()">Continue</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentChapter = null;
let currentQuizData = null;
let currentQuestionIndex = 0;
let quizAnswers = {};
let fontSize = 16;

$(document).ready(function() {
    // Initialize enhanced course player
    initializeEnhancedPlayer();
});

function initializeEnhancedPlayer() {
    // Set up progressive loading observers
    setupProgressiveLoading();
    
    // Set up keyboard shortcuts
    setupKeyboardShortcuts();
    
    // Auto-save progress
    setInterval(saveProgress, 30000); // Every 30 seconds
}

function loadChapter(chapterId) {
    currentChapter = chapterId;
    
    // Update UI
    $('.chapter-item').removeClass('active');
    $(`.chapter-item[data-chapter-id="${chapterId}"]`).addClass('active');
    
    // Show loading state
    $('#loading-state').show();
    $('#chapter-content').hide();
    $('#quiz-section').hide();
    $('#quiz-results').hide();
    
    // Load chapter content
    $.get(`/admin/enhanced-course-player/{{ $enrollment->id }}/chapters/${chapterId}/content`)
        .done(function(response) {
            displayChapterContent(response);
        })
        .fail(function(xhr) {
            showError('Failed to load chapter content: ' + (xhr.responseJSON?.message || 'Unknown error'));
        });
}

function displayChapterContent(data) {
    const chapter = data.chapter;
    const questions = data.questions;
    
    // Update chapter info
    $('#chapter-title').text(chapter.title);
    $('#word-count').text(chapter.content_stats.word_count + ' words');
    $('#reading-time').text(chapter.reading_time + ' min read');
    $('#content-size').text(chapter.content_stats.size_mb + ' MB');
    $('#chapter-stats').show();
    
    // Display content
    $('#content-container').html(chapter.content);
    
    // Setup quiz if available
    if (data.has_quiz && questions.length > 0) {
        currentQuizData = questions;
        $('#quiz-btn').show();
    } else {
        $('#quiz-btn').hide();
    }
    
    // Check for large content features
    if (data.content_features.requires_chunking) {
        setupProgressiveLoading();
    }
    
    // Show content
    $('#loading-state').hide();
    $('#chapter-content').show();
    
    // Setup lazy loading for images
    setupLazyLoading();
    
    // Apply current font size
    adjustContentFontSize();
}

function setupProgressiveLoading() {
    // Intersection Observer for lazy loading content chunks
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const chunkElement = entry.target;
                    const chunkIndex = chunkElement.dataset.chunk;
                    
                    if (chunkIndex && currentChapter) {
                        loadContentChunk(currentChapter, chunkIndex, chunkElement);
                        observer.unobserve(chunkElement);
                    }
                }
            });
        }, {
            rootMargin: '100px'
        });
        
        // Observe lazy load elements
        document.querySelectorAll('.lazy-load').forEach(el => {
            observer.observe(el);
        });
    }
}

function loadContentChunk(chapterId, chunkIndex, element) {
    const loadingPlaceholder = element.querySelector('.loading-placeholder');
    const chunkContent = element.querySelector('.chunk-content');
    
    loadingPlaceholder.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Loading...';
    
    $.get(`/admin/enhanced-course-player/{{ $enrollment->id }}/chapters/${chapterId}/chunk/${chunkIndex}`)
        .done(function(response) {
            chunkContent.innerHTML = response.content;
            chunkContent.style.display = 'block';
            loadingPlaceholder.style.display = 'none';
        })
        .fail(function() {
            loadingPlaceholder.innerHTML = '<span class="text-danger">Failed to load content</span>';
        });
}

function setupLazyLoading() {
    // Lazy load images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

function startQuiz() {
    if (!currentQuizData || currentQuizData.length === 0) {
        showError('No quiz questions available');
        return;
    }
    
    // Hide content, show quiz
    $('#chapter-content').hide();
    $('#quiz-section').show();
    
    // Initialize quiz
    currentQuestionIndex = 0;
    quizAnswers = {};
    
    // Setup quiz UI
    $('#total-questions').text(currentQuizData.length);
    displayQuestion(0);
}

function displayQuestion(index) {
    if (index < 0 || index >= currentQuizData.length) return;
    
    const question = currentQuizData[index];
    currentQuestionIndex = index;
    
    // Update progress
    const progress = ((index + 1) / currentQuizData.length) * 100;
    $('#quiz-progress-bar').css('width', progress + '%');
    $('#current-question').text(index + 1);
    
    // Build question HTML
    let questionHtml = `
        <div class="question-container">
            <h6>Question ${index + 1}</h6>
            <p class="question-text">${question.question_text}</p>
    `;
    
    if (question.question_type === 'multiple_choice') {
        const options = JSON.parse(question.options || '{}');
        questionHtml += '<div class="options">';
        
        Object.keys(options).forEach(key => {
            if (options[key]) {
                const checked = quizAnswers[question.id] === key ? 'checked' : '';
                questionHtml += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="question_${question.id}" 
                               id="option_${key}" value="${key}" ${checked}
                               onchange="saveQuizAnswer(${question.id}, '${key}')">
                        <label class="form-check-label" for="option_${key}">
                            ${key}. ${options[key]}
                        </label>
                    </div>
                `;
            }
        });
        
        questionHtml += '</div>';
    } else if (question.question_type === 'true_false') {
        const checked_true = quizAnswers[question.id] === 'True' ? 'checked' : '';
        const checked_false = quizAnswers[question.id] === 'False' ? 'checked' : '';
        
        questionHtml += `
            <div class="options">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="question_${question.id}" 
                           id="true_${question.id}" value="True" ${checked_true}
                           onchange="saveQuizAnswer(${question.id}, 'True')">
                    <label class="form-check-label" for="true_${question.id}">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="question_${question.id}" 
                           id="false_${question.id}" value="False" ${checked_false}
                           onchange="saveQuizAnswer(${question.id}, 'False')">
                    <label class="form-check-label" for="false_${question.id}">False</label>
                </div>
            </div>
        `;
    }
    
    questionHtml += '</div>';
    
    $('#quiz-container').html(questionHtml);
    
    // Update navigation buttons
    $('#prev-question').prop('disabled', index === 0);
    $('#next-question').toggle(index < currentQuizData.length - 1);
    $('#submit-quiz').toggle(index === currentQuizData.length - 1);
}

function saveQuizAnswer(questionId, answer) {
    quizAnswers[questionId] = answer;
}

function previousQuestion() {
    if (currentQuestionIndex > 0) {
        displayQuestion(currentQuestionIndex - 1);
    }
}

function nextQuestion() {
    if (currentQuestionIndex < currentQuizData.length - 1) {
        displayQuestion(currentQuestionIndex + 1);
    }
}

function submitQuiz() {
    // Validate all questions answered
    const unanswered = currentQuizData.filter(q => !quizAnswers[q.id]);
    
    if (unanswered.length > 0) {
        if (!confirm(`You have ${unanswered.length} unanswered questions. Submit anyway?`)) {
            return;
        }
    }
    
    // Show loading
    $('#submit-quiz').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
    
    // Submit quiz
    $.post(`/admin/enhanced-course-player/{{ $enrollment->id }}/chapters/${currentChapter}/quiz`, {
        answers: quizAnswers,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        displayQuizResults(response);
    })
    .fail(function(xhr) {
        showError('Failed to submit quiz: ' + (xhr.responseJSON?.message || 'Unknown error'));
        $('#submit-quiz').prop('disabled', false).html('<i class="fas fa-check"></i> Submit Quiz');
    });
}

function displayQuizResults(results) {
    // Hide quiz, show results
    $('#quiz-section').hide();
    $('#quiz-results').show();
    
    // Update results display
    $('#quiz-score').text(results.percentage + '%');
    $('#correct-answers').text(results.correct_answers);
    $('#total-quiz-questions').text(results.total_questions);
    $('#performance-level').text(results.performance_stats?.performance_level || 'Good');
    
    // Update chapter status if passed
    if (results.passed) {
        $(`.chapter-item[data-chapter-id="${currentChapter}"]`).addClass('completed');
    }
}

function completeChapter() {
    if (!currentChapter) return;
    
    $('#complete-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Completing...');
    
    $.post(`/admin/enhanced-course-player/{{ $enrollment->id }}/chapters/${currentChapter}/complete`, {
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            $(`.chapter-item[data-chapter-id="${currentChapter}"]`).addClass('completed');
            showSuccess('Chapter completed successfully!');
            
            // Auto-load next chapter if available
            if (response.next_chapter_available) {
                setTimeout(() => {
                    const nextChapter = $(`.chapter-item[data-chapter-id="${currentChapter}"]`).next('.chapter-item');
                    if (nextChapter.length) {
                        const nextChapterId = nextChapter.data('chapter-id');
                        loadChapter(nextChapterId);
                    }
                }, 2000);
            }
        }
    })
    .fail(function(xhr) {
        showError('Failed to complete chapter: ' + (xhr.responseJSON?.message || 'Unknown error'));
    })
    .always(function() {
        $('#complete-btn').prop('disabled', false).html('<i class="fas fa-check"></i> Mark Complete');
    });
}

function adjustFontSize(delta) {
    fontSize += delta;
    fontSize = Math.max(12, Math.min(24, fontSize)); // Limit between 12px and 24px
    adjustContentFontSize();
}

function adjustContentFontSize() {
    $('#content-container').css('font-size', fontSize + 'px');
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
}

function setupKeyboardShortcuts() {
    $(document).keydown(function(e) {
        // Only in quiz mode
        if ($('#quiz-section').is(':visible')) {
            if (e.key === 'ArrowLeft') {
                previousQuestion();
            } else if (e.key === 'ArrowRight') {
                nextQuestion();
            }
        }
        
        // Font size shortcuts
        if (e.ctrlKey) {
            if (e.key === '=') {
                e.preventDefault();
                adjustFontSize(1);
            } else if (e.key === '-') {
                e.preventDefault();
                adjustFontSize(-1);
            }
        }
    });
}

function saveProgress() {
    if (currentChapter) {
        // Auto-save progress silently
        $.post(`/admin/enhanced-course-player/{{ $enrollment->id }}/chapters/${currentChapter}/progress`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        });
    }
}

function showError(message) {
    // Show error notification
    const alert = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>`;
    
    $('#chapter-content').prepend(alert);
}

function showSuccess(message) {
    // Show success notification
    const alert = `<div class="alert alert-success alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>`;
    
    $('#chapter-content').prepend(alert);
}
</script>
@endsection

@section('styles')
<style>
.chapter-item {
    cursor: pointer;
    transition: all 0.3s ease;
}

.chapter-item:hover {
    background-color: #f8f9fa;
}

.chapter-item.active {
    background-color: #007bff;
    color: white;
}

.chapter-item.completed {
    border-left: 4px solid #28a745;
}

.chapter-content {
    line-height: 1.6;
    font-size: 16px;
}

.chapter-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 15px 0;
}

.chapter-content h1, .chapter-content h2, .chapter-content h3 {
    margin-top: 30px;
    margin-bottom: 15px;
}

.quiz-progress {
    margin-bottom: 20px;
}

.question-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.question-text {
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 20px;
}

.options .form-check {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
    transition: background-color 0.2s;
}

.options .form-check:hover {
    background-color: #e9ecef;
}

.options .form-check-label {
    font-size: 16px;
    cursor: pointer;
    width: 100%;
}

.content-chunk {
    margin-bottom: 20px;
}

.loading-placeholder {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.lazy-load {
    min-height: 100px;
}

.sticky-top {
    top: 20px;
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 20px;
    }
    
    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}

/* Fullscreen styles */
:fullscreen .container-fluid {
    height: 100vh;
    overflow-y: auto;
}

/* Print styles */
@media print {
    .card-header, .chapter-item, #chapter-actions, #quiz-section, #quiz-results {
        display: none !important;
    }
    
    .chapter-content {
        font-size: 12pt;
        line-height: 1.4;
    }
}
</style>
@endsection
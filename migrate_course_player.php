<?php
/**
 * Migrate Course Player Interface
 * Replicate exact course player from previous system
 */

echo "🎓 MIGRATING COURSE PLAYER INTERFACE\n";
echo "===================================\n\n";

// Create the course player view
$coursePlayerView = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title ?? "Course Player" }} - {{ config("app.name") }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .course-player {
            min-height: 100vh;
            background: #f8f9fa;
        }
        .course-sidebar {
            background: white;
            border-right: 1px solid #dee2e6;
            height: 100vh;
            overflow-y: auto;
        }
        .course-content {
            background: white;
            min-height: 100vh;
            padding: 0;
        }
        .chapter-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s;
        }
        .chapter-item:hover {
            background: #f8f9fa;
        }
        .chapter-item.active {
            background: #007bff;
            color: white;
        }
        .chapter-item.completed {
            background: #28a745;
            color: white;
        }
        .progress-bar-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
        }
        .content-area {
            margin-top: 80px;
            padding: 30px;
        }
        .quiz-container {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .question-counter {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .answer-option {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .answer-option:hover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        .answer-option.selected {
            border-color: #007bff;
            background: #007bff;
            color: white;
        }
        .answer-option.correct {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
        .answer-option.incorrect {
            border-color: #dc3545;
            background: #dc3545;
            color: white;
        }
        .timer-display {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #343a40;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            z-index: 1001;
        }
        .navigation-buttons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1001;
        }
        .video-container {
            position: relative;
            width: 100%;
            height: 400px;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }
        .certificate-section {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="course-player">
        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $course->title ?? "Course" }}</h5>
                    <small class="text-muted">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="progress me-3" style="width: 200px;">
                        <div class="progress-bar" id="overall-progress" role="progressbar" style="width: {{ $enrollment->progress_percentage ?? 0 }}%">
                            {{ round($enrollment->progress_percentage ?? 0) }}%
                        </div>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Timer Display -->
        <div class="timer-display" id="timer-display">
            <i class="fas fa-clock me-2"></i>
            <span id="timer">00:00:00</span>
        </div>

        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 course-sidebar" id="course-sidebar">
                <div class="p-3 border-bottom">
                    <h6 class="mb-0">Course Chapters</h6>
                </div>
                <div id="chapters-list">
                    @if(isset($chapters) && $chapters->count() > 0)
                        @foreach($chapters as $chapter)
                        <div class="chapter-item" data-chapter-id="{{ $chapter->id }}" onclick="loadChapter({{ $chapter->id }})">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $chapter->title }}</div>
                                    <small class="text-muted">{{ $chapter->duration ?? 5 }} minutes</small>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle chapter-status" id="status-{{ $chapter->id }}"></i>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="p-3 text-muted">No chapters available</div>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 course-content">
                <div class="content-area" id="content-area">
                    <!-- Welcome Screen -->
                    <div id="welcome-screen" class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-graduation-cap fa-5x text-primary mb-3"></i>
                            <h2>Welcome to {{ $course->title ?? "Your Course" }}</h2>
                            <p class="lead">{{ $course->description ?? "Start your learning journey" }}</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-book fa-2x text-info mb-3"></i>
                                        <h5>Chapters</h5>
                                        <p>{{ $chapters->count() ?? 0 }} chapters to complete</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-question-circle fa-2x text-warning mb-3"></i>
                                        <h5>Quizzes</h5>
                                        <p>Interactive quizzes included</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-certificate fa-2x text-success mb-3"></i>
                                        <h5>Certificate</h5>
                                        <p>Earn your completion certificate</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary btn-lg" onclick="startCourse()">
                                <i class="fas fa-play me-2"></i>Start Course
                            </button>
                        </div>
                    </div>

                    <!-- Chapter Content -->
                    <div id="chapter-content" style="display: none;">
                        <div id="chapter-header">
                            <h3 id="chapter-title"></h3>
                            <p id="chapter-description" class="text-muted"></p>
                        </div>

                        <!-- Video Content -->
                        <div id="video-section" style="display: none;">
                            <div class="video-container">
                                <video id="chapter-video" controls style="width: 100%; height: 100%;">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>

                        <!-- Text Content -->
                        <div id="text-content">
                            <!-- Chapter content will be loaded here -->
                        </div>

                        <!-- Quiz Section -->
                        <div id="quiz-section" style="display: none;">
                            <div class="quiz-container">
                                <div class="question-counter">
                                    Question <span id="current-question">1</span> of <span id="total-questions">1</span>
                                </div>
                                
                                <div id="question-content">
                                    <h4 id="question-text"></h4>
                                    <div id="answer-options">
                                        <!-- Answer options will be loaded here -->
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button class="btn btn-secondary me-2" id="prev-question" onclick="previousQuestion()" disabled>
                                        <i class="fas fa-arrow-left me-2"></i>Previous
                                    </button>
                                    <button class="btn btn-primary" id="next-question" onclick="nextQuestion()">
                                        Next<i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                    <button class="btn btn-success" id="submit-quiz" onclick="submitQuiz()" style="display: none;">
                                        <i class="fas fa-check me-2"></i>Submit Quiz
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Results -->
                        <div id="quiz-results" style="display: none;">
                            <div class="quiz-container">
                                <div class="text-center">
                                    <i class="fas fa-chart-bar fa-3x mb-3" id="results-icon"></i>
                                    <h3 id="results-title"></h3>
                                    <div class="row mt-4">
                                        <div class="col-md-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h4 id="score-percentage" class="text-primary">0%</h4>
                                                    <p>Score</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h4 id="correct-answers" class="text-success">0</h4>
                                                    <p>Correct</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h4 id="wrong-answers" class="text-danger">0</h4>
                                                    <p>Wrong</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h4 id="total-quiz-questions" class="text-info">0</h4>
                                                    <p>Total</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button class="btn btn-primary" onclick="completeChapter()">
                                            <i class="fas fa-arrow-right me-2"></i>Continue to Next Chapter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Exam Section -->
                    <div id="final-exam-section" style="display: none;">
                        <div class="text-center mb-4">
                            <i class="fas fa-graduation-cap fa-4x text-warning mb-3"></i>
                            <h2>Final Examination</h2>
                            <p class="lead">Complete your final exam to earn your certificate</p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5>Exam Instructions:</h5>
                                <ul>
                                    <li>You must score 80% or higher to pass</li>
                                    <li>You have unlimited time to complete the exam</li>
                                    <li>Review your answers before submitting</li>
                                    <li>Once submitted, you cannot retake the exam</li>
                                </ul>
                                
                                <div class="mt-4">
                                    <button class="btn btn-warning btn-lg" onclick="startFinalExam()">
                                        <i class="fas fa-play me-2"></i>Start Final Exam
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Section -->
                    <div id="certificate-section" style="display: none;">
                        <div class="certificate-section">
                            <i class="fas fa-certificate fa-4x mb-3"></i>
                            <h2>Congratulations!</h2>
                            <p class="lead">You have successfully completed the course</p>
                            
                            <div class="mt-4">
                                <button class="btn btn-light btn-lg me-3" onclick="viewCertificate()">
                                    <i class="fas fa-eye me-2"></i>View Certificate
                                </button>
                                <button class="btn btn-outline-light btn-lg" onclick="downloadCertificate()">
                                    <i class="fas fa-download me-2"></i>Download Certificate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button class="btn btn-secondary me-2" id="prev-chapter" onclick="previousChapter()" style="display: none;">
                <i class="fas fa-arrow-left"></i>
            </button>
            <button class="btn btn-primary" id="next-chapter" onclick="nextChapter()" style="display: none;">
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Course Player JavaScript will be loaded here
        let currentChapter = 0;
        let chapters = @json($chapters ?? []);
        let enrollment = @json($enrollment ?? null);
        let currentQuiz = null;
        let currentQuestionIndex = 0;
        let quizAnswers = [];
        let timer = { hours: 0, minutes: 0, seconds: 0 };
        let timerInterval = null;

        // Initialize course player
        document.addEventListener("DOMContentLoaded", function() {
            initializeCoursePlayer();
            startTimer();
            updateProgress();
        });

        function initializeCoursePlayer() {
            // Load saved progress
            loadProgress();
            
            // Set up CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content")
                }
            });
        }

        function startCourse() {
            $("#welcome-screen").hide();
            if (chapters.length > 0) {
                loadChapter(chapters[0].id);
            }
        }

        function loadChapter(chapterId) {
            // Implementation will be added in next part
            console.log("Loading chapter:", chapterId);
        }

        function startTimer() {
            timerInterval = setInterval(function() {
                timer.seconds++;
                if (timer.seconds >= 60) {
                    timer.seconds = 0;
                    timer.minutes++;
                    if (timer.minutes >= 60) {
                        timer.minutes = 0;
                        timer.hours++;
                    }
                }
                updateTimerDisplay();
            }, 1000);
        }

        function updateTimerDisplay() {
            const display = String(timer.hours).padStart(2, "0") + ":" + 
                           String(timer.minutes).padStart(2, "0") + ":" + 
                           String(timer.seconds).padStart(2, "0");
            $("#timer").text(display);
        }

        function toggleSidebar() {
            $("#course-sidebar").toggle();
        }

        // Additional functions will be implemented
    </script>
</body>
</html>';

// Save the course player view
if (!is_dir('resources/views/course')) {
    mkdir('resources/views/course', 0755, true);
}

file_put_contents('resources/views/course/player.blade.php', $coursePlayerView);
echo "✅ Created course player interface\n";
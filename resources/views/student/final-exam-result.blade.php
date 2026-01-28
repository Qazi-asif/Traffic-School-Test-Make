<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Final Exam Results - {{ $courseDetails->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .results-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .results-header {
            background: var(--primary-gradient);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .results-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }

        .results-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .results-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .score-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .score-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .score-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .score-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .score-card.overall::before {
            background: var(--success-gradient);
        }

        .score-card.quiz::before {
            background: var(--info-gradient);
        }

        .score-card.free-response::before {
            background: var(--warning-gradient);
        }

        .score-card.final-exam::before {
            background: var(--dark-gradient);
        }

        .score-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .score-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }

        .score-icon.overall { background: var(--success-gradient); }
        .score-icon.quiz { background: var(--info-gradient); }
        .score-icon.free-response { background: var(--warning-gradient); }
        .score-icon.final-exam { background: var(--dark-gradient); }

        .score-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .score-subtitle {
            font-size: 0.9rem;
            color: #718096;
            margin: 0;
        }

        .score-display {
            text-align: center;
            margin-bottom: 20px;
        }

        .score-number {
            font-size: 3rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
        }

        .score-percentage {
            font-size: 1rem;
            color: #718096;
            margin-top: 5px;
        }

        .progress-ring {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            position: relative;
        }

        .progress-ring svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .progress-ring-bg {
            fill: none;
            stroke: #e2e8f0;
            stroke-width: 8;
        }

        .progress-ring-fill {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dasharray 1s ease;
        }

        .grade-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 10px;
        }

        .grade-A { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
        .grade-B { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
        .grade-C { background: linear-gradient(135deg, #f093fb, #f5576c); color: white; }
        .grade-D { background: linear-gradient(135deg, #ffecd2, #fcb69f); color: #8b4513; }
        .grade-F { background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 15px;
        }

        .status-passed {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }

        .status-failed {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .status-under-review {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .breakdown-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .breakdown-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .breakdown-title i {
            margin-right: 10px;
            color: #667eea;
        }

        .component-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .component-item {
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .component-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .component-header {
            display: flex;
            justify-content-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .component-name {
            font-weight: 600;
            color: #2d3748;
        }

        .component-weight {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .component-score {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .component-progress {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .component-progress-fill {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 4px;
            transition: width 1s ease;
        }

        .feedback-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feedback-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .rating-stars {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .star {
            font-size: 2rem;
            color: #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .star:hover,
        .star.active {
            color: #ffd700;
            transform: scale(1.1);
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .grading-period {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 2px solid #f6c23e;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .grading-period h5 {
            color: #856404;
            margin-bottom: 10px;
        }

        .grading-period p {
            color: #856404;
            margin: 0;
        }

        .certificate-section {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .certificate-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .btn-certificate {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-certificate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
            color: white;
        }

        @media (max-width: 768px) {
            .results-header h1 {
                font-size: 2rem;
            }
            
            .score-cards {
                grid-template-columns: 1fr;
            }
            
            .component-breakdown {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="results-container">
        <!-- Header -->
        <div class="results-header">
            <h1><i class="fas fa-graduation-cap me-3"></i>Final Exam Results</h1>
            <p>{{ $courseDetails->title }} - {{ $courseDetails->state_code ?? 'Course' }}</p>
        </div>

        <!-- Score Cards -->
        <div class="score-cards">
            <!-- Overall Score -->
            <div class="score-card overall">
                <div class="score-header">
                    <div class="score-icon overall">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <h3 class="score-title">Overall Score</h3>
                        <p class="score-subtitle">Final Grade</p>
                    </div>
                </div>
                <div class="score-display">
                    <div class="progress-ring">
                        <svg viewBox="0 0 120 120">
                            <circle class="progress-ring-bg" cx="60" cy="60" r="54"></circle>
                            <circle class="progress-ring-fill" cx="60" cy="60" r="54" 
                                    stroke="url(#overallGradient)" 
                                    stroke-dasharray="{{ 2 * pi() * 54 }}" 
                                    stroke-dashoffset="{{ 2 * pi() * 54 * (1 - $result->overall_score / 100) }}">
                            </circle>
                            <defs>
                                <linearGradient id="overallGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#11998e"/>
                                    <stop offset="100%" style="stop-color:#38ef7d"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="score-number">{{ number_format($result->overall_score, 1) }}</div>
                    <div class="score-percentage">out of 100%</div>
                    <div class="grade-badge grade-{{ $result->grade_letter }}">
                        Grade: {{ $result->grade_letter }}
                    </div>
                    <div class="status-badge status-{{ $result->status }}">
                        <i class="fas fa-{{ $result->is_passing ? 'check-circle' : 'times-circle' }} me-2"></i>
                        {{ ucfirst(str_replace('_', ' ', $result->status)) }}
                    </div>
                </div>
            </div>

            <!-- Quiz Average -->
            <div class="score-card quiz">
                <div class="score-header">
                    <div class="score-icon quiz">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h3 class="score-title">Chapter Quizzes</h3>
                        <p class="score-subtitle">Average Score (30%)</p>
                    </div>
                </div>
                <div class="score-display">
                    <div class="score-number">{{ number_format($componentScores['quiz_average']['score'], 1) }}</div>
                    <div class="score-percentage">Average Score</div>
                    <div class="component-progress">
                        <div class="component-progress-fill" style="width: {{ $componentScores['quiz_average']['score'] }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Free Response -->
            @if($componentScores['free_response']['score'] !== null)
            <div class="score-card free-response">
                <div class="score-header">
                    <div class="score-icon free-response">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h3 class="score-title">Free Response</h3>
                        <p class="score-subtitle">Written Answers (20%)</p>
                    </div>
                </div>
                <div class="score-display">
                    <div class="score-number">{{ number_format($componentScores['free_response']['score'], 1) }}</div>
                    <div class="score-percentage">Total Score</div>
                    <div class="component-progress">
                        <div class="component-progress-fill" style="width: {{ $componentScores['free_response']['score'] }}%"></div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Final Exam -->
            <div class="score-card final-exam">
                <div class="score-header">
                    <div class="score-icon final-exam">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="score-title">Final Exam</h3>
                        <p class="score-subtitle">{{ $result->final_exam_correct }}/{{ $result->final_exam_total }} Correct (50%)</p>
                    </div>
                </div>
                <div class="score-display">
                    <div class="score-number">{{ number_format($result->final_exam_score, 1) }}</div>
                    <div class="score-percentage">Exam Score</div>
                    <div class="component-progress">
                        <div class="component-progress-fill" style="width: {{ $result->final_exam_score }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificate Section -->
        @if($result->passed)
        <div class="certificate-section">
            <div class="certificate-icon">
                <i class="fas fa-certificate"></i>
            </div>
            <h3>Congratulations!</h3>
            <p>You have successfully completed the course. Your certificate is ready for download.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/certificate/view?enrollment_id={{ $result->enrollment_id }}" class="btn-certificate" target="_blank">
                    <i class="fas fa-eye me-2"></i>View Certificate
                </a>
                <a href="/certificate/generate?enrollment_id={{ $result->enrollment_id }}" class="btn-certificate" target="_blank">
                    <i class="fas fa-download me-2"></i>Download Certificate
                </a>
            </div>
        </div>
        @elseif($result->status === 'under_review')
        <div class="grading-period">
            <h5><i class="fas fa-clock me-2"></i>Under Review</h5>
            <p>Your exam is currently being reviewed. Results will be available within 24 hours.</p>
            @if($result->grading_period_ends_at)
                <p><strong>Review completes by:</strong> {{ $result->grading_period_ends_at->format('M j, Y g:i A') }}</p>
            @endif
        </div>
        @else
        <div class="alert alert-warning text-center">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Exam Not Passed</h5>
            <p>You need a score of {{ $result->passing_threshold }}% or higher to pass. Please review the course material and retake the exam.</p>
            <a href="/course-player/{{ $result->enrollment_id }}" class="btn btn-primary">
                <i class="fas fa-redo me-2"></i>Review Course & Retake Exam
            </a>
        </div>
        @endif

       

        <!-- Exam Details -->
        <div class="breakdown-section">
            <h3 class="breakdown-title">
                <i class="fas fa-info-circle"></i>Exam Details
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="component-item">
                        <h5>Completion Time</h5>
                        <p class="mb-0">{{ $result->formatted_exam_duration }}</p>
                        <small class="text-muted">Completed on {{ $result->exam_completed_at->format('M j, Y g:i A') }}</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="component-item">
                        <h5>Passing Threshold</h5>
                        <p class="mb-0">{{ $result->passing_threshold }}%</p>
                        <small class="text-muted">Minimum score required to pass</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('student_rating');
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
            
            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#ffd700';
                    } else {
                        s.style.color = '#e2e8f0';
                    }
                });
            });
        });
        
        document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
            const currentRating = ratingInput.value;
            
            stars.forEach((s, index) => {
                if (index < currentRating) {
                    s.style.color = '#ffd700';
                } else {
                    s.style.color = '#e2e8f0';
                }
            });
        });

        // Animate progress bars on load
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.component-progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });
    </script>
</body>
</html>
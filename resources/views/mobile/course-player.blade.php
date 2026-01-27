@extends('layouts.mobile-responsive')

@section('title', 'Course Player - Mobile')

@section('content')
<div class="mobile-course-player" id="coursePlayer">
    <div class="course-header bg-primary text-white p-3 d-flex align-items-center justify-content-between">
        <button onclick="goBack()" class="btn btn-link text-white p-0 touch-target" aria-label="Go back">
            <i class="fas fa-arrow-left fs-4"></i>
        </button>
        <h1 class="h5 mb-0 flex-grow-1 text-center">{{ $course->title ?? 'Course Title' }}</h1>
        <button onclick="toggleFullscreen()" class="btn btn-link text-white p-0 touch-target" aria-label="Toggle fullscreen">
            <i class="fas fa-expand fs-4"></i>
        </button>
    </div>

    <div class="progress-container bg-white p-3 border-bottom">
        <div class="progress mb-2" style="height: 8px;">
            <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
        </div>
        <div class="text-center small text-muted">
            <span id="progressText">0% Complete</span>
        </div>
    </div>

    <div class="content-area flex-grow-1 p-3" id="contentArea">
        <div id="videoContainer" class="video-container mb-3" style="display: none;">
            <video id="videoPlayer" class="w-100 rounded" controls playsinline>
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <div id="textContainer" class="text-content" style="display: none;">
            <div id="textContent" class="lh-lg"></div>
        </div>
        
        <div id="quizContainer" class="quiz-container" style="display: none;">
            <h3 id="quizQuestion" class="h5 mb-3"></h3>
            <div id="quizOptions" class="mb-3"></div>
            <button onclick="submitAnswer()" id="submitBtn" class="btn btn-primary w-100 touch-target" disabled>
                Submit Answer
            </button>
        </div>
    </div>

    <div class="navigation-controls bg-white border-top p-3 d-flex align-items-center justify-content-between">
        <button onclick="previousContent()" id="prevBtn" class="btn btn-primary touch-target">
            <i class="fas fa-chevron-left me-1"></i> Previous
        </button>
        <span id="contentCounter" class="text-muted">1 / 10</span>
        <button onclick="nextContent()" id="nextBtn" class="btn btn-primary touch-target">
            Next <i class="fas fa-chevron-right ms-1"></i>
        </button>
    </div>
</div>

<style>
.mobile-course-player {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: #f8f9fa;
}

.video-container video {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.quiz-option {
    display: block;
    width: 100%;
    padding: 1rem;
    margin-bottom: 0.5rem;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
}

.quiz-option:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.quiz-option.selected {
    border-color: #007bff;
    background: #e3f2fd;
}

@media (max-width: 576px) {
    .course-header h1 {
        font-size: 1rem;
    }
    
    .navigation-controls {
        padding: 0.75rem;
    }
    
    .navigation-controls button {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
let currentIndex = 0;
let courseContent = [];
let selectedAnswer = null;
let touchStartX = 0;
let touchEndX = 0;

// Sample course content
courseContent = [
    {
        type: 'video',
        url: '/videos/intro.mp4',
        title: 'Introduction'
    },
    {
        type: 'text',
        content: '<h4>Traffic Safety Rules</h4><p>Understanding basic traffic safety rules is essential for all drivers...</p>',
        title: 'Safety Rules'
    },
    {
        type: 'quiz',
        question: 'What is the speed limit in residential areas?',
        options: ['25 mph', '35 mph', '45 mph', '55 mph'],
        correct: 0,
        title: 'Speed Limits Quiz'
    }
];

function goBack() {
    if (confirm('Are you sure you want to leave this course?')) {
        window.history.back();
    }
}

function toggleFullscreen() {
    const contentArea = document.getElementById('contentArea');
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        contentArea.requestFullscreen();
    }
}

function previousContent() {
    if (currentIndex > 0) {
        currentIndex--;
        loadContent();
        updateProgress();
    }
}

function nextContent() {
    if (currentIndex < courseContent.length - 1) {
        currentIndex++;
        loadContent();
        updateProgress();
    }
}

function loadContent() {
    const content = courseContent[currentIndex];
    
    // Hide all containers
    document.getElementById('videoContainer').style.display = 'none';
    document.getElementById('textContainer').style.display = 'none';
    document.getElementById('quizContainer').style.display = 'none';
    
    // Show appropriate container
    if (content.type === 'video') {
        document.getElementById('videoContainer').style.display = 'block';
        document.getElementById('videoPlayer').src = content.url;
    } else if (content.type === 'text') {
        document.getElementById('textContainer').style.display = 'block';
        document.getElementById('textContent').innerHTML = content.content;
    } else if (content.type === 'quiz') {
        document.getElementById('quizContainer').style.display = 'block';
        document.getElementById('quizQuestion').textContent = content.question;
        
        const optionsContainer = document.getElementById('quizOptions');
        optionsContainer.innerHTML = '';
        
        content.options.forEach((option, index) => {
            const button = document.createElement('button');
            button.className = 'quiz-option touch-target';
            button.textContent = option;
            button.onclick = () => selectAnswer(index);
            optionsContainer.appendChild(button);
        });
        
        selectedAnswer = null;
        document.getElementById('submitBtn').disabled = true;
    }
    
    updateNavigation();
}

function selectAnswer(index) {
    selectedAnswer = index;
    
    // Update visual selection
    document.querySelectorAll('.quiz-option').forEach((btn, i) => {
        btn.classList.toggle('selected', i === index);
    });
    
    document.getElementById('submitBtn').disabled = false;
}

function submitAnswer() {
    const content = courseContent[currentIndex];
    const isCorrect = selectedAnswer === content.correct;
    
    alert(isCorrect ? 'Correct!' : 'Incorrect. The correct answer is: ' + content.options[content.correct]);
    
    nextContent();
}

function updateProgress() {
    const percentage = ((currentIndex + 1) / courseContent.length) * 100;
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = Math.round(percentage) + '% Complete';
}

function updateNavigation() {
    document.getElementById('prevBtn').disabled = currentIndex === 0;
    document.getElementById('nextBtn').disabled = currentIndex === courseContent.length - 1;
    document.getElementById('contentCounter').textContent = `${currentIndex + 1} / ${courseContent.length}`;
}

// Touch/Swipe handling
document.getElementById('coursePlayer').addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
});

document.getElementById('coursePlayer').addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0 && currentIndex < courseContent.length - 1) {
            nextContent();
        } else if (diff < 0 && currentIndex > 0) {
            previousContent();
        }
    }
}

// Initialize
loadContent();
updateProgress();
</script>
@endsection

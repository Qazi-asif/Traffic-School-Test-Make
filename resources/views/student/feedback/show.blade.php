<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
    
    <div class="container-fluid mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Course Feedback</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-comments me-2"></i>Your Submitted Feedback</h5>
                            <p class="mb-0">Here are all the feedback submissions you have made for your courses.</p>
                        </div>

                        <div id="feedback-list">
                            <!-- Feedback will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    
    <script>
        // Load user's feedback from localStorage and any backend data
        function loadUserFeedback() {
            const feedbackContainer = document.getElementById('feedback-list');
            let feedbackHtml = '';
            
            // Check localStorage for feedback (from course completion)
            const localFeedback = localStorage.getItem('student_feedback_{{ auth()->id() }}');
            if (localFeedback) {
                try {
                    const feedback = JSON.parse(localFeedback);
                    const date = new Date(feedback.timestamp).toLocaleDateString();
                    const stars = '★'.repeat(feedback.rating) + '☆'.repeat(5 - feedback.rating);
                    
                    feedbackHtml += `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title">Course Feedback</h6>
                                    <small class="text-muted">${date}</small>
                                </div>
                                <div class="mb-2">
                                    <span class="text-warning" style="font-size: 1.2em;">${stars}</span>
                                    <span class="ms-2">${feedback.rating}/5 stars</span>
                                </div>
                                <p class="card-text">${feedback.feedback}</p>
                                <span class="badge bg-success">Submitted</span>
                            </div>
                        </div>
                    `;
                } catch (e) {
                    console.error('Error parsing feedback:', e);
                }
            }
            
            // Check for feedback submitted during final exam completion
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('student_feedback_') && key !== 'student_feedback_{{ auth()->id() }}') {
                    try {
                        const feedback = JSON.parse(localStorage.getItem(key));
                        if (feedback && feedback.rating && feedback.feedback) {
                            const date = new Date(feedback.timestamp).toLocaleDateString();
                            const stars = '★'.repeat(feedback.rating) + '☆'.repeat(5 - feedback.rating);
                            
                            feedbackHtml += `
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title">Course Feedback</h6>
                                            <small class="text-muted">${date}</small>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-warning" style="font-size: 1.2em;">${stars}</span>
                                            <span class="ms-2">${feedback.rating}/5 stars</span>
                                        </div>
                                        <p class="card-text">${feedback.feedback}</p>
                                        <span class="badge bg-success">Submitted</span>
                                    </div>
                                </div>
                            `;
                        }
                    } catch (e) {
                        console.error('Error parsing feedback:', e);
                    }
                }
            });
            
            if (feedbackHtml === '') {
                feedbackHtml = `
                    <div class="alert alert-info text-center">
                        <i class="fas fa-comment-slash fa-3x mb-3 text-muted"></i>
                        <h5>No Feedback Yet</h5>
                        <p class="mb-2">You haven't submitted any course feedback yet.</p>
                        <p class="mb-0"><small class="text-muted">Feedback is typically submitted after completing the final exam.</small></p>
                    </div>
                `;
            }
            
            feedbackContainer.innerHTML = feedbackHtml;
        }
        
        // Load feedback when page loads
        document.addEventListener('DOMContentLoaded', loadUserFeedback);
    </script>
</body>
</html>

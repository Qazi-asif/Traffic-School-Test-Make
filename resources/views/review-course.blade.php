<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Your Course</title>
    <link href="/css/themes.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .review-card {
            background-color: var(--bg-card);
            border-color: var(--border);
        }
        
        .review-textarea {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        
        .review-textarea::placeholder {
            color: var(--text-secondary);
        }
        
        .review-textarea:focus {
            border-color: var(--accent);
            outline: none;
        }
        
        .star-rating {
            cursor: pointer;
        }
        
        .star-rating span {
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s;
            font-size: 3rem;
        }
        
        .star-rating span.text-yellow-400 {
            color: #fbbf24 !important;
        }
        
        .btn-submit {
            background-color: var(--accent);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .btn-submit:hover {
            background-color: var(--hover);
        }
        
        .btn-cancel {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            transition: var(--transition);
        }
        
        .btn-cancel:hover {
            background-color: var(--hover);
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="review-card rounded-lg shadow-lg p-8 border">
                <h1 class="text-4xl font-bold mb-3">Rate Your Course</h1>
                <p class="text-lg opacity-75 mb-8">{{ $courseName }}</p>

                <form action="/submit-review" method="POST" class="space-y-8">
                    @csrf
                    <input type="hidden" name="enrollment_id" value="{{ $enrollmentId }}">
                    <input type="hidden" name="course_name" value="{{ $courseName }}">
                    <input type="hidden" name="completion_date" value="{{ $completionDate }}">
                    <input type="hidden" name="score" value="{{ $score }}">

                    <!-- Rating Section -->
                    <div>
                        <label class="block text-xl font-semibold mb-6">How would you rate this course?</label>
                        <div class="flex gap-6 justify-center" id="star-container">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="star-rating">
                                    <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" required>
                                    <span class="peer-checked:text-yellow-400">★</span>
                                </label>
                            @endfor
                        </div>
                        @error('rating')
                            <p class="text-red-500 text-sm mt-4">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Feedback Section -->
                    <div>
                        <label for="feedback" class="block text-xl font-semibold mb-4">Your Feedback (Optional)</label>
                        <textarea name="feedback" id="feedback" rows="6" 
                            class="review-textarea w-full border rounded-lg p-4 text-base"
                            placeholder="Share your experience with this course..."></textarea>
                        @error('feedback')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="btn-submit flex-1 py-4 rounded-lg font-semibold text-lg">
                            Submit & Get Certificate
                        </button>
                        <button type="button" onclick="history.back()" class="btn-cancel flex-1 py-4 rounded-lg font-semibold text-lg">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const stars = document.querySelectorAll('.star-rating input');
        const container = document.getElementById('star-container');
        
        stars.forEach((star) => {
            star.addEventListener('change', () => {
                const rating = parseInt(star.value);
                const allSpans = container.querySelectorAll('span');
                allSpans.forEach((span, i) => {
                    if (i < rating) {
                        span.classList.add('text-yellow-400');
                    } else {
                        span.classList.remove('text-yellow-400');
                    }
                });
            });
        });
    </script>
</body>
</html>

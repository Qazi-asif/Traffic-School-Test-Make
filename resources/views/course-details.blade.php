<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title }} - Course Details</title>
    <link href="/css/themes.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .details-card {
            background-color: var(--bg-card);
            border-color: var(--border);
        }
        
        .rating-stars {
            color: #fbbf24;
        }
        
        .review-item {
            background-color: var(--bg-secondary);
            border-color: var(--border);
        }
        
        .btn-enroll {
            background-color: var(--accent);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .btn-enroll:hover {
            background-color: var(--hover);
        }
        
        .btn-back {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            transition: var(--transition);
        }
        
        .btn-back:hover {
            background-color: var(--hover);
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <button onclick="history.back()" class="btn-back px-6 py-2 rounded-lg font-semibold mb-6">
                ← Back to Courses
            </button>

            <!-- Course Header -->
            <div class="details-card rounded-lg shadow-lg p-8 border mb-8">
                <h1 class="text-4xl font-bold mb-4">{{ $course->title }}</h1>
                <p class="text-lg opacity-75 mb-6">{{ $course->description }}</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div>
                        <p class="text-sm opacity-75">State</p>
                        <p class="text-xl font-semibold">{{ $course->state_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm opacity-75">Duration</p>
                        <p class="text-xl font-semibold">
                            @if ($course->total_duration && $course->total_duration > 0)
                                {{ $course->total_duration }} min
                            @elseif ($course->duration && $course->duration > 0)
                                {{ $course->duration }} min
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm opacity-75">Price</p>
                        <p class="text-xl font-semibold">${{ number_format($course->price, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm opacity-75">Pass Score</p>
                        <p class="text-xl font-semibold">{{ $course->min_pass_score }}%</p>
                    </div>
                </div>

                <button onclick="window.location.href='/payment?course_id={{ $course->id }}&table={{ $course->table }}'" class="btn-enroll px-8 py-3 rounded-lg font-semibold text-lg">
                    Enroll Now
                </button>
            </div>

            <!-- Ratings & Reviews Section -->
            <div class="details-card rounded-lg shadow-lg p-8 border">
                <h2 class="text-2xl font-bold mb-6">Ratings & Reviews</h2>
                
                <!-- Average Rating -->
                <div class="mb-8 pb-8 border-b" style="border-color: var(--border);">
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-5xl font-bold">{{ $avgRating }}</p>
                            <p class="text-sm opacity-75">out of 5</p>
                        </div>
                        <div>
                            <div class="rating-stars text-4xl">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= round($avgRating))
                                        ★
                                    @else
                                        ☆
                                    @endif
                                @endfor
                            </div>
                            <p class="text-sm opacity-75 mt-2">{{ $totalReviews }} review{{ $totalReviews !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                @if ($reviews->count() > 0)
                    <div class="space-y-4">
                        @foreach ($reviews as $review)
                            <div class="review-item rounded-lg p-6 border">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="font-semibold">{{ $review->user->name ?? 'Anonymous' }}</p>
                                        <p class="text-sm opacity-75">{{ $review->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="rating-stars text-lg">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $review->rating)
                                                ★
                                            @else
                                                ☆
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                @if ($review->feedback)
                                    <p class="opacity-90">{{ $review->feedback }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center opacity-75 py-8">No reviews yet. Be the first to review this course!</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>

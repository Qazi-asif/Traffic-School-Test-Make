<?php

use App\Http\Controllers\CourseTimerController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FlhsmvController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\SupportTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| New Modules API Routes
|--------------------------------------------------------------------------
| Add these routes to your routes/api.php file
*/

// FLHSMV Integration Routes
Route::prefix('flhsmv')->middleware('auth')->group(function () {
    Route::post('/submit-completion', [FlhsmvController::class, 'submitCompletion']);
    Route::get('/submission/{id}', [FlhsmvController::class, 'getSubmissionStatus']);
    Route::get('/submissions', [FlhsmvController::class, 'listSubmissions']);
    Route::post('/retry/{id}', [FlhsmvController::class, 'retrySubmission']);
});

// Payment Gateway Routes
Route::prefix('payment')->group(function () {
    // Stripe
    Route::post('/stripe/create-intent', [PaymentGatewayController::class, 'createStripePayment'])->middleware('auth');
    Route::post('/stripe/process', [PaymentGatewayController::class, 'processStripePayment'])->middleware('auth');
    Route::post('/stripe/webhook', [PaymentGatewayController::class, 'stripeWebhook']);

    // PayPal
    Route::post('/paypal/create-order', [PaymentGatewayController::class, 'createPayPalOrder'])->middleware('auth');
    Route::post('/paypal/capture', [PaymentGatewayController::class, 'capturePayPalOrder'])->middleware('auth');
    Route::post('/paypal/webhook', [PaymentGatewayController::class, 'paypalWebhook']);

    // Dummy Payment (for testing)
    Route::post('/dummy/process', [PaymentGatewayController::class, 'processDummyPayment'])->middleware('auth');
});

// Course Timer Routes
Route::prefix('timer')->middleware('web')->group(function () {
    Route::post('/start', [CourseTimerController::class, 'startTimer'])->middleware('auth');
    Route::post('/update', [CourseTimerController::class, 'updateTimer'])->middleware('auth');
    Route::post('/heartbeat', [CourseTimerController::class, 'heartbeat'])->middleware('auth');
    Route::post('/validate', [CourseTimerController::class, 'validateSession'])->middleware('auth');
    Route::post('/violation', [CourseTimerController::class, 'recordViolation'])->middleware('auth');
    Route::post('/bypass', [CourseTimerController::class, 'bypassTimer'])->middleware('auth');
    Route::post('/check-status', [CourseTimerController::class, 'checkTimerStatus'])->middleware('auth');
    Route::post('/configure', [CourseTimerController::class, 'configureTimer']);
    Route::post('/toggle/{id}', function ($id) {
        $timer = \App\Models\CourseTimer::findOrFail($id);
        $timer->is_enabled = ! $timer->is_enabled;
        $timer->save();

        return response()->json(['success' => true, 'timer' => $timer]);
    });
    Route::delete('/delete/{id}', function ($id) {
        $timer = \App\Models\CourseTimer::findOrFail($id);
        $timer->delete();

        return response()->json(['success' => true]);
    });
    Route::get('/list', function () {
        try {
            $timers = \App\Models\CourseTimer::all();

            // Load chapter info based on chapter_type
            $timers->each(function ($timer) {
                if ($timer->chapter_type === 'chapters') {
                    $chapter = \App\Models\Chapter::find($timer->chapter_id);
                    if ($chapter) {
                        $course = \App\Models\FloridaCourse::find($chapter->course_id);
                        $timer->chapter = (object) [
                            'id' => $chapter->id,
                            'title' => $chapter->title.' - '.($course->title ?? 'Unknown').' (Florida)',
                        ];
                    }
                } else {
                    $chapter = \App\Models\Chapter::find($timer->chapter_id);
                    if ($chapter) {
                        $course = \App\Models\Course::find($chapter->course_id);
                        $timer->chapter = (object) [
                            'id' => $chapter->id,
                            'title' => $chapter->title.' - '.($course->title ?? 'Unknown'),
                        ];
                    }
                }
            });

            return response()->json($timers);
        } catch (\Exception $e) {
            \Log::error('Timer list error: '.$e->getMessage());

            return response()->json([]);
        }
    });
});

// FAQ Routes
Route::prefix('faqs')->middleware('web')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::post('/', [FaqController::class, 'store']);
    Route::put('/{id}', [FaqController::class, 'update']);
    Route::delete('/{id}', [FaqController::class, 'destroy']);
});

// FAQ alias
Route::get('/faq', [FaqController::class, 'index']);

// Counties API
Route::prefix('counties')->middleware('web')->group(function () {
    Route::get('/', function () {
        try {
            $counties = \App\Models\County::orderBy('name')->get();

            return response()->json($counties);
        } catch (\Exception $e) {
            \Log::error('Counties API error: '.$e->getMessage());

            return response()->json([]);
        }
    });

    Route::get('/{id}', function ($id) {
        try {
            $county = \App\Models\County::findOrFail($id);

            return response()->json($county);
        } catch (\Exception $e) {
            return response()->json(['error' => 'County not found'], 404);
        }
    });

    Route::post('/', function (Request $request) {
        try {
            \Log::info('=== County Create START ===');
            \Log::info('Request data:', $request->all());

            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255',
                'is_active' => 'boolean',
            ]);

            \Log::info('Validation passed');

            $county = \App\Models\County::create([
                'name' => $request->name,
                'code' => $request->code,
                'state_code' => 'FL',
                'is_active' => $request->is_active ?? true,
            ]);

            \Log::info('County created:', $county->toArray());
            \Log::info('=== County Create END ===');

            return response()->json(['success' => true, 'county' => $county]);
        } catch (\Exception $e) {
            \Log::error('=== County Create ERROR ===');
            \Log::error('Error: '.$e->getMessage());
            \Log::error('File: '.$e->getFile().':'.$e->getLine());

            return response()->json(['error' => $e->getMessage()], 422);
        }
    });

    Route::put('/{id}', function (Request $request, $id) {
        try {
            $county = \App\Models\County::findOrFail($id);
            $county->update($request->all());

            return response()->json(['success' => true, 'county' => $county]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    });

    Route::delete('/{id}', function ($id) {
        try {
            $county = \App\Models\County::findOrFail($id);
            $county->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    });
});

// Support Ticket Routes
Route::prefix('support/tickets')->middleware('auth')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index']);
    Route::post('/', [SupportTicketController::class, 'store']);
    Route::get('/{id}', [SupportTicketController::class, 'show']);
    Route::post('/{id}/reply', [SupportTicketController::class, 'reply']);
    Route::put('/{id}/status', [SupportTicketController::class, 'updateStatus']);
});

// Alternative route for support/tickets (web middleware)
Route::prefix('support')->middleware('web')->group(function () {
    Route::get('/tickets', [SupportTicketController::class, 'index']);
    Route::post('/tickets', [SupportTicketController::class, 'store']);
});

// Coupon API routes
Route::prefix('coupons')->middleware('web')->group(function () {
    Route::post('/apply', [App\Http\Controllers\CouponController::class, 'apply']);
    Route::post('/use', [App\Http\Controllers\CouponController::class, 'use']);
});

// Helper Routes for Dropdowns (no auth required as they're called from authenticated pages)
Route::get('/chapters', function () {
    try {
        // Get chapters from both tables
        $chapters = \App\Models\Chapter::select('id', 'title', 'course_id')
            ->get()
            ->map(function ($ch) {
                $ch->type = 'chapters';
                $courseName = \App\Models\Course::find($ch->course_id)?->title ?? 'Unknown';
                $ch->display_title = $ch->title.' - '.$courseName;

                return $ch;
            });

        $courseChapters = \App\Models\Chapter::select('id', 'title', 'course_id')
            ->where('course_table', 'florida_courses')
            ->get()
            ->map(function ($ch) {
                $ch->type = 'chapters';
                $courseName = \App\Models\FloridaCourse::find($ch->course_id)?->title ?? 'Unknown';
                $ch->display_title = $ch->title.' - '.$courseName.' (Florida)';

                return $ch;
            });

        $allChapters = $chapters->concat($courseChapters);

        return response()->json($allChapters);
    } catch (\Exception $e) {
        \Log::error('Chapters API error: '.$e->getMessage());
        \Log::error('Stack: '.$e->getTraceAsString());

        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/courses', function () {
    try {
        \Log::info('=== Courses API START ===');

        // Get regular courses
        $courses = \App\Models\Course::select('id', 'course_type', 'description')
            ->get()
            ->map(function ($c) {
                $c->name = $c->course_type.' - '.$c->description;

                return $c;
            });

        // Get Florida courses
        $floridaCourses = \App\Models\FloridaCourse::select('id', 'title')
            ->get()
            ->map(function ($c) {
                $c->name = $c->title.' (Florida)';

                return $c;
            });

        $allCourses = $courses->concat($floridaCourses);

        \Log::info('Courses loaded: '.$allCourses->count());
        \Log::info('=== Courses API END ===');

        return response()->json($allCourses);
    } catch (\Exception $e) {
        \Log::error('=== Courses API ERROR ===');
        \Log::error('Error: '.$e->getMessage());
        \Log::error('File: '.$e->getFile().':'.$e->getLine());

        return response()->json([]);
    }
});

// Question Banks API
Route::prefix('question-banks')->middleware('web')->group(function () {
    Route::get('/', function (Request $request) {
        try {
            \Log::info('=== Question Banks GET START ===');
            \Log::info('Query params:', $request->all());

            $query = \App\Models\QuestionBank::query();

            if ($request->course_id) {
                $query->where('course_id', $request->course_id);
            }

            $questions = $query->orderBy('created_at', 'desc')->get();

            \Log::info('Questions loaded: '.$questions->count());
            \Log::info('=== Question Banks GET END ===');

            return response()->json($questions);
        } catch (\Exception $e) {
            \Log::error('=== Question Banks GET ERROR ===');
            \Log::error('Error: '.$e->getMessage());
            \Log::error('File: '.$e->getFile().':'.$e->getLine());

            return response()->json([]);
        }
    });

    Route::get('/{id}', function ($id) {
        try {
            $question = \App\Models\QuestionBank::findOrFail($id);

            return response()->json($question);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Question not found'], 404);
        }
    });

    Route::post('/', function (Request $request) {
        try {
            \Log::info('=== Question Bank Create START ===');
            \Log::info('Request data:', $request->all());

            $request->validate([
                'course_id' => 'required|integer',
                'question_text' => 'required|string',
                'options' => 'required|array',
                'correct_answer' => 'required|string',
            ]);

            \Log::info('Validation passed');

            $question = \App\Models\QuestionBank::create([
                'course_id' => $request->course_id,
                'question_text' => $request->question_text,
                'options' => json_encode($request->options),
                'correct_answer' => $request->correct_answer,
                'category' => $request->category,
            ]);

            \Log::info('Question created:', $question->toArray());
            \Log::info('=== Question Bank Create END ===');

            return response()->json(['success' => true, 'question' => $question]);
        } catch (\Exception $e) {
            \Log::error('=== Question Bank Create ERROR ===');
            \Log::error('Error: '.$e->getMessage());
            \Log::error('File: '.$e->getFile().':'.$e->getLine());

            return response()->json(['error' => $e->getMessage()], 422);
        }
    });

    Route::put('/{id}', function (Request $request, $id) {
        try {
            $question = \App\Models\QuestionBank::findOrFail($id);
            $question->update([
                'course_id' => $request->course_id,
                'question_text' => $request->question_text,
                'options' => json_encode($request->options),
                'correct_answer' => $request->correct_answer,
                'category' => $request->category,
            ]);

            return response()->json(['success' => true, 'question' => $question]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    });

    Route::delete('/{id}', function ($id) {
        try {
            $question = \App\Models\QuestionBank::findOrFail($id);
            $question->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    });
});

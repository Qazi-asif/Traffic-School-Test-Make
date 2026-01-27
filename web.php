<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// File serving route with different pattern
Route::get('files/{filename}', function ($filename) {
    $path = storage_path('app/public/course-media/'.$filename);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

// Direct file serving route
Route::get('storage/course-media/1761175955_indian-man-7061278_640__1_.jpg', function () {
    $path = storage_path('app/public/course-media/1761175955_indian-man-7061278_640__1_.jpg');

    return response()->file($path);
});

// Debug route to test file access
Route::get('test-file', function () {
    $filename = '1761175955_indian-man-7061278_640__1_.jpg';
    $path = storage_path('app/public/course-media/'.$filename);

    return response()->json([
        'filename' => $filename,
        'path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0,
        'storage_path' => storage_path('app/public/course-media/'),
        'files_in_dir' => scandir(storage_path('app/public/course-media/')),
    ]);
});

// Storage route for serving files
Route::get('storage/course-media/{filename}', function ($filename) {
    $path = storage_path('app/public/course-media/'.$filename);

    \Log::info('Storage route hit', ['filename' => $filename, 'path' => $path, 'exists' => file_exists($path)]);

    if (! file_exists($path)) {
        \Log::error('File not found', ['path' => $path]);
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

Route::get('/', function () {
    return redirect('/dashboard');
});

// Include DICDS routes
Route::prefix('dicds')->group(base_path('routes/dicds.php'));

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Test route to check authentication status
Route::get('/auth-test', function () {
    \Log::info('Auth test route accessed', [
        'is_authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session_token' => session('jwt_token'),
    ]);

    return response()->json([
        'is_authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session_token' => session('jwt_token'),
        'session_id' => session()->getId(),
    ]);
});

// Test route with built-in auth middleware
Route::get('/auth-test-middleware', function () {
    return response()->json([
        'message' => 'You are authenticated!',
        'user' => auth()->user(),
    ]);
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

Route::get('/courses', function () {
    return view('courses');
})->middleware('auth');

Route::get('/certificates/verify/{hash}', function ($hash) {
    $certificate = \App\Models\FloridaCertificate::where('verification_hash', $hash)->first();

    if (! $certificate) {
        abort(404, 'Certificate not found');
    }

    return view('certificates.verify', compact('certificate'));
});

// Serve storage files
Route::get('/files/{path}', function ($path) {
    $filePath = storage_path('app/public/'.$path);

    if (! file_exists($filePath)) {
        // Try course-media subdirectory
        $filePath = storage_path('app/public/course-media/'.$path);
        if (! file_exists($filePath)) {
            abort(404);
        }
    }

    return response()->file($filePath);
})->where('path', '.*');

Route::get('/generate-certificates', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }

    $userId = auth()->id();

    // Find only truly completed enrollments - exclude active status
    $completedEnrollments = \App\Models\UserCourseEnrollment::with(['floridaCourse', 'user'])
        ->where('user_id', $userId)
        ->where('status', 'completed')
        ->where('progress_percentage', '>=', 100)
        ->whereNotNull('completed_at')
        ->whereDoesntHave('floridaCertificate')
        ->get();

    $generated = 0;

    foreach ($completedEnrollments as $enrollment) {
        try {
            $year = date('Y');
            $lastCertificate = \App\Models\FloridaCertificate::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastCertificate ?
                (int) substr($lastCertificate->dicds_certificate_number, -6) + 1 : 1;

            $certificateNumber = 'FL'.$year.str_pad($sequence, 6, '0', STR_PAD_LEFT);

            \App\Models\FloridaCertificate::create([
                'enrollment_id' => $enrollment->id,
                'dicds_certificate_number' => $certificateNumber,
                'student_name' => $enrollment->user->first_name.' '.$enrollment->user->last_name,
                'course_name' => $enrollment->floridaCourse->title ?? 'Florida Traffic School Course',
                'completion_date' => $enrollment->completed_at,
                'verification_hash' => \Illuminate\Support\Str::random(32),
                'status' => 'generated',
            ]);

            $generated++;

        } catch (\Exception $e) {
            \Log::error('Certificate generation error: '.$e->getMessage());
        }
    }

    return redirect('/my-certificates')->with('success', "Generated {$generated} certificates for completed courses.");
})->middleware('auth');

Route::get('/my-certificates', function () {
    return view('my-certificates');
})->middleware('auth')->name('my-certificates');

Route::get('/my-enrollments', function () {
    return view('my-enrollments');
})->middleware('auth');

Route::get('/course-player/{enrollmentId}', function () {
    return view('course-player');
})->middleware('auth');

Route::get('/course-player', function () {
    return view('course-player');
})->middleware('auth');

Route::get('/profile', function () {
    return view('profile');
})->middleware('auth');

Route::get('/my-payments', function () {
    return view('my-payments');
})->middleware('auth');

Route::get('/create-course', function () {
    return view('create-course');
})->middleware('auth', 'role:super-admin,admin');

// Web routes for course operations (using session auth)
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::post('/web/courses', [App\Http\Controllers\CourseController::class, 'storeWeb']);
    Route::match(['PUT', 'POST'], '/web/courses/{course}', [App\Http\Controllers\CourseController::class, 'updateWeb']);
    Route::post('/web/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
    Route::match(['PUT', 'POST'], '/web/chapters/{chapter}', [App\Http\Controllers\ChapterController::class, 'updateWeb']);
    Route::delete('/web/chapters/{chapter}', [App\Http\Controllers\ChapterController::class, 'destroyWeb']);
});

// Payment routes
Route::middleware('auth')->group(function () {
    Route::get('/payment/create', [App\Http\Controllers\PaymentPageController::class, 'create'])->name('payment.create');
    Route::get('/payment/{enrollment}', [App\Http\Controllers\PaymentPageController::class, 'show'])->name('payment.show');
    Route::post('/payment/stripe', [App\Http\Controllers\PaymentPageController::class, 'processStripe']);
    Route::post('/payment/paypal', [App\Http\Controllers\PaymentPageController::class, 'processPaypal']);
    Route::get('/payment/success', [App\Http\Controllers\PaymentPageController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [App\Http\Controllers\PaymentPageController::class, 'cancel'])->name('payment.cancel');
});

// Web routes for enrollments (using session auth with payment middleware)
Route::middleware(['auth', 'payment'])->group(function () {
    Route::post('/web/enrollments', [App\Http\Controllers\EnrollmentController::class, 'storeWeb'])->name('enrollment.store');
    Route::get('/web/my-enrollments', [App\Http\Controllers\EnrollmentController::class, 'myEnrollmentsWeb']);
});

// Web routes for user profile (using session auth)
Route::middleware('auth')->group(function () {
    Route::get('/web/user', [App\Http\Controllers\AuthController::class, 'userWeb']);
    Route::put('/web/user', [App\Http\Controllers\AuthController::class, 'updateProfileWeb']);
    Route::get('/web/enrollments/{enrollment}', [App\Http\Controllers\EnrollmentController::class, 'showWeb']);
    Route::get('/web/courses', [App\Http\Controllers\CourseController::class, 'indexWeb']);
    Route::get('/web/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::post('/web/enrollments/{enrollment}/complete-chapter/{chapter}', [App\Http\Controllers\ProgressController::class, 'completeChapterWeb']);
    Route::get('/web/my-payments', [App\Http\Controllers\PaymentController::class, 'myPaymentsWeb']);
});

// Web routes for admin (using session auth)
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/web/users', [App\Http\Controllers\UserController::class, 'indexWeb']);
    Route::post('/web/users', [App\Http\Controllers\UserController::class, 'storeWeb']);
    Route::put('/web/users/{user}', [App\Http\Controllers\UserController::class, 'updateWeb']);
    Route::delete('/web/users/{user}', [App\Http\Controllers\UserController::class, 'destroyWeb']);
    Route::get('/web/enrollments', [App\Http\Controllers\EnrollmentController::class, 'indexWeb']);
    Route::get('/web/admin/reports', [App\Http\Controllers\ReportController::class, 'indexWeb']);
    Route::get('/web/admin/reports/generate', [App\Http\Controllers\ReportController::class, 'generateWeb']);
    Route::get('/web/admin/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStatsWeb']);

    // Admin payments CRUD
    Route::get('/web/admin/payments', [App\Http\Controllers\PaymentController::class, 'index']);
    Route::post('/web/admin/payments', [App\Http\Controllers\PaymentController::class, 'store']);
    Route::get('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'show']);
    Route::put('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'update']);
    Route::delete('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'destroy']);
    Route::post('/web/admin/payments/{payment}/refund', [App\Http\Controllers\PaymentController::class, 'refund']);
    Route::get('/web/admin/payments/{payment}/pdf', [App\Http\Controllers\PaymentController::class, 'downloadPDF']);
    Route::post('/web/admin/payments/{payment}/email', [App\Http\Controllers\PaymentController::class, 'emailReceipt']);

    // Admin invoices CRUD
    Route::get('/web/admin/invoices', [App\Http\Controllers\InvoiceController::class, 'index']);
    Route::post('/web/admin/invoices', [App\Http\Controllers\InvoiceController::class, 'store']);
    Route::get('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'show']);
    Route::put('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'update']);
    Route::delete('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'destroy']);
    Route::post('/web/admin/invoices/{invoice}/send', [App\Http\Controllers\InvoiceController::class, 'send']);
    Route::get('/web/admin/invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'download']);
    Route::post('/web/admin/invoices/{invoice}/email', [App\Http\Controllers\InvoiceController::class, 'emailInvoice']);

    // Admin certificates CRUD
    Route::get('/web/admin/certificates', [App\Http\Controllers\CertificateController::class, 'index']);
    Route::post('/web/admin/certificates', [App\Http\Controllers\CertificateController::class, 'store']);
    Route::get('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'show']);
    Route::put('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'update']);
    Route::delete('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'destroy']);
    Route::post('/web/admin/certificates/{certificate}/submit-to-state', [App\Http\Controllers\CertificateController::class, 'submitToState']);
    Route::get('/web/admin/certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'download']);
    Route::post('/web/admin/certificates/{certificate}/email', [App\Http\Controllers\CertificateController::class, 'emailCertificate']);

    // State Integration Web Routes
    Route::get('/web/admin/state-configurations', [App\Http\Controllers\StateConfigurationController::class, 'index']);
    Route::post('/web/admin/state-configurations', [App\Http\Controllers\StateConfigurationController::class, 'store']);
    Route::get('/web/admin/state-configurations/{stateCode}/test-connection', [App\Http\Controllers\StateConfigurationController::class, 'testConnection']);
    Route::delete('/web/admin/state-configurations/{stateConfiguration}', [App\Http\Controllers\StateConfigurationController::class, 'destroy']);

    Route::get('/web/admin/submission-queue/stats', [App\Http\Controllers\StateSubmissionController::class, 'stats']);
    Route::post('/web/admin/submission-queue/process-pending', [App\Http\Controllers\StateSubmissionController::class, 'processPending']);
    Route::get('/web/admin/submission-queue', [App\Http\Controllers\StateSubmissionController::class, 'index']);
    Route::post('/web/admin/submission-queue/{id}/retry', [App\Http\Controllers\StateSubmissionController::class, 'retry']);

    // Email Templates Web Routes
    Route::get('/web/admin/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'index']);
    Route::post('/web/admin/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'store']);
    Route::get('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'show']);
    Route::put('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'update']);
    Route::delete('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'destroy']);
    Route::post('/web/admin/email-templates/{emailTemplate}/test', [App\Http\Controllers\EmailTemplateController::class, 'test']);

    Route::get('/web/admin/email-logs', [App\Http\Controllers\EmailLogController::class, 'index']);
    Route::get('/web/admin/email-logs/stats', [App\Http\Controllers\EmailLogController::class, 'stats']);
});

// Public certificate verification
Route::get('/certificates/{verificationHash}/verify', [App\Http\Controllers\CertificateController::class, 'verify']);

Route::get('/admin/florida-courses', function () {
    return view('admin.florida-courses');
});

Route::get('/admin/florida-certificates', function () {
    return view('admin.florida-certificates');
});

Route::get('/admin/dicds-orders', function () {
    return view('admin.dicds-orders');
});

Route::get('/admin/florida-dashboard', function () {
    return view('admin.florida-dashboard');
});

Route::get('/admin/certificate-inventory', function () {
    return view('admin.certificate-inventory');
});

Route::get('/admin/compliance-reports', function () {
    return view('admin.compliance-reports');
});

Route::get('/admin/florida-payments', function () {
    return view('admin.florida-payments');
});

Route::get('/admin/fee-remittances', function () {
    return view('admin.fee-remittances');
});

Route::get('/admin/pricing-rules', function () {
    return view('admin.pricing-rules');
});

Route::get('/admin/florida-email-templates', function () {
    return view('admin.florida-email-templates');
});

Route::get('/admin/dicds-submissions', function () {
    return view('admin.dicds-submissions');
});

Route::get('/admin/certificate-lookup', function () {
    return view('admin.certificate-lookup');
});

Route::get('/admin/school-activity', function () {
    return view('admin.school-activity');
});

Route::get('/admin/web-service-info', function () {
    return view('admin.web-service-info');
});

Route::get('/admin/legal-documents', function () {
    return view('admin.legal-documents');
});

Route::get('/admin/copyright-protection', function () {
    return view('admin.copyright-protection');
});

Route::get('/admin/user-consents', function () {
    return view('admin.user-consents');
});

// Web routes for DICDS order management
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/web/dicds-orders', [App\Http\Controllers\FloridaApprovalController::class, 'indexWeb']);
    Route::post('/web/dicds-orders', [App\Http\Controllers\DicdsOrderController::class, 'storeWeb']);
    Route::put('/web/dicds-orders/{id}/amend', [App\Http\Controllers\DicdsOrderAmendmentController::class, 'amendWeb']);
    Route::post('/web/dicds-orders/{id}/generate-receipt', [App\Http\Controllers\DicdsReceiptController::class, 'generateWeb']);
    Route::put('/web/dicds-orders/{id}/update-approval', [App\Http\Controllers\FloridaApprovalController::class, 'updateApprovalWeb']);
    Route::get('/web/florida-schools', [App\Http\Controllers\FloridaSchoolController::class, 'indexWeb']);
    Route::get('/web/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'indexWeb']);
    Route::get('/api/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'indexWeb']);
    Route::put('/api/florida-courses/{id}', [App\Http\Controllers\FloridaCourseController::class, 'updateWeb']);
    Route::get('/api/florida-certificates', function () {
        $certificates = \App\Models\FloridaCertificate::orderBy('created_at', 'desc')->get();

        return response()->json($certificates);
    });
    Route::get('/api/admin/florida-dashboard/stats', function () {
        $inventory = [
            ['course_type' => 'BDI', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
            ['course_type' => 'ADI', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
            ['course_type' => 'TLSAE', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
        ];

        return response()->json([
            'available' => 0,
            'used_this_month' => \App\Models\FloridaCertificate::whereMonth('completion_date', now()->month)->count(),
            'pending' => 0,
            'failed' => 0,
            'inventory' => $inventory,
            'recent_submissions' => \App\Models\FloridaCertificate::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    });

    Route::get('/api/admin/certificate-inventory', function () {
        return response()->json(\App\Models\CertificateInventory::all());
    });

    Route::get('/api/admin/florida-reports', function () {
        return response()->json(\App\Models\FloridaComplianceReport::with('generator')->orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/admin/florida-reports/generate', function (Illuminate\Http\Request $request) {
        $report = \App\Models\FloridaComplianceReport::create([
            'report_type' => $request->report_type,
            'report_date' => now(),
            'data_range_start' => $request->data_range_start,
            'data_range_end' => $request->data_range_end,
            'generated_by' => auth()->id(),
        ]);

        return response()->json($report);
    });

    Route::get('/api/admin/florida-reports/{id}/download', function ($id) {
        $report = \App\Models\FloridaComplianceReport::findOrFail($id);

        return response()->json(['message' => 'Download functionality to be implemented']);
    });

    Route::get('/api/florida-payments', function () {
        $payments = \App\Models\FloridaPayment::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'payments' => $payments,
            'total_revenue' => $payments->sum('total_amount'),
            'florida_fees' => $payments->sum('florida_assessment_fee'),
            'pending_remittance' => $payments->where('florida_fee_remitted', false)->sum('florida_assessment_fee'),
        ]);
    });

    Route::get('/api/florida-remittances', function () {
        return response()->json(\App\Models\FloridaFeeRemittance::with('submitter')->orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/florida-remittances', function (Illuminate\Http\Request $request) {
        $remittance = \App\Models\FloridaFeeRemittance::create([
            'remittance_date' => $request->remittance_date,
            'total_assessment_fees' => $request->total_assessment_fees,
            'total_courses' => $request->total_courses,
            'payment_method' => $request->payment_method,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return response()->json($remittance);
    });

    Route::post('/api/florida-remittances/{id}/submit', function (Illuminate\Http\Request $request, $id) {
        $remittance = \App\Models\FloridaFeeRemittance::findOrFail($id);
        $remittance->update([
            'florida_reference_number' => $request->florida_reference_number,
            'processed_by_florida' => true,
            'processed_at' => now(),
        ]);

        return response()->json($remittance);
    });

    Route::get('/api/pricing-rules', function () {
        return response()->json(\App\Models\FloridaPricingRule::where('is_active', true)->get());
    });

    Route::post('/api/pricing-rules', function (Illuminate\Http\Request $request) {
        $rule = \App\Models\FloridaPricingRule::create($request->all());

        return response()->json($rule);
    });

    Route::get('/api/florida-email-templates', function () {
        return response()->json(\App\Models\FloridaEmailTemplate::orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/florida-email-templates', function (Illuminate\Http\Request $request) {
        $template = \App\Models\FloridaEmailTemplate::create(array_merge($request->all(), ['created_by' => auth()->id()]));

        return response()->json($template);
    });

    Route::post('/api/florida-email-templates/{id}/test', function (Illuminate\Http\Request $request, $id) {
        $template = \App\Models\FloridaEmailTemplate::findOrFail($id);
        \App\Services\FloridaMailService::send($request->email, $template->subject, $template->content);

        return response()->json(['message' => 'Test email sent']);
    });
    Route::get('/api/florida-certificates/{id}/view', function ($id) {
        $certificate = \App\Models\FloridaCertificate::findOrFail($id);

        return view('certificates.florida-certificate', compact('certificate'));
    });
    Route::get('/api/florida-certificates/{id}/download', function ($id) {
        $certificate = \App\Models\FloridaCertificate::findOrFail($id);
        $html = view('certificates.florida-certificate', compact('certificate'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificate->dicds_certificate_number.'.html"');
    });
    Route::get('/api/email-logs-stats', function () {
        return response()->json([
            'total_sent' => 0,
            'total_failed' => 0,
            'total_pending' => 0,
        ]);
    });
    Route::get('/api/email-logs', function () {
        return response()->json([]);
    });
    Route::post('/api/notifications/send', function (Request $request) {
        $user = \App\Models\User::where('email', $request->user_email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Create notification record (if you have a notifications table)
        // For now, just return success
        return response()->json([
            'message' => 'Notification sent successfully',
            'user' => $user->name,
            'type' => $request->type,
        ]);
    });
    Route::get('/api/pwa/manifest', function (Request $request) {
        $theme = $request->cookie('theme', 'dark-blue');

        $themes = [
            'dark-blue' => [
                'background_color' => '#1e3a5f',
                'theme_color' => '#4a90e2',
            ],
            'dark' => [
                'background_color' => '#1a1a1a',
                'theme_color' => '#ffffff',
            ],
            'light' => [
                'background_color' => '#f0f8ff',
                'theme_color' => '#87ceeb',
            ],
        ];

        $colors = $themes[$theme] ?? $themes['dark-blue'];

        return response()->json([
            'name' => 'Traffic School',
            'short_name' => 'TrafficSchool',
            'description' => 'Online Traffic School Management System',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $colors['background_color'],
            'theme_color' => $colors['theme_color'],
            'icons' => [
                [
                    'src' => '/favicon.ico',
                    'sizes' => '64x64',
                    'type' => 'image/x-icon',
                ],
            ],
        ]);
    });
    Route::get('/web/data-export/download', function (Request $request) {
        $type = $request->query('type');
        $format = $request->query('format', 'html');
        $user = auth()->user();

        $data = [
            'user' => $user,
            'type' => $type,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        // Generate HTML content
        $html = view('exports.data-export', $data)->render();

        if ($format === 'pdf') {
            // For PDF, return HTML with print-friendly CSS
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="export-'.$type.'-'.time().'.html"');
        }

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="export-'.$type.'-'.time().'.html"');
    });
    Route::get('/api/florida-courses/{id}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::post('/api/florida-courses/{id}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
    Route::get('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'show']);
    Route::put('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'updateWeb']);
    Route::delete('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'destroyWeb']);
    Route::get('/api/chapters/{id}/questions', [App\Http\Controllers\QuestionController::class, 'index']);
    Route::post('/api/chapters/{id}/questions', [App\Http\Controllers\QuestionController::class, 'store']);
    Route::get('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'show']);
    Route::put('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'destroy']);

    // Certificate Lookup
    Route::post('/web/certificate-lookup', [App\Http\Controllers\CertificateLookupController::class, 'search']);
    Route::post('/web/certificate-lookup/{id}/reprint', [App\Http\Controllers\CertificateLookupController::class, 'reprint']);

    // School Activity Reports
    Route::post('/web/school-activity-reports/generate', [App\Http\Controllers\SchoolActivityController::class, 'generate']);
    Route::get('/web/school-activity-reports', [App\Http\Controllers\SchoolActivityController::class, 'index']);

    // Web Service Info
    Route::get('/web/dicds-web-service-info', function () {
        return response()->json(App\Models\DicdsWebServiceInfo::with('school')->get());
    });

    // Legal Documents
    Route::get('/web/legal-documents', [App\Http\Controllers\LegalDocumentController::class, 'index']);
    Route::post('/web/legal-documents', [App\Http\Controllers\LegalDocumentController::class, 'store']);

    // Copyright Protection
    Route::get('/web/copyright-protection/stats', [App\Http\Controllers\CopyrightProtectionController::class, 'stats']);

    // User Consents
    Route::get('/web/user-consents', function () {
        return response()->json(App\Models\UserLegalConsent::with(['user', 'document'])->get());
    });
});

Route::get('/certificate-verification', function () {
    return view('certificate-verification');
});

// Admin routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    });
    Route::get('/admin/enrollments', function () {
        return view('admin.enrollments');
    });
    Route::get('/admin/enrollments/{id}', function ($id) {
        $enrollment = \App\Models\UserCourseEnrollment::with(['user', 'floridaCourse', 'progress.chapter'])->findOrFail($id);

        return view('admin.enrollment-detail', compact('enrollment'));
    });
    Route::get('/admin/users', function () {
        return view('admin.users');
    });
    Route::get('/admin/reports', function () {
        return view('admin.reports');
    });
    Route::get('/admin/payments', function () {
        return view('admin.payments');
    });
    Route::get('/admin/invoices', function () {
        return view('admin.invoices');
    });
    Route::get('/admin/certificates', function () {
        return view('admin.certificates');
    });
    Route::get('/admin/state-integration', function () {
        return view('admin.state-integration');
    });
    Route::get('/admin/email-templates', function () {
        return view('admin.email-templates');
    });
    Route::get('/admin/notifications', function () {
        return view('admin.notifications');
    });
    Route::get('/admin/accessibility-settings', function () {
        return view('admin.accessibility-settings');
    });
    Route::get('/admin/mobile-optimization', function () {
        return view('admin.mobile-optimization');
    });
    Route::get('/admin/pwa-management', function () {
        return view('admin.pwa-management');
    });
    Route::get('/admin/security-dashboard', function () {
        return view('admin.security-dashboard');
    });
    Route::get('/admin/account-security', function () {
        return view('admin.account-security');
    });
    Route::get('/admin/data-export', function () {
        return view('admin.data-export');
    });

    // Florida Security & Audit Module Routes
    Route::get('/admin/florida-security', [App\Http\Controllers\FloridaSecurityWebController::class, 'securityDashboard']);
    Route::get('/admin/florida-audit', [App\Http\Controllers\Admin\FloridaAuditController::class, 'index'])->name('admin.florida-audit.index');
    Route::get('/admin/florida-audit/export', [App\Http\Controllers\Admin\FloridaAuditController::class, 'export'])->name('admin.florida-audit.export');
    Route::get('/admin/florida-compliance', [App\Http\Controllers\FloridaSecurityWebController::class, 'complianceManager']);
    Route::get('/admin/florida-data-export', [App\Http\Controllers\FloridaSecurityWebController::class, 'dataExportTool']);

    // Florida Mobile & Accessibility Module Routes
    Route::get('/admin/florida-mobile', function () {
        return view('admin.florida-mobile');
    });
    Route::get('/admin/florida-accessibility', function () {
        return view('admin.florida-accessibility');
    });

    // Florida DICDS UI & Workflow Module Routes
    Route::get('/dicds/welcome', function () {
        return view('dicds.welcome');
    });
    Route::get('/dicds/main-menu', function () {
        return view('dicds.main-menu');
    });
    Route::get('/admin/dicds-user-management', function () {
        return view('admin.dicds-user-management');
    });
    Route::get('/admin/dicds-access-requests', function () {
        return view('admin.dicds-access-requests');
    });

    Route::get('/admin/florida-courses/{courseId}/chapters', function () {
        return view('admin.chapter-builder');
    });
    Route::get('/admin/chapters/{chapterId}/questions', function ($chapterId) {
        return view('admin.question-manager', ['chapterId' => $chapterId]);
    });
    Route::get('/admin/courses/{courseId}/final-exam', function () {
        return view('admin.question-manager');
    });
    Route::get('/admin/courses/{courseId}/preview', function () {
        return view('admin.course-preview');
    });

    // Mobile-specific routes
    Route::get('/mobile/course/{id}', function ($id) {
        return view('mobile.course-player', ['course' => (object) ['id' => $id, 'title' => 'Sample Course']]);
    });

    // Web routes for accessibility system
    Route::get('/web/accessibility/preferences', [App\Http\Controllers\AccessibilityController::class, 'getPreferences']);
    Route::put('/web/accessibility/preferences', [App\Http\Controllers\AccessibilityController::class, 'updatePreferences']);
    Route::post('/web/accessibility/reset-preferences', [App\Http\Controllers\AccessibilityController::class, 'resetPreferences']);

    Route::get('/web/device-info', [App\Http\Controllers\MobileOptimizationController::class, 'getDeviceInfo']);
    Route::get('/web/mobile-optimized/{component}', [App\Http\Controllers\MobileOptimizationController::class, 'getMobileOptimizedComponent']);

    // Web routes for security system
    Route::get('/web/security/logs', [App\Http\Controllers\SecurityLogController::class, 'index']);
    Route::get('/web/account/security-settings', [App\Http\Controllers\AccountSecurityController::class, 'getSecuritySettings']);
    Route::put('/web/account/password', [App\Http\Controllers\AccountSecurityController::class, 'changePassword']);
    Route::get('/web/account/login-history', [App\Http\Controllers\AccountSecurityController::class, 'getLoginHistory']);
    Route::post('/web/data-export/request', [App\Http\Controllers\DataExportController::class, 'requestExport']);
    Route::get('/web/audit/dashboard', [App\Http\Controllers\AuditController::class, 'getDashboard']);

    // Web routes for certificates
    Route::get('/web/certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'downloadWeb']);
});
// Certificate Management Routes
Route::middleware('auth')->group(function () {
    Route::get('/web/admin/certificates', function (Request $request) {
        $certificates = \App\Models\FloridaCertificate::query()
            ->when($request->state_code, fn ($q) => $q->where('state', $request->state_code))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($certificates);
    });

    Route::post('/web/admin/certificates', function (Request $request) {
        try {
            $validated = $request->validate([
                'enrollment_id' => 'nullable|integer',
                'student_name' => 'required|string',
                'course_name' => 'required|string',
                'state_code' => 'required|string',
                'completion_date' => 'required|date',
                'status' => 'nullable|string',
            ]);

            $certificate = \App\Models\FloridaCertificate::create([
                'enrollment_id' => $validated['enrollment_id'] ?? null,
                'student_name' => $validated['student_name'],
                'course_name' => $validated['course_name'],
                'state' => $validated['state_code'],
                'completion_date' => $validated['completion_date'],
                'final_exam_score' => 80.00,
                'driver_license_number' => 'A000000000000',
                'citation_number' => '0000000',
                'citation_county' => 'UNKNOWN',
                'traffic_school_due_date' => now()->addDays(30),
                'student_address' => 'N/A',
                'student_date_of_birth' => now()->subYears(25),
                'court_name' => 'N/A',
                'dicds_certificate_number' => 'CERT-'.time(),
                'verification_hash' => bin2hex(random_bytes(16)),
                'generated_at' => now(),
            ]);

            return response()->json($certificate, 201);
        } catch (\Exception $e) {
            \Log::error('Certificate creation error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('/api/enrollments', function () {
        $enrollments = \App\Models\UserCourseEnrollment::where('user_id', auth()->id())
            ->get()
            ->map(function ($enrollment) {
                $course = \App\Models\FloridaCourse::find($enrollment->course_id);

                $enrollmentArray = $enrollment->toArray();
                $enrollmentArray['course'] = $course ? $course->toArray() : null;

                return $enrollmentArray;
            });

        return response()->json($enrollments);
    });

    Route::get('/api/dicds-submissions', function () {
        $submissions = \App\Models\FloridaCertificate::where('is_sent_to_student', true)
            ->orderBy('sent_at', 'desc')
            ->get()
            ->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'student_name' => $cert->student_name,
                    'course_name' => $cert->course_name,
                    'certificate_number' => $cert->dicds_certificate_number,
                    'submitted_at' => $cert->sent_at,
                    'status' => 'success',
                ];
            });

        return response()->json($submissions);
    });
});

// Public Pages
Route::get('/faq', function () {
    return view('faq');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
});

Route::get('/terms-conditions', function () {
    return view('legal.terms-conditions');
});

Route::get('/refund-policy', function () {
    return view('legal.refund-policy');
});

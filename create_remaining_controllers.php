<?php

// Script to create remaining admin controllers for Texas, Delaware

$states = ['texas', 'delaware'];
$controllers = ['QuizController', 'EnrollmentController', 'CertificateController'];

foreach ($states as $state) {
    $stateTitle = ucfirst($state);
    
    foreach ($controllers as $controller) {
        $controllerName = str_replace('Controller', '', $controller);
        $controllerLower = strtolower($controllerName);
        
        // Create directory if it doesn't exist
        $dir = "app/Http/Controllers/Admin/" . ucfirst($state);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $template = getControllerTemplate($state, $stateTitle, $controller, $controllerName, $controllerLower);
        
        file_put_contents("{$dir}/{$controller}.php", $template);
        echo "Created {$dir}/{$controller}.php\n";
    }
}

function getControllerTemplate($state, $stateTitle, $controller, $controllerName, $controllerLower) {
    $namespace = "App\\Http\\Controllers\\Admin\\" . ucfirst($state);
    
    switch ($controller) {
        case 'QuizController':
            return getQuizControllerTemplate($namespace, $state, $stateTitle);
        case 'EnrollmentController':
            return getEnrollmentControllerTemplate($namespace, $state, $stateTitle);
        case 'CertificateController':
            return getCertificateControllerTemplate($namespace, $state, $stateTitle);
    }
}

function getQuizControllerTemplate($namespace, $state, $stateTitle) {
    return "<?php

namespace {$namespace};

use App\\Http\\Controllers\\Controller;
use App\\Models\\Chapter;
use App\\Models\\ChapterQuestion;
use App\\Models\\Course;
use Illuminate\\Http\\Request;

class QuizController extends Controller
{
    public function index(Request \$request)
    {
        \$query = ChapterQuestion::with(['chapter.course'])
            ->whereHas('chapter.course', function(\$q) {
                \$q->where('state', '{$state}');
            })
            ->orderBy('created_at', 'desc');

        if (\$request->filled('course_id')) {
            \$query->whereHas('chapter', function(\$q) use (\$request) {
                \$q->where('course_id', \$request->course_id);
            });
        }

        if (\$request->filled('chapter_id')) {
            \$query->where('chapter_id', \$request->chapter_id);
        }

        if (\$request->filled('search')) {
            \$query->where(function(\$q) use (\$request) {
                \$q->where('question_text', 'like', '%' . \$request->search . '%')
                  ->orWhere('option_a', 'like', '%' . \$request->search . '%')
                  ->orWhere('option_b', 'like', '%' . \$request->search . '%')
                  ->orWhere('option_c', 'like', '%' . \$request->search . '%')
                  ->orWhere('option_d', 'like', '%' . \$request->search . '%');
            });
        }

        \$questions = \$query->paginate(20);
        \$courses = Course::where('state', '{$state}')->orderBy('title')->get();

        return view('admin.{$state}.quizzes.index', compact('questions', 'courses'));
    }

    public function create(Request \$request)
    {
        \$chapterId = \$request->get('chapter_id');
        \$chapter = \$chapterId ? Chapter::findOrFail(\$chapterId) : null;
        \$courses = Course::where('state', '{$state}')->with('chapters')->orderBy('title')->get();

        return view('admin.{$state}.quizzes.create', compact('chapter', 'courses'));
    }

    public function store(Request \$request)
    {
        \$request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'points' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        \$chapter = Chapter::with('course')->findOrFail(\$request->chapter_id);
        if (\$chapter->course->state !== '{$state}') {
            return redirect()->back()->with('error', 'Invalid chapter selection.');
        }

        \$question = ChapterQuestion::create([
            'chapter_id' => \$request->chapter_id,
            'question_text' => \$request->question_text,
            'option_a' => \$request->option_a,
            'option_b' => \$request->option_b,
            'option_c' => \$request->option_c,
            'option_d' => \$request->option_d,
            'correct_answer' => \$request->correct_answer,
            'explanation' => \$request->explanation,
            'difficulty_level' => \$request->difficulty_level ?? 'medium',
            'points' => \$request->points ?? 1,
            'is_active' => \$request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.{$state}.quizzes.show', \$question)
            ->with('success', '{$stateTitle} quiz question created successfully.');
    }

    public function show(ChapterQuestion \$quiz)
    {
        \$quiz->load(['chapter.course']);
        
        \$stats = [
            'total_attempts' => 0,
            'correct_attempts' => 0,
            'average_score' => 0,
            'difficulty_level' => \$quiz->difficulty_level,
        ];

        return view('admin.{$state}.quizzes.show', compact('quiz', 'stats'));
    }

    public function edit(ChapterQuestion \$quiz)
    {
        \$courses = Course::where('state', '{$state}')->with('chapters')->orderBy('title')->get();
        return view('admin.{$state}.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request \$request, ChapterQuestion \$quiz)
    {
        \$request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'points' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        \$chapter = Chapter::with('course')->findOrFail(\$request->chapter_id);
        if (\$chapter->course->state !== '{$state}') {
            return redirect()->back()->with('error', 'Invalid chapter selection.');
        }

        \$quiz->update([
            'chapter_id' => \$request->chapter_id,
            'question_text' => \$request->question_text,
            'option_a' => \$request->option_a,
            'option_b' => \$request->option_b,
            'option_c' => \$request->option_c,
            'option_d' => \$request->option_d,
            'correct_answer' => \$request->correct_answer,
            'explanation' => \$request->explanation,
            'difficulty_level' => \$request->difficulty_level,
            'points' => \$request->points,
            'is_active' => \$request->boolean('is_active'),
        ]);

        return redirect()->route('admin.{$state}.quizzes.show', \$quiz)
            ->with('success', '{$stateTitle} quiz question updated successfully.');
    }

    public function destroy(ChapterQuestion \$quiz)
    {
        \$quiz->delete();

        return redirect()->route('admin.{$state}.quizzes.index')
            ->with('success', '{$stateTitle} quiz question deleted successfully.');
    }
}";
}

function getEnrollmentControllerTemplate($namespace, $state, $stateTitle) {
    return "<?php

namespace {$namespace};

use App\\Http\\Controllers\\Controller;
use App\\Models\\UserCourseEnrollment;
use App\\Models\\User;
use App\\Models\\Course;
use Illuminate\\Http\\Request;

class EnrollmentController extends Controller
{
    public function index(Request \$request)
    {
        \$query = UserCourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function(\$q) {
                \$q->where('state', '{$state}');
            })
            ->orderBy('created_at', 'desc');

        if (\$request->filled('search')) {
            \$query->whereHas('user', function(\$q) use (\$request) {
                \$q->where('first_name', 'like', '%' . \$request->search . '%')
                  ->orWhere('last_name', 'like', '%' . \$request->search . '%')
                  ->orWhere('email', 'like', '%' . \$request->search . '%');
            });
        }

        if (\$request->filled('status')) {
            \$query->where('status', \$request->status);
        }

        if (\$request->filled('payment_status')) {
            \$query->where('payment_status', \$request->payment_status);
        }

        if (\$request->filled('course_id')) {
            \$query->where('course_id', \$request->course_id);
        }

        if (\$request->filled('date_from')) {
            \$query->whereDate('created_at', '>=', \$request->date_from);
        }

        if (\$request->filled('date_to')) {
            \$query->whereDate('created_at', '<=', \$request->date_to);
        }

        \$enrollments = \$query->paginate(20);
        \$courses = Course::where('state', '{$state}')->orderBy('title')->get();

        \$stats = [
            'total' => UserCourseEnrollment::whereHas('course', function(\$q) {
                \$q->where('state', '{$state}');
            })->count(),
            'active' => UserCourseEnrollment::whereHas('course', function(\$q) {
                \$q->where('state', '{$state}');
            })->where('status', 'active')->count(),
            'completed' => UserCourseEnrollment::whereHas('course', function(\$q) {
                \$q->where('state', '{$state}');
            })->whereNotNull('completed_at')->count(),
            'pending_payment' => UserCourseEnrollment::whereHas('course', function(\$q) {
                \$q->where('state', '{$state}');
            })->where('payment_status', 'pending')->count(),
        ];

        return view('admin.{$state}.enrollments.index', compact('enrollments', 'courses', 'stats'));
    }

    public function create()
    {
        \$users = User::orderBy('first_name')->orderBy('last_name')->get();
        \$courses = Course::where('state', '{$state}')->where('is_active', true)->orderBy('title')->get();

        return view('admin.{$state}.enrollments.create', compact('users', 'courses'));
    }

    public function store(Request \$request)
    {
        \$request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'citation_number' => 'nullable|string',
            'court_date' => 'nullable|date',
            'status' => 'required|in:active,completed,expired,cancelled',
        ]);

        \$course = Course::findOrFail(\$request->course_id);
        if (\$course->state !== '{$state}') {
            return redirect()->back()->with('error', 'Invalid course selection.');
        }

        \$enrollment = UserCourseEnrollment::create([
            'user_id' => \$request->user_id,
            'course_id' => \$request->course_id,
            'course_table' => 'courses',
            'payment_status' => \$request->payment_status,
            'amount_paid' => \$request->amount_paid,
            'payment_method' => \$request->payment_method,
            'citation_number' => \$request->citation_number,
            'court_date' => \$request->court_date,
            'status' => \$request->status,
            'enrolled_at' => now(),
        ]);

        return redirect()->route('admin.{$state}.enrollments.show', \$enrollment)
            ->with('success', '{$stateTitle} enrollment created successfully.');
    }

    public function show(UserCourseEnrollment \$enrollment)
    {
        \$enrollment->load(['user', 'course', 'progress', 'quizAttempts', 'payments']);

        \$stats = [
            'progress_percentage' => \$enrollment->progress_percentage,
            'total_time_spent' => \$enrollment->total_time_spent,
            'quiz_attempts' => \$enrollment->quizAttempts->count(),
            'average_quiz_score' => \$enrollment->quizAttempts->avg('score') ?? 0,
            'total_payments' => \$enrollment->payments->sum('amount'),
        ];

        return view('admin.{$state}.enrollments.show', compact('enrollment', 'stats'));
    }

    public function edit(UserCourseEnrollment \$enrollment)
    {
        \$users = User::orderBy('first_name')->orderBy('last_name')->get();
        \$courses = Course::where('state', '{$state}')->orderBy('title')->get();

        return view('admin.{$state}.enrollments.edit', compact('enrollment', 'users', 'courses'));
    }

    public function update(Request \$request, UserCourseEnrollment \$enrollment)
    {
        \$request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'citation_number' => 'nullable|string',
            'court_date' => 'nullable|date',
            'status' => 'required|in:active,completed,expired,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        \$course = Course::findOrFail(\$request->course_id);
        if (\$course->state !== '{$state}') {
            return redirect()->back()->with('error', 'Invalid course selection.');
        }

        \$enrollment->update([
            'user_id' => \$request->user_id,
            'course_id' => \$request->course_id,
            'payment_status' => \$request->payment_status,
            'amount_paid' => \$request->amount_paid,
            'payment_method' => \$request->payment_method,
            'citation_number' => \$request->citation_number,
            'court_date' => \$request->court_date,
            'status' => \$request->status,
            'progress_percentage' => \$request->progress_percentage ?? \$enrollment->progress_percentage,
        ]);

        return redirect()->route('admin.{$state}.enrollments.show', \$enrollment)
            ->with('success', '{$stateTitle} enrollment updated successfully.');
    }

    public function destroy(UserCourseEnrollment \$enrollment)
    {
        \$enrollment->delete();

        return redirect()->route('admin.{$state}.enrollments.index')
            ->with('success', '{$stateTitle} enrollment deleted successfully.');
    }
}";
}

function getCertificateControllerTemplate($namespace, $state, $stateTitle) {
    return "<?php

namespace {$namespace};

use App\\Http\\Controllers\\Controller;
use App\\Models\\Certificate;
use App\\Models\\UserCourseEnrollment;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Storage;

class CertificateController extends Controller
{
    public function index(Request \$request)
    {
        \$query = Certificate::with(['enrollment.user', 'enrollment.course'])
            ->whereHas('enrollment.course', function(\$q) {
                \$q->where('state', '{$state}');
            })
            ->orderBy('created_at', 'desc');

        if (\$request->filled('search')) {
            \$query->whereHas('enrollment.user', function(\$q) use (\$request) {
                \$q->where('first_name', 'like', '%' . \$request->search . '%')
                  ->orWhere('last_name', 'like', '%' . \$request->search . '%')
                  ->orWhere('email', 'like', '%' . \$request->search . '%');
            });
        }

        if (\$request->filled('status')) {
            \$query->where('status', \$request->status);
        }

        if (\$request->filled('course_id')) {
            \$query->whereHas('enrollment', function(\$q) use (\$request) {
                \$q->where('course_id', \$request->course_id);
            });
        }

        if (\$request->filled('date_from')) {
            \$query->whereDate('created_at', '>=', \$request->date_from);
        }

        if (\$request->filled('date_to')) {
            \$query->whereDate('created_at', '<=', \$request->date_to);
        }

        \$certificates = \$query->paginate(20);

        \$stats = [
            'total' => Certificate::whereHas('enrollment.course', function(\$q) {
                \$q->where('state', '{$state}');
            })->count(),
            'pending' => Certificate::whereHas('enrollment.course', function(\$q) {
                \$q->where('state', '{$state}');
            })->where('status', 'pending')->count(),
            'generated' => Certificate::whereHas('enrollment.course', function(\$q) {
                \$q->where('state', '{$state}');
            })->where('status', 'generated')->count(),
            'sent' => Certificate::whereHas('enrollment.course', function(\$q) {
                \$q->where('state', '{$state}');
            })->where('status', 'sent')->count(),
        ];

        return view('admin.{$state}.certificates.index', compact('certificates', 'stats'));
    }

    public function create()
    {
        \$enrollments = UserCourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function(\$q) {
                \$q->where('state', '{$state}');
            })
            ->whereNotNull('completed_at')
            ->whereDoesntHave('certificate')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('admin.{$state}.certificates.create', compact('enrollments'));
    }

    public function store(Request \$request)
    {
        \$request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'certificate_number' => 'nullable|string|unique:certificates,certificate_number',
            'issue_date' => 'nullable|date',
        ]);

        \$enrollment = UserCourseEnrollment::with('course')->findOrFail(\$request->enrollment_id);

        if (\$enrollment->course->state !== '{$state}') {
            return redirect()->back()->with('error', 'Invalid enrollment selection.');
        }

        if (\$enrollment->certificate) {
            return redirect()->route('admin.{$state}.certificates.index')
                ->with('error', 'Certificate already exists for this enrollment.');
        }

        \$certificate = Certificate::create([
            'enrollment_id' => \$request->enrollment_id,
            'certificate_number' => \$request->certificate_number ?? \$this->generateCertificateNumber(),
            'issue_date' => \$request->issue_date ?? now(),
            'status' => 'pending',
        ]);

        return redirect()->route('admin.{$state}.certificates.show', \$certificate)
            ->with('success', '{$stateTitle} certificate created successfully.');
    }

    public function show(Certificate \$certificate)
    {
        \$certificate->load(['enrollment.user', 'enrollment.course']);

        return view('admin.{$state}.certificates.show', compact('certificate'));
    }

    public function edit(Certificate \$certificate)
    {
        return view('admin.{$state}.certificates.edit', compact('certificate'));
    }

    public function update(Request \$request, Certificate \$certificate)
    {
        \$request->validate([
            'certificate_number' => 'required|string|unique:certificates,certificate_number,' . \$certificate->id,
            'issue_date' => 'required|date',
            'status' => 'required|in:pending,generated,sent,error',
            'notes' => 'nullable|string',
        ]);

        \$certificate->update([
            'certificate_number' => \$request->certificate_number,
            'issue_date' => \$request->issue_date,
            'status' => \$request->status,
            'notes' => \$request->notes,
        ]);

        return redirect()->route('admin.{$state}.certificates.show', \$certificate)
            ->with('success', '{$stateTitle} certificate updated successfully.');
    }

    public function destroy(Certificate \$certificate)
    {
        if (\$certificate->pdf_path && Storage::exists(\$certificate->pdf_path)) {
            Storage::delete(\$certificate->pdf_path);
        }

        \$certificate->delete();

        return redirect()->route('admin.{$state}.certificates.index')
            ->with('success', '{$stateTitle} certificate deleted successfully.');
    }

    private function generateCertificateNumber()
    {
        \$prefix = strtoupper(substr('{$state}', 0, 2)) . date('Y');
        \$lastCertificate = Certificate::whereHas('enrollment.course', function(\$q) {
                \$q->where('state', '{$state}');
            })
            ->where('certificate_number', 'like', \$prefix . '%')
            ->orderBy('certificate_number', 'desc')
            ->first();

        if (\$lastCertificate) {
            \$lastNumber = (int) substr(\$lastCertificate->certificate_number, -6);
            \$newNumber = str_pad(\$lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            \$newNumber = '000001';
        }

        return \$prefix . \$newNumber;
    }
}";
}

echo "Controller generation script created.\n";
?>
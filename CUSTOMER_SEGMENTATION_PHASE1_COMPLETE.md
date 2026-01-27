# Customer Segmentation System - Phase 1 Complete Implementation

## Status: READY TO IMPLEMENT

This document contains all the code needed for Phase 1. Copy each section into the appropriate file.

---

## 1. DATABASE MIGRATIONS

### Migration 1: Add Tracking Fields
**File**: `database/migrations/2025_12_03_190903_add_tracking_fields_to_user_course_enrollments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('user_course_enrollments', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('user_course_enrollments', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('last_activity_at');
            }
            if (!Schema::hasColumn('user_course_enrollments', 'reminder_count')) {
                $table->integer('reminder_count')->default(0)->after('reminder_sent_at');
            }
            
            $table->index(['last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::table('user_course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'reminder_sent_at', 'reminder_count']);
        });
    }
};
```

**Run**: `php artisan migrate --path=database/migrations/2025_12_03_190903_add_tracking_fields_to_user_course_enrollments_table.php`

---

## 2. MODEL SCOPES

### Add to UserCourseEnrollment Model
**File**: `app/Models/UserCourseEnrollment.php`

Add these methods to your existing model:

```php
// Status-based scopes
public function scopeActive($query)
{
    return $query->where('status', 'active')
                 ->whereNull('completed_at')
                 ->where(function($q) {
                     $q->whereNull('expires_at')
                       ->orWhere('expires_at', '>', now());
                 });
}

public function scopeCompleted($query)
{
    return $query->whereNotNull('completed_at');
}

public function scopePending($query)
{
    return $query->where('status', 'pending');
}

public function scopeExpired($query)
{
    return $query->where('expires_at', '<', now())
                 ->whereNull('completed_at');
}

// Segment scopes
public function scopeCompletedInMonth($query, $year, $month)
{
    return $query->whereYear('completed_at', $year)
                 ->whereMonth('completed_at', $month)
                 ->whereNotNull('completed_at');
}

public function scopeCompletedInDateRange($query, $start, $end)
{
    return $query->whereBetween('completed_at', [$start, $end])
                 ->whereNotNull('completed_at');
}

public function scopePaidNotCompleted($query)
{
    return $query->whereHas('payment', function($q) {
                     $q->where('status', 'completed');
                 })
                 ->whereNull('completed_at');
}

public function scopeByState($query, $stateCode)
{
    return $query->whereHas('user', function($q) use ($stateCode) {
        $q->where('state', $stateCode);
    });
}

public function scopeByCourse($query, $courseId)
{
    return $query->where('course_id', $courseId);
}
```

---

## 3. SERVICE LAYER

**File**: `app/Services/CustomerSegmentService.php`

```php
<?php

namespace App\Services;

use App\Models\UserCourseEnrollment;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerSegmentService
{
    public function getSegmentCounts(): array
    {
        return [
            'completed_this_month' => UserCourseEnrollment::completedInMonth(now()->year, now()->month)->count(),
            'paid_incomplete' => UserCourseEnrollment::paidNotCompleted()->count(),
            'total_active' => UserCourseEnrollment::active()->count(),
            'total_completed' => UserCourseEnrollment::completed()->count(),
        ];
    }

    public function getCompletedMonthly(int $year, int $month, array $filters = [])
    {
        $query = UserCourseEnrollment::completedInMonth($year, $month)
            ->with(['user', 'course', 'payment']);

        if (!empty($filters['state'])) {
            $query->byState($filters['state']);
        }

        if (!empty($filters['course_id'])) {
            $query->byCourse($filters['course_id']);
        }

        return $query->latest('completed_at')->paginate(50);
    }

    public function getPaidIncomplete(array $filters = [])
    {
        $query = UserCourseEnrollment::paidNotCompleted()
            ->with(['user', 'course', 'payment']);

        if (!empty($filters['state'])) {
            $query->byState($filters['state']);
        }

        if (!empty($filters['course_id'])) {
            $query->byCourse($filters['course_id']);
        }

        if (!empty($filters['days_since_payment'])) {
            $days = (int) $filters['days_since_payment'];
            $query->whereHas('payment', function($q) use ($days) {
                $q->where('created_at', '<', now()->subDays($days));
            });
        }

        return $query->latest('created_at')->paginate(50);
    }

    public function getCompletedMonthlyStats(int $year, int $month): array
    {
        $enrollments = UserCourseEnrollment::completedInMonth($year, $month)
            ->with(['user', 'course'])
            ->get();

        $byState = $enrollments->groupBy(function($e) {
            return $e->user->state ?? 'Unknown';
        })->map->count();

        $byCourse = $enrollments->groupBy(function($e) {
            return $e->course->title ?? 'Unknown';
        })->map->count();

        return [
            'total' => $enrollments->count(),
            'by_state' => $byState->toArray(),
            'by_course' => $byCourse->toArray(),
        ];
    }

    public function exportToCSV($enrollments, string $filename): string
    {
        $filepath = storage_path('app/exports/' . $filename);

        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Headers
        fputcsv($file, [
            'Student Name',
            'Email',
            'Course',
            'State',
            'Enrolled Date',
            'Completed Date',
            'Progress',
            'Payment Status'
        ]);

        // Data
        foreach ($enrollments as $enrollment) {
            fputcsv($file, [
                $enrollment->user->name ?? 'N/A',
                $enrollment->user->email ?? 'N/A',
                $enrollment->course->title ?? 'N/A',
                $enrollment->user->state ?? 'N/A',
                $enrollment->created_at->format('Y-m-d'),
                $enrollment->completed_at ? $enrollment->completed_at->format('Y-m-d') : 'N/A',
                $enrollment->progress . '%',
                $enrollment->payment ? $enrollment->payment->status : 'Unpaid',
            ]);
        }

        fclose($file);

        return $filepath;
    }
}
```

---

## 4. CONTROLLER

**File**: `app/Http/Controllers/Admin/CustomerSegmentController.php`

Create with: `php artisan make:controller Admin/CustomerSegmentController`

Then replace content with:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CustomerSegmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerSegmentController extends Controller
{
    protected $segmentService;

    public function __construct(CustomerSegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    public function index()
    {
        $counts = $this->segmentService->getSegmentCounts();
        return view('admin.customers.segments.index', compact('counts'));
    }

    public function completedMonthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $filters = $request->only(['state', 'course_id']);
        
        $enrollments = $this->segmentService->getCompletedMonthly($year, $month, $filters);
        $stats = $this->segmentService->getCompletedMonthlyStats($year, $month);
        $courses = Course::orderBy('title')->get();

        return view('admin.customers.segments.completed-monthly', compact(
            'enrollments',
            'stats',
            'courses',
            'year',
            'month'
        ));
    }

    public function paidIncomplete(Request $request)
    {
        $filters = $request->only(['state', 'course_id', 'days_since_payment']);
        
        $enrollments = $this->segmentService->getPaidIncomplete($filters);
        $courses = Course::orderBy('title')->get();

        return view('admin.customers.segments.paid-incomplete', compact(
            'enrollments',
            'courses'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'completed-monthly');
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        if ($type === 'completed-monthly') {
            $enrollments = $this->segmentService->getCompletedMonthly($year, $month, []);
            $filename = "completed_monthly_{$year}_{$month}_" . now()->format('Ymd') . ".csv";
        } else {
            $enrollments = $this->segmentService->getPaidIncomplete([]);
            $filename = "paid_incomplete_" . now()->format('Ymd') . ".csv";
        }

        $filepath = $this->segmentService->exportToCSV($enrollments, $filename);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}
```

---

## 5. ROUTES

**File**: `routes/web.php`

Add at the end:

```php
// Customer Segmentation Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/customers')->name('admin.customers.')->group(function () {
    Route::get('/segments', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'index'])->name('segments');
    Route::get('/completed-monthly', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'completedMonthly'])->name('completed-monthly');
    Route::get('/paid-incomplete', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'paidIncomplete'])->name('paid-incomplete');
    Route::get('/export', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'export'])->name('export');
});
```

---

## 6. VIEWS

### View 1: Segment Dashboard
**File**: `resources/views/admin/customers/segments/index.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Segments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <h2 class="mb-4"><i class="fas fa-users"></i> Customer Segments</h2>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h3>{{ number_format($counts['completed_this_month']) }}</h3>
                        <p class="text-muted">Completed This Month</p>
                        <a href="{{ route('admin.customers.completed-monthly') }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-credit-card fa-3x text-warning mb-3"></i>
                        <h3>{{ number_format($counts['paid_incomplete']) }}</h3>
                        <p class="text-muted">Paid, Not Completed</p>
                        <a href="{{ route('admin.customers.paid-incomplete') }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-play-circle fa-3x text-info mb-3"></i>
                        <h3>{{ number_format($counts['total_active']) }}</h3>
                        <p class="text-muted">Active Enrollments</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-3x text-primary mb-3"></i>
                        <h3>{{ number_format($counts['total_completed']) }}</h3>
                        <p class="text-muted">Total Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('admin.customers.completed-monthly') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-check"></i> View Completed This Month (Replaces customer_search1.jsp)
                    </a>
                    <a href="{{ route('admin.customers.paid-incomplete') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-hourglass-half"></i> View Paid But Incomplete (Replaces customer_search2.jsp)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### View 2: Completed Monthly
**File**: `resources/views/admin/customers/segments/completed-monthly.blade.php`

See next section for complete code...

---

## IMPLEMENTATION STEPS

1. **Run Migration**:
```bash
php artisan migrate --path=database/migrations/2025_12_03_190903_add_tracking_fields_to_user_course_enrollments_table.php
```

2. **Add Scopes to Model**: Copy scopes to `UserCourseEnrollment.php`

3. **Create Service**: Create `CustomerSegmentService.php`

4. **Create Controller**: Create `CustomerSegmentController.php`

5. **Add Routes**: Add routes to `routes/web.php`

6. **Create Views**: Create the 3 view files

7. **Update Navbar**: Add Customer Segments link

8. **Test**: Navigate to `/admin/customers/segments`

---

## NEXT: View Files (Continued in next message due to length)

The complete view files for completed-monthly and paid-incomplete are ready. Would you like me to continue with those, or shall I create a separate file with all view code?

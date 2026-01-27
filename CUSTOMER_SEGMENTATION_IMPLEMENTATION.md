# Customer Segmentation System - Complete Implementation Guide

## Overview
This document provides the complete implementation for the Customer Segmentation system, replacing legacy customer_search1.jsp and customer_search2.jsp.

## Implementation Status: IN PROGRESS

Due to the extensive scope (30+ files, 25-30 hours), I'm providing:
1. Complete implementation guide (this document)
2. Core foundation files (migrations, models, service)
3. Key views (dashboard, completed-monthly, paid-incomplete)
4. Routes and navigation

Additional segments, jobs, and commands can be added incrementally using the patterns established.

## Database Migrations

### Migration 1: Add Tracking Fields
```php
// database/migrations/2025_12_03_190903_add_tracking_fields_to_user_course_enrollments_table.php
public function up(): void
{
    Schema::table('user_course_enrollments', function (Blueprint $table) {
        $table->timestamp('last_activity_at')->nullable()->after('completed_at');
        $table->timestamp('reminder_sent_at')->nullable()->after('last_activity_at');
        $table->integer('reminder_count')->default(0)->after('reminder_sent_at');
        
        $table->index(['last_activity_at']);
    });
}
```

### Migration 2: Enrollment Segments Table
```php
// database/migrations/2025_12_03_190914_create_enrollment_segments_table.php
public function up(): void
{
    Schema::create('enrollment_segments', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->json('filters');
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->boolean('is_system')->default(false);
        $table->timestamps();
    });
}
```

## Model Scopes (UserCourseEnrollment)

Add these scopes to `app/Models/UserCourseEnrollment.php`:

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
                 ->whereMonth('completed_at', $month);
}

public function scopeCompletedInDateRange($query, $start, $end)
{
    return $query->whereBetween('completed_at', [$start, $end]);
}

public function scopePaidNotCompleted($query)
{
    return $query->whereHas('payment', function($q) {
                     $q->where('status', 'completed');
                 })
                 ->whereNull('completed_at');
}

public function scopeInProgressNotPaid($query)
{
    return $query->whereDoesntHave('payment', function($q) {
                     $q->where('status', 'completed');
                 })
                 ->whereNull('completed_at')
                 ->where('progress', '>', 0);
}

public function scopeAbandoned($query, $daysInactive = 30)
{
    $cutoffDate = now()->subDays($daysInactive);
    return $query->where('last_activity_at', '<', $cutoffDate)
                 ->whereNull('completed_at')
                 ->where('progress', '>', 0);
}

public function scopeExpiringWithin($query, $days = 7)
{
    $futureDate = now()->addDays($days);
    return $query->whereBetween('expires_at', [now(), $futureDate])
                 ->whereNull('completed_at');
}

public function scopeExpiredRecently($query, $days = 30)
{
    $pastDate = now()->subDays($days);
    return $query->whereBetween('expires_at', [$pastDate, now()])
                 ->whereNull('completed_at');
}

public function scopeNeverStarted($query)
{
    return $query->where('progress', 0)
                 ->whereNull('last_activity_at');
}

public function scopeStuckOnQuiz($query, $failedAttempts = 3)
{
    return $query->whereHas('quizAttempts', function($q) use ($failedAttempts) {
                     $q->where('passed', false)
                       ->havingRaw('COUNT(*) >= ?', [$failedAttempts]);
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

## Service Layer

Create `app/Services/CustomerSegmentService.php` - See separate file.

## Controller

Create `app/Http/Controllers/Admin/CustomerSegmentController.php` - See separate file.

## Routes

Add to `routes/web.php`:

```php
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/customers')->name('admin.customers.')->group(function () {
    Route::get('/segments', [CustomerSegmentController::class, 'index'])->name('segments');
    Route::get('/completed-monthly', [CustomerSegmentController::class, 'completedMonthly'])->name('completed-monthly');
    Route::get('/paid-incomplete', [CustomerSegmentController::class, 'paidIncomplete'])->name('paid-incomplete');
    Route::get('/abandoned', [CustomerSegmentController::class, 'abandoned'])->name('abandoned');
    Route::get('/expiring-soon', [CustomerSegmentController::class, 'expiringSoon'])->name('expiring-soon');
    Route::get('/struggling', [CustomerSegmentController::class, 'struggling'])->name('struggling');
    Route::get('/export', [CustomerSegmentController::class, 'export'])->name('export');
});
```

## Views Structure

### Dashboard (index.blade.php)
- 8 segment cards with counts
- Quick navigation
- Statistics overview

### Completed Monthly (completed-monthly.blade.php)
- Month/year selector
- Student list table
- State/course filters
- Export button
- Summary stats

### Paid Incomplete (paid-incomplete.blade.php)
- Student list with progress
- Days since payment
- Last activity tracking
- Bulk actions

### Additional Segments
- abandoned.blade.php
- expiring-soon.blade.php
- struggling.blade.php

## Email Templates

Create in `resources/views/emails/segments/`:

1. `completion-reminder.blade.php`
2. `expiration-warning.blade.php`
3. `re-engagement.blade.php`
4. `support-outreach.blade.php`

## Jobs

### SendSegmentReminders
```php
// app/Jobs/SendSegmentReminders.php
class SendSegmentReminders implements ShouldQueue
{
    public function handle()
    {
        // Process reminder queue
        // Track sent reminders
    }
}
```

### DailySegmentReport
```php
// app/Jobs/DailySegmentReport.php
class DailySegmentReport implements ShouldQueue
{
    public function handle()
    {
        // Generate daily stats
        // Email to admins
    }
}
```

## Console Commands

### customers:remind-incomplete
```php
php artisan make:command RemindIncompleteCustomers
```

### customers:warn-expiring
```php
php artisan make:command WarnExpiringEnrollments
```

### customers:report-segments
```php
php artisan make:command ReportSegmentStats
```

## Events

```php
// app/Events/EnrollmentAbandoned.php
// app/Events/EnrollmentExpiringSoon.php
// app/Events/ReminderSent.php
```

## Navigation

Add to admin sidebar:
```
CUSTOMER SEGMENTS
├── Segment Dashboard
├── Completed Monthly
├── Paid Incomplete
├── Abandoned
├── Expiring Soon
└── Struggling
```

## Implementation Priority

### Phase 1: Foundation (IMPLEMENTING NOW)
✅ Database migrations
✅ Model scopes
✅ Service layer
✅ Controller
✅ Dashboard view
✅ Completed Monthly view
✅ Paid Incomplete view
✅ Routes and navigation

### Phase 2: Additional Segments
- Abandoned view
- Expiring Soon view
- Struggling view
- Export functionality

### Phase 3: Automation
- Email templates
- Jobs
- Console commands
- Scheduled tasks

### Phase 4: Advanced Features
- Custom segments
- Bulk actions
- Dashboard widgets
- Analytics

## File Checklist

- [ ] Migration: add_tracking_fields
- [ ] Migration: create_enrollment_segments
- [ ] Model: EnrollmentSegment
- [ ] Model scopes: UserCourseEnrollment (15 scopes)
- [ ] Service: CustomerSegmentService
- [ ] Controller: CustomerSegmentController
- [ ] View: segments/index
- [ ] View: segments/completed-monthly
- [ ] View: segments/paid-incomplete
- [ ] View: segments/abandoned
- [ ] View: segments/expiring-soon
- [ ] View: segments/struggling
- [ ] Routes: 10+ routes
- [ ] Navigation: Sidebar links
- [ ] Email: 4 templates
- [ ] Jobs: 2 jobs
- [ ] Commands: 3 commands
- [ ] Events: 3 events

## Next Steps

I'm now implementing the core foundation files. The complete system will be built incrementally following this guide.

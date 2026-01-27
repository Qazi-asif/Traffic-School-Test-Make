<?php

namespace App\Services;

use App\Models\EnrollmentSegment;
use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerSegmentService
{
    public function getCompletedMonthly(int $year, int $month, array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course', 'floridaCertificate'])
            ->completedInMonth($year, $month);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('completed_at', 'desc')->paginate(50);
    }

    public function getPaidIncomplete(array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->paidNotCompleted();

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('enrolled_at', 'desc')->paginate(50);
    }

    public function getAbandoned(int $daysInactive = 30, array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->abandoned($daysInactive);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('last_activity_at', 'asc')->paginate(50);
    }

    public function getExpiringSoon(int $days = 7, array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->expiringWithin($days);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('court_date', 'asc')->paginate(50);
    }

    public function getExpired(int $daysAgo = 30, array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->expiredRecently($daysAgo);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('updated_at', 'desc')->paginate(50);
    }

    public function getNeverStarted(array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->neverStarted();

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('enrolled_at', 'desc')->paginate(50);
    }

    public function getStruggling(int $failedAttempts = 3, array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course', 'quizAttempts'])
            ->stuckOnQuiz($failedAttempts);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('last_activity_at', 'desc')->paginate(50);
    }

    public function getInProgress(array $filters = []): LengthAwarePaginator
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->whereNotNull('started_at')
            ->whereNull('completed_at')
            ->where('payment_status', 'paid');

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('last_activity_at', 'desc')->paginate(50);
    }

    public function getSegmentCounts(): array
    {
        return [
            'completed_this_month' => UserCourseEnrollment::query()->completedInMonth(now()->year, now()->month)->count(),
            'paid_incomplete' => UserCourseEnrollment::query()->paidNotCompleted()->count(),
            'in_progress' => UserCourseEnrollment::whereNotNull('started_at')
                ->whereNull('completed_at')
                ->where('payment_status', 'paid')
                ->count(),
            'abandoned' => UserCourseEnrollment::query()->abandoned(30)->count(),
            'expiring_soon' => UserCourseEnrollment::query()->expiringWithin(7)->count(),
            'expired' => UserCourseEnrollment::query()->expiredRecently(30)->count(),
            'never_started' => UserCourseEnrollment::query()->neverStarted()->count(),
            'struggling' => UserCourseEnrollment::query()->stuckOnQuiz(3)->count(),
        ];
    }

    public function getMonthlyCompletionTrend(int $months = 12): Collection
    {
        $data = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = UserCourseEnrollment::query()->completedInMonth($date->year, $date->month)->count();

            $data->push([
                'month' => $date->format('M Y'),
                'count' => $count,
            ]);
        }

        return $data;
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['state'])) {
            $query->byState($filters['state']);
        }

        if (! empty($filters['course_id'])) {
            $query->byCourse($filters['course_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['progress_min'])) {
            $query->where('progress_percentage', '>=', $filters['progress_min']);
        }

        if (isset($filters['progress_max'])) {
            $query->where('progress_percentage', '<=', $filters['progress_max']);
        }

        return $query;
    }

    public function sendReminder(Collection $enrollments, string $templateKey): int
    {
        $count = 0;

        foreach ($enrollments as $enrollment) {
            // Send email based on template
            // Mail::to($enrollment->user->email)->send(new ReminderEmail($enrollment, $templateKey));

            $enrollment->update([
                'reminder_sent_at' => now(),
                'reminder_count' => $enrollment->reminder_count + 1,
            ]);

            $count++;
        }

        return $count;
    }

    public function extendExpiration(Collection $enrollments, int $days): int
    {
        $count = 0;

        foreach ($enrollments as $enrollment) {
            if ($enrollment->court_date) {
                $enrollment->update([
                    'court_date' => $enrollment->court_date->addDays($days),
                ]);
                $count++;
            }
        }

        return $count;
    }

    public function exportToCSV(Collection $enrollments): string
    {
        $filename = 'segment_export_'.now()->format('Y-m-d_His').'.csv';
        $path = storage_path('app/exports/'.$filename);

        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');

        // Headers
        fputcsv($file, [
            'Enrollment ID',
            'Student Name',
            'Email',
            'Course',
            'State',
            'Enrolled Date',
            'Started Date',
            'Completed Date',
            'Progress %',
            'Payment Status',
            'Amount Paid',
            'Last Activity',
        ]);

        // Data
        foreach ($enrollments as $enrollment) {
            fputcsv($file, [
                $enrollment->id,
                $enrollment->user->name ?? '',
                $enrollment->user->email ?? '',
                $enrollment->course->title ?? '',
                $enrollment->course->state ?? '',
                $enrollment->enrolled_at?->format('Y-m-d H:i:s'),
                $enrollment->started_at?->format('Y-m-d H:i:s'),
                $enrollment->completed_at?->format('Y-m-d H:i:s'),
                $enrollment->progress_percentage,
                $enrollment->payment_status,
                $enrollment->amount_paid,
                $enrollment->last_activity_at?->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return $filename;
    }

    public function saveSegment(string $name, array $filters, ?int $userId): EnrollmentSegment
    {
        return EnrollmentSegment::create([
            'name' => $name,
            'filters' => $filters,
            'created_by' => $userId,
            'is_system' => false,
        ]);
    }
}

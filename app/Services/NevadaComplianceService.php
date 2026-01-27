<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\NevadaCertificate;
use App\Models\NevadaComplianceLog;
use App\Models\NevadaSubmission;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NevadaComplianceService
{
    /**
     * Log activity for Nevada compliance
     */
    public function logActivity(UserCourseEnrollment $enrollment, string $type, array $details = []): void
    {
        NevadaComplianceLog::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'log_type' => $type,
            'chapter_id' => $details['chapter_id'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ]);
    }

    /**
     * Log user login
     */
    public function logLogin(User $user): void
    {
        // Get active Nevada enrollments for this user
        $enrollments = UserCourseEnrollment::where('user_id', $user->id)
            ->whereHas('course.nevadaCourse')
            ->where('status', 'active')
            ->get();

        foreach ($enrollments as $enrollment) {
            $this->logActivity($enrollment, 'login', [
                'login_time' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Log chapter progress
     */
    public function logChapterProgress($progress, string $action): void
    {
        if (! $progress->enrollment) {
            return;
        }

        $enrollment = $progress->enrollment;

        // Check if this is a Nevada course
        if (! $enrollment->course->nevadaCourse ?? false) {
            return;
        }

        $type = $action === 'start' ? 'chapter_start' : 'chapter_complete';

        $this->logActivity($enrollment, $type, [
            'chapter_id' => $progress->chapter_id,
            'chapter_title' => $progress->chapter->title ?? null,
            'time_spent' => $progress->time_spent ?? 0,
        ]);
    }

    /**
     * Log quiz attempt
     */
    public function logQuizAttempt($attempt): void
    {
        if (! $attempt->enrollment) {
            return;
        }

        $enrollment = $attempt->enrollment;

        // Check if this is a Nevada course
        if (! $enrollment->course->nevadaCourse ?? false) {
            return;
        }

        $passed = $attempt->score >= $attempt->passing_score;
        $type = $passed ? 'quiz_pass' : 'quiz_fail';

        $this->logActivity($enrollment, $type, [
            'quiz_id' => $attempt->quiz_id ?? null,
            'score' => $attempt->score,
            'passing_score' => $attempt->passing_score,
            'attempt_number' => $attempt->attempt_number ?? 1,
        ]);
    }

    /**
     * Log course completion
     */
    public function logCompletion(UserCourseEnrollment $enrollment): void
    {
        // Check if this is a Nevada course
        if (! $enrollment->course->nevadaCourse ?? false) {
            return;
        }

        $this->logActivity($enrollment, 'completion', [
            'completion_date' => $enrollment->completed_at?->toDateTimeString(),
            'total_time' => $enrollment->total_time_spent ?? 0,
        ]);
    }

    /**
     * Validate completion requirements
     */
    public function validateCompletionRequirements(UserCourseEnrollment $enrollment): array
    {
        $errors = [];
        $nevadaCourse = $enrollment->course->nevadaCourse ?? null;

        if (! $nevadaCourse) {
            $errors[] = 'Not a Nevada course';

            return $errors;
        }

        // Check time requirements
        if (! $this->validateTimeRequirements($enrollment)) {
            $errors[] = "Minimum time requirement of {$nevadaCourse->required_hours} hours not met";
        }

        // Check all chapters completed
        $totalChapters = $enrollment->course->chapters()->count();
        $completedChapters = $enrollment->progress()->where('completed', true)->count();

        if ($completedChapters < $totalChapters) {
            $errors[] = "Not all chapters completed ({$completedChapters}/{$totalChapters})";
        }

        // Check completion within allowed days
        if ($enrollment->enrolled_at) {
            $daysSinceEnrollment = $enrollment->enrolled_at->diffInDays(now());
            if ($daysSinceEnrollment > $nevadaCourse->max_completion_days) {
                $errors[] = "Course must be completed within {$nevadaCourse->max_completion_days} days";
            }
        }

        return $errors;
    }

    /**
     * Validate time requirements
     */
    public function validateTimeRequirements(UserCourseEnrollment $enrollment): bool
    {
        $nevadaCourse = $enrollment->course->nevadaCourse ?? null;

        if (! $nevadaCourse) {
            return false;
        }

        $requiredSeconds = $nevadaCourse->required_hours * 3600;
        $actualSeconds = $enrollment->total_time_spent ?? 0;

        return $actualSeconds >= $requiredSeconds;
    }

    /**
     * Generate Nevada certificate number
     */
    public function generateNevadaCertificateNumber(): string
    {
        $prefix = 'NV';
        $year = now()->format('Y');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        return "{$prefix}{$year}{$random}";
    }

    /**
     * Create Nevada certificate
     */
    public function createNevadaCertificate(Certificate $certificate): NevadaCertificate
    {
        $enrollment = $certificate->enrollment;

        $nevadaCertificate = NevadaCertificate::create([
            'certificate_id' => $certificate->id,
            'enrollment_id' => $enrollment->id,
            'nevada_certificate_number' => $this->generateNevadaCertificateNumber(),
            'completion_date' => $certificate->issue_date ?? now(),
            'submission_status' => 'pending',
        ]);

        $this->logActivity($enrollment, 'certificate', [
            'certificate_number' => $nevadaCertificate->nevada_certificate_number,
        ]);

        return $nevadaCertificate;
    }

    /**
     * Submit to state
     */
    public function submitToState(NevadaCertificate $certificate): NevadaSubmission
    {
        $submission = NevadaSubmission::create([
            'certificate_id' => $certificate->id,
            'submission_method' => 'electronic',
            'submission_date' => now(),
            'status' => 'pending',
        ]);

        try {
            // TODO: Implement actual state submission API call
            // For now, mark as sent
            $submission->update([
                'status' => 'sent',
                'confirmation_number' => 'CONF-'.strtoupper(substr(md5(uniqid()), 0, 10)),
            ]);

            $certificate->update([
                'submission_status' => 'submitted',
                'submission_date' => now(),
            ]);

            $this->logActivity($certificate->enrollment, 'submission', [
                'certificate_number' => $certificate->nevada_certificate_number,
                'submission_id' => $submission->id,
            ]);

        } catch (\Exception $e) {
            $submission->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $submission;
    }

    /**
     * Check submission status
     */
    public function checkSubmissionStatus(NevadaSubmission $submission): string
    {
        // TODO: Implement actual status check with state API
        return $submission->status;
    }

    /**
     * Get compliance report
     */
    public function getComplianceReport(Carbon $from, Carbon $to): array
    {
        $logs = NevadaComplianceLog::dateRange($from, $to)->get();

        return [
            'total_logs' => $logs->count(),
            'by_type' => $logs->groupBy('log_type')->map->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'unique_enrollments' => $logs->pluck('enrollment_id')->unique()->count(),
            'date_range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ];
    }

    /**
     * Get student activity log
     */
    public function getStudentActivityLog(UserCourseEnrollment $enrollment): Collection
    {
        return NevadaComplianceLog::where('enrollment_id', $enrollment->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Export compliance logs
     */
    public function exportComplianceLogs(array $filters): string
    {
        $query = NevadaComplianceLog::query();

        if (isset($filters['from']) && isset($filters['to'])) {
            $query->dateRange($filters['from'], $filters['to']);
        }

        if (isset($filters['log_type'])) {
            $query->byType($filters['log_type']);
        }

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $query->byEnrollment($filters['enrollment_id']);
        }

        $logs = $query->with(['user', 'enrollment'])->get();

        // Generate CSV
        $csv = "ID,Date/Time,User,Enrollment,Log Type,IP Address,Details\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->created_at->toDateTimeString(),
                $log->user->email ?? 'N/A',
                $log->enrollment_id ?? 'N/A',
                $log->log_type,
                $log->ip_address,
                json_encode($log->details)
            );
        }

        return $csv;
    }
}

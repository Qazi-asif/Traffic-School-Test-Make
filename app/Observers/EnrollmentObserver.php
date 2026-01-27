<?php

namespace App\Observers;

use App\Models\UserCourseEnrollment;

class EnrollmentObserver
{
    /**
     * Handle the UserCourseEnrollment "created" event.
     */
    public function created(UserCourseEnrollment $userCourseEnrollment): void
    {
        //
    }

    /**
     * Handle the UserCourseEnrollment "updated" event.
     */
    public function updated(UserCourseEnrollment $userCourseEnrollment): void
    {
        // Check if status changed to completed and certificate doesn't exist
        if ($userCourseEnrollment->isDirty('status') &&
            $userCourseEnrollment->status === 'completed' &&
            ! $userCourseEnrollment->certificate()->exists()) {

            $this->generateCertificate($userCourseEnrollment);
        }
    }

    /**
     * Generate certificate for completed enrollment and fire CourseCompleted event
     */
    private function generateCertificate(UserCourseEnrollment $enrollment): void
    {
        try {
            $enrollment->load(['user', 'course']);

            if (! $enrollment->user || ! $enrollment->course) {
                \Log::warning('Cannot generate certificate: missing user or course', ['enrollment_id' => $enrollment->id]);

                return;
            }

            $certificate = \App\Models\FloridaCertificate::create([
                'enrollment_id' => $enrollment->id,
                'dicds_certificate_number' => 'FL-'.date('Y').'-'.str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
                'student_name' => $enrollment->user->first_name.' '.$enrollment->user->last_name,
                'completion_date' => now(),
                'course_name' => $enrollment->course->title,
                'final_exam_score' => $enrollment->final_score ?? 0,
                'driver_license_number' => $enrollment->user->driver_license,
                'citation_number' => $enrollment->user->citation_number,
                'citation_county' => $enrollment->user->court_selected,
                'traffic_school_due_date' => $enrollment->user->due_year && $enrollment->user->due_month && $enrollment->user->due_day
                    ? \Carbon\Carbon::create($enrollment->user->due_year, $enrollment->user->due_month, $enrollment->user->due_day)
                    : now()->addDays(90),
                'student_address' => $enrollment->user->mailing_address,
                'student_date_of_birth' => $enrollment->user->birth_year && $enrollment->user->birth_month && $enrollment->user->birth_day
                    ? \Carbon\Carbon::create($enrollment->user->birth_year, $enrollment->user->birth_month, $enrollment->user->birth_day)
                    : null,
                'court_name' => $enrollment->user->court_selected,
                'state' => $enrollment->user->license_state ?? 'FL',
                'verification_hash' => \Illuminate\Support\Str::random(32),
                'is_sent_to_student' => false,
                'generated_at' => now(),
            ]);

            \Log::info('Certificate auto-generated for enrollment', ['enrollment_id' => $enrollment->id]);

            // Fire CourseCompleted event to trigger state transmissions
            event(new \App\Events\CourseCompleted($enrollment));

            // 🔥 FIX: Fire CertificateGenerated event to trigger email delivery
            event(new \App\Events\CertificateGenerated($certificate));

        } catch (\Exception $e) {
            \Log::error('Failed to auto-generate certificate', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the UserCourseEnrollment "deleted" event.
     */
    public function deleted(UserCourseEnrollment $userCourseEnrollment): void
    {
        //
    }

    /**
     * Handle the UserCourseEnrollment "restored" event.
     */
    public function restored(UserCourseEnrollment $userCourseEnrollment): void
    {
        //
    }

    /**
     * Handle the UserCourseEnrollment "force deleted" event.
     */
    public function forceDeleted(UserCourseEnrollment $userCourseEnrollment): void
    {
        //
    }
}

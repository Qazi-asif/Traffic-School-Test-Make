<?php

namespace App\Listeners;

use App\Events\CertificateGenerated;
use App\Notifications\CertificateGeneratedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCertificateEmail implements ShouldQueue
{
    public function handle(CertificateGenerated $event)
    {
        $certificate = $event->certificate;
        
        // Get user from enrollment relationship
        if ($certificate->enrollment && $certificate->enrollment->user) {
            $user = $certificate->enrollment->user;
        } else {
            // Fallback: try to find user by enrollment_id
            $enrollment = \App\Models\UserCourseEnrollment::with('user')->find($certificate->enrollment_id);
            $user = $enrollment ? $enrollment->user : null;
        }
        
        if ($user) {
            $user->notify(new CertificateGeneratedNotification($certificate));
        } else {
            \Log::error('Cannot send certificate email: user not found', [
                'certificate_id' => $certificate->id,
                'enrollment_id' => $certificate->enrollment_id
            ]);
        }
    }
}

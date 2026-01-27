<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCourseEnrollment;

class CertificateAccessService
{
    public function handleCertificateDownload(User $user, $enrollmentId = null)
    {
        // Skip locking for admin and super-admin roles
        $userRole = $user->role ? $user->role->slug : null;
        if (in_array($userRole, ['admin', 'super-admin', 'super_admin'])) {
            return ['status' => 'success', 'message' => 'Admin users are exempt from access revocation'];
        }

        // If specific enrollment ID provided, only revoke that one
        if ($enrollmentId) {
            \Log::info('Looking for enrollment', ['user_id' => $user->id, 'enrollment_id' => $enrollmentId]);

            $enrollment = UserCourseEnrollment::where('user_id', $user->id)
                ->where('id', $enrollmentId)
                ->where('access_revoked', false)
                ->first();

            \Log::info('Enrollment found', ['found' => $enrollment ? 'yes' : 'no']);

            if ($enrollment) {
                $result = $enrollment->update([
                    'access_revoked' => true,
                    'access_revoked_at' => now(),
                ]);

                \Log::info('Update result', ['success' => $result, 'enrollment_id' => $enrollment->id]);

                // Refresh to verify
                $enrollment->refresh();
                \Log::info('After refresh', ['access_revoked' => $enrollment->access_revoked]);
            }
        }

        // Check if ALL enrollments have been revoked
        $totalEnrollments = UserCourseEnrollment::where('user_id', $user->id)->count();
        $revokedEnrollments = UserCourseEnrollment::where('user_id', $user->id)
            ->where('access_revoked', true)
            ->count();

        // Only lock account if ALL courses have been downloaded
        if ($totalEnrollments > 0 && $totalEnrollments === $revokedEnrollments) {
            $user->update([
                'account_locked' => true,
                'lock_reason' => 'All course certificates downloaded',
                'locked_at' => now(),
            ]);

            return [
                'status' => 'account_locked',
                'message' => 'Your account has been locked after downloading all certificates. Please contact support to regain access.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Certificate downloaded. You still have access to other courses.',
        ];
    }
}

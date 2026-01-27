<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\UserCourseEnrollment;
use Closure;
use Illuminate\Http\Request;

class PaymentMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Only intercept enrollment requests
        if ($request->route()->getName() !== 'enrollment.store' &&
            ! $request->is('web/enrollments') &&
            ! $request->is('api/enrollments')) {
            return $next($request);
        }

        $courseId = $request->course_id;
        $course = $this->findCourse($courseId);

        if (! $course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        // Check if course requires payment
        if ($course->price > 0) {
            // Check if user already has pending payment for this course
            $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
                ->where('course_id', $this->getRealCourseId($courseId))
                ->where('payment_status', 'pending')
                ->first();

            if ($existingEnrollment) {
                return redirect()->route('payment.show', $existingEnrollment->id);
            }

            // Redirect to payment page
            return redirect()->route('payment.create', [
                'course_id' => $courseId,
                'citation_number' => $request->citation_number,
                'court_date' => $request->court_date,
            ]);
        }

        return $next($request);
    }

    private function findCourse($courseId)
    {
        if (str_starts_with($courseId, 'florida_')) {
            $realId = str_replace('florida_', '', $courseId);

            return FloridaCourse::find($realId);
        } elseif (str_starts_with($courseId, 'courses_')) {
            $realId = str_replace('courses_', '', $courseId);

            return Course::find($realId);
        } else {
            return Course::find($courseId) ?? FloridaCourse::find($courseId);
        }
    }

    private function getRealCourseId($courseId)
    {
        if (str_starts_with($courseId, 'florida_')) {
            return str_replace('florida_', '', $courseId);
        } elseif (str_starts_with($courseId, 'courses_')) {
            return str_replace('courses_', '', $courseId);
        }

        return $courseId;
    }
}

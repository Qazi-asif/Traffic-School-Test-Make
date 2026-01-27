<?php

namespace App\Http\Middleware;

use App\Models\Review;
use Closure;
use Illuminate\Http\Request;

class CheckCourseReview
{
    public function handle(Request $request, Closure $next)
    {
        $enrollmentId = $request->query('enrollment_id');

        if (! $enrollmentId) {
            return $next($request);
        }

        $review = Review::where('user_id', auth()->id())
            ->where('enrollment_id', $enrollmentId)
            ->first();

        if ($review) {
            return redirect('/certificate?'.$request->getQueryString());
        }

        return redirect('/review-course?enrollment_id='.$enrollmentId.'&'.$request->getQueryString());
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function show(Request $request)
    {
        $enrollmentId = $request->query('enrollment_id');
        $courseName = $request->query('course_name');
        $completionDate = $request->query('completion_date');
        $score = $request->query('score');

        return view('review-course', compact('enrollmentId', 'courseName', 'completionDate', 'score'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
            'course_name' => 'required|string',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'enrollment_id' => $validated['enrollment_id'],
            'course_name' => $validated['course_name'],
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'],
        ]);

        return redirect('/certificate?'.http_build_query($request->query()));
    }
}

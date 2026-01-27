<?php

namespace App\Http\Controllers;

use App\Models\InstructorCourseAssignment;
use Illuminate\Http\Request;

class InstructorCourseController extends Controller
{
    public function assign(Request $request, $id)
    {
        $validated = $request->validate([
            'course_type' => 'required|in:BDI,ADI,TLSAE',
            'delivery_type' => 'required|in:internet,in_person,cd_rom,video,dvd',
            'status_date' => 'required|date',
        ]);

        $assignment = InstructorCourseAssignment::create([
            'instructor_id' => $id,
            'course_type' => $validated['course_type'],
            'delivery_type' => $validated['delivery_type'],
            'status' => 'active',
            'status_date' => $validated['status_date'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        return response()->json($assignment, 201);
    }

    public function update(Request $request, $instructorId, $assignmentId)
    {
        $assignment = InstructorCourseAssignment::findOrFail($assignmentId);
        $assignment->update($request->all());

        return response()->json($assignment);
    }

    public function authorizedCourses($id)
    {
        $courses = InstructorCourseAssignment::where('instructor_id', $id)
            ->where('status', 'active')
            ->get();

        return response()->json($courses);
    }
}

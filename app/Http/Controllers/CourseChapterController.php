<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;

class CourseChapterController extends Controller
{
    public function index($courseId)
    {
        $chapters = Chapter::where('course_id', $courseId)
            ->orderBy('order_index')
            ->get();

        return response()->json(['data' => $chapters]);
    }

    public function store(Request $request, $courseId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'order_index' => 'required|integer|min:1',
            'duration' => 'required|integer|min:1',
            'required_min_time' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['course_id'] = $courseId;
        $validated['course_table'] = 'courses'; // Set default course table
        $chapter = Chapter::create($validated);

        return response()->json([
            'message' => 'Chapter created successfully',
            'data' => $chapter,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'order_index' => 'required|integer|min:1',
            'duration' => 'required|integer|min:1',
            'required_min_time' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $chapter = Chapter::findOrFail($id);
        $chapter->update($validated);

        return response()->json([
            'message' => 'Chapter updated successfully',
            'data' => $chapter,
        ]);
    }

    public function destroy($id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return response()->json([
            'message' => 'Chapter deleted successfully',
        ]);
    }
}

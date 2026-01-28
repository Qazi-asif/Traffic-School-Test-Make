<?php

namespace App\Http\Controllers\Admin\Texas;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['chapters'])
            ->where('state', 'texas')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('course_type')) {
            $query->where('course_type', $request->course_type);
        }

        $courses = $query->paginate(20);

        return view('admin.texas.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.texas.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_type' => 'required|string',
            'delivery_type' => 'required|in:In Person,Internet,CD-Rom,Video,DVD',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'passing_score' => 'required|integer|min:1|max:100',
            'certificate_type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'state' => 'texas',
            'course_type' => $request->course_type,
            'delivery_type' => $request->delivery_type,
            'duration' => $request->duration,
            'price' => $request->price,
            'passing_score' => $request->passing_score,
            'certificate_type' => $request->certificate_type,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.texas.courses.show', $course)
            ->with('success', 'Texas course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['chapters.questions', 'enrollments.user']);
        
        $stats = [
            'total_chapters' => $course->chapters->count(),
            'total_questions' => $course->chapters->sum(function($chapter) {
                return $chapter->questions->count();
            }),
            'total_enrollments' => $course->enrollments->count(),
            'active_enrollments' => $course->enrollments->where('status', 'active')->count(),
            'completed_enrollments' => $course->enrollments->whereNotNull('completed_at')->count(),
            'total_revenue' => $course->enrollments->sum('amount_paid'),
        ];

        $recentEnrollments = $course->enrollments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.texas.courses.show', compact('course', 'stats', 'recentEnrollments'));
    }

    public function edit(Course $course)
    {
        return view('admin.texas.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_type' => 'required|string',
            'delivery_type' => 'required|in:In Person,Internet,CD-Rom,Video,DVD',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'passing_score' => 'required|integer|min:1|max:100',
            'certificate_type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'course_type' => $request->course_type,
            'delivery_type' => $request->delivery_type,
            'duration' => $request->duration,
            'price' => $request->price,
            'passing_score' => $request->passing_score,
            'certificate_type' => $request->certificate_type,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.texas.courses.show', $course)
            ->with('success', 'Texas course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if ($course->enrollments()->count() > 0) {
            return redirect()->route('admin.texas.courses.index')
                ->with('error', 'Cannot delete course with existing enrollments.');
        }

        $course->delete();

        return redirect()->route('admin.texas.courses.index')
            ->with('success', 'Texas course deleted successfully.');
    }

    public function toggleStatus(Course $course)
    {
        $course->update(['is_active' => !$course->is_active]);

        $status = $course->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Course {$status} successfully.");
    }
}
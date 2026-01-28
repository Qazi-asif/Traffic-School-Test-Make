<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\FloridaCourse;
use App\Models\MissouriCourse;
use App\Models\TexasCourse;
use App\Models\DelawareCourse;

class BulkUploadApiController extends Controller
{
    /**
     * Get courses by type
     */
    public function getCourses($courseType)
    {
        switch ($courseType) {
            case 'florida_courses':
                $courses = FloridaCourse::select('id', 'title')->orderBy('title')->get();
                break;
            case 'missouri_courses':
                $courses = MissouriCourse::select('id', 'title')->orderBy('title')->get();
                break;
            case 'texas_courses':
                $courses = TexasCourse::select('id', 'title')->orderBy('title')->get();
                break;
            case 'delaware_courses':
                $courses = DelawareCourse::select('id', 'title')->orderBy('title')->get();
                break;
            case 'courses':
            default:
                $courses = Course::select('id', 'title')->orderBy('title')->get();
                break;
        }

        return response()->json($courses);
    }

    /**
     * Get chapters by course
     */
    public function getChapters($courseType, $courseId)
    {
        $chapters = Chapter::where('course_id', $courseId)
            ->where('course_table', $courseType)
            ->select('id', 'title', 'order_index')
            ->orderBy('order_index')
            ->get();

        return response()->json($chapters);
    }

    /**
     * Get content statistics
     */
    public function getStats()
    {
        $stats = [
            'courses' => Course::count(),
            'florida_courses' => FloridaCourse::count(),
            'missouri_courses' => MissouriCourse::count(),
            'texas_courses' => TexasCourse::count(),
            'delaware_courses' => DelawareCourse::count(),
            'total_chapters' => Chapter::count(),
            'total_questions' => \DB::table('chapter_questions')->count(),
            'recent_uploads' => $this->getRecentUploads()
        ];

        return response()->json($stats);
    }

    /**
     * Get recent upload activity
     */
    private function getRecentUploads()
    {
        return [
            'chapters_today' => Chapter::whereDate('created_at', today())->count(),
            'questions_today' => \DB::table('chapter_questions')->whereDate('created_at', today())->count(),
            'chapters_week' => Chapter::where('created_at', '>=', now()->subWeek())->count(),
            'questions_week' => \DB::table('chapter_questions')->where('created_at', '>=', now()->subWeek())->count()
        ];
    }
}
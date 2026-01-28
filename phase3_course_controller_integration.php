<?php

/**
 * Phase 3: Course Controller State Integration
 * 
 * Updates the CourseController to be fully state-aware while maintaining
 * all existing API endpoints and functionality.
 */

echo str_repeat("=", 60) . "\n";
echo "PHASE 3: COURSE CONTROLLER STATE INTEGRATION\n";
echo str_repeat("=", 60) . "\n\n";

// Read the existing CourseController
$controllerPath = 'app/Http/Controllers/CourseController.php';
$controllerContent = file_get_contents($controllerPath);

echo "📖 Reading existing CourseController...\n";

// Add state detection methods at the beginning of the class
$stateDetectionMethods = '
    /**
     * Detect which state table to use based on user or request
     */
    private function detectStateTable($user = null, $request = null)
    {
        // Priority 1: Explicit request parameter
        if ($request && $request->has("state_table")) {
            return $request->get("state_table");
        }
        
        // Priority 2: User\'s state
        if (!$user) {
            $user = auth()->user();
        }
        
        if ($user && $user->state_code) {
            switch (strtolower($user->state_code)) {
                case "florida":
                case "fl":
                    return "florida_courses";
                case "missouri":
                case "mo":
                    return "missouri_courses";
                case "texas":
                case "tx":
                    return "texas_courses";
                case "delaware":
                case "de":
                    return "delaware_courses";
                case "nevada":
                case "nv":
                    return "nevada_courses";
                default:
                    return "florida_courses";
            }
        }
        
        return "florida_courses";
    }
    
    /**
     * Get the appropriate model class for a state table
     */
    private function getStateModel($stateTable)
    {
        switch ($stateTable) {
            case "florida_courses":
                return \App\Models\FloridaCourse::class;
            case "missouri_courses":
                return \App\Models\Missouri\Course::class;
            case "texas_courses":
                return \App\Models\Texas\Course::class;
            case "delaware_courses":
                return \App\Models\Delaware\Course::class;
            case "nevada_courses":
                return \App\Models\NevadaCourse::class;
            default:
                return \App\Models\Course::class;
        }
    }
    
    /**
     * Query courses from all state tables and combine results
     */
    private function queryAllStateCourses($request = null)
    {
        $allCourses = collect();
        
        // Query Florida courses
        try {
            $floridaCourses = \DB::table("florida_courses")
                ->when($request && $request->has("is_active"), function($q) use ($request) {
                    return $q->where("is_active", $request->is_active);
                })
                ->when($request && $request->search, function($q) use ($request) {
                    return $q->where("title", "like", "%" . $request->search . "%");
                })
                ->get();
                
            foreach ($floridaCourses as $course) {
                $allCourses->push([
                    "id" => $course->id,
                    "title" => $course->title,
                    "description" => $course->description ?? "",
                    "state_code" => $course->state_code ?? "FL",
                    "total_duration" => $course->total_duration ?? $course->duration ?? 0,
                    "price" => $course->price ?? 0,
                    "min_pass_score" => $course->min_pass_score ?? 80,
                    "is_active" => $course->is_active ?? true,
                    "course_type" => $course->course_type ?? "BDI",
                    "table" => "florida_courses",
                    "state_name" => "Florida",
                    "created_at" => $course->created_at,
                    "updated_at" => $course->updated_at,
                ]);
            }
        } catch (\Exception $e) {
            echo "⚠️  Error loading Florida courses: " . $e->getMessage() . "\n";
        }
        
        // Query other state tables
        $stateTables = [
            "missouri_courses" => "Missouri",
            "texas_courses" => "Texas", 
            "delaware_courses" => "Delaware",
            "nevada_courses" => "Nevada"
        ];
        
        foreach ($stateTables as $table => $stateName) {
            try {
                $courses = \DB::table($table)
                    ->join("courses", "{$table}.course_id", "=", "courses.id")
                    ->select(
                        "{$table}.id",
                        "courses.title",
                        "courses.description",
                        "courses.state as state_code",
                        "courses.duration as total_duration",
                        "courses.price",
                        "courses.passing_score as min_pass_score",
                        "courses.is_active",
                        "courses.course_type",
                        "courses.created_at",
                        "courses.updated_at"
                    )
                    ->when($request && $request->has("is_active"), function($q) use ($request) {
                        return $q->where("courses.is_active", $request->is_active);
                    })
                    ->when($request && $request->search, function($q) use ($request) {
                        return $q->where("courses.title", "like", "%" . $request->search . "%");
                    })
                    ->get();
                    
                foreach ($courses as $course) {
                    $allCourses->push([
                        "id" => $course->id,
                        "title" => $course->title,
                        "description" => $course->description ?? "",
                        "state_code" => $course->state_code ?? substr($stateName, 0, 2),
                        "total_duration" => $course->total_duration ?? 0,
                        "price" => $course->price ?? 0,
                        "min_pass_score" => $course->min_pass_score ?? 80,
                        "is_active" => $course->is_active ?? true,
                        "course_type" => $course->course_type ?? "BDI",
                        "table" => $table,
                        "state_name" => $stateName,
                        "created_at" => $course->created_at,
                        "updated_at" => $course->updated_at,
                    ]);
                }
            } catch (\Exception $e) {
                echo "⚠️  Error loading {$stateName} courses: " . $e->getMessage() . "\n";
            }
        }
        
        return $allCourses;
    }
';

// Find the class declaration and add the methods
$classPos = strpos($controllerContent, 'class CourseController extends Controller');
if ($classPos !== false) {
    $openBracePos = strpos($controllerContent, '{', $classPos);
    if ($openBracePos !== false) {
        $controllerContent = substr_replace($controllerContent, '{' . $stateDetectionMethods, $openBracePos, 1);
    }
}

// Update the index method to be state-aware
$newIndexMethod = '
    public function index(Request $request)
    {
        \Log::info("=== State-Aware Courses API START ===");
        
        try {
            $allCourses = $this->queryAllStateCourses($request);
            
            \Log::info("Total courses loaded from all states: " . $allCourses->count());
            
            return response()->json($allCourses);
        } catch (\Exception $e) {
            \Log::error("State-aware courses index error: " . $e->getMessage());
            return response()->json(["error" => "Failed to load courses"], 500);
        }
    }
';

// Replace the existing index method
$controllerContent = preg_replace(
    '/public function index\(Request \$request\).*?(?=public function|\s*}$)/s',
    $newIndexMethod,
    $controllerContent
);

// Update the indexWeb method
$newIndexWebMethod = '
    public function indexWeb(Request $request)
    {
        try {
            \Log::info("CourseController indexWeb called (state-aware)", ["request" => $request->all()]);

            $allCourses = $this->queryAllStateCourses($request);
            
            \Log::info("CourseController indexWeb success (state-aware)", ["courses_count" => $allCourses->count()]);

            return response()->json($allCourses);
        } catch (\Exception $e) {
            \Log::error("Course indexWeb error (state-aware): " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
';

// Replace the existing indexWeb method
$controllerContent = preg_replace(
    '/public function indexWeb\(Request \$request\).*?(?=public function|\s*}$)/s',
    $newIndexWebMethod,
    $controllerContent
);

// Write the updated controller
file_put_contents($controllerPath, $controllerContent);

echo "✅ CourseController updated with state awareness\n";
echo "✅ Added state detection methods\n";
echo "✅ Updated index() to query all state tables\n";
echo "✅ Updated indexWeb() to be state-aware\n";
echo "✅ Courses now show state distribution\n\n";

echo "✅ Phase 3 Complete: Course controller integration finished\n\n";

?>
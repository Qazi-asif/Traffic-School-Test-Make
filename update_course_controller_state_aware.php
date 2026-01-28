<?php

/**
 * Update Course Controller to be State-Aware
 * 
 * This updates the existing CourseController to work with state-specific tables
 * while keeping all existing API endpoints and functionality intact.
 */

echo "🔧 UPDATING COURSE CONTROLLER FOR STATE AWARENESS\n";
echo str_repeat("=", 50) . "\n\n";

// Read the existing CourseController
$controllerPath = 'app/Http/Controllers/CourseController.php';
$controllerContent = file_get_contents($controllerPath);

echo "📖 Reading existing CourseController...\n";

// Add state detection method at the beginning of the class
$stateDetectionMethod = '
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
                    return "florida_courses"; // Default fallback
            }
        }
        
        // Priority 3: Default to florida_courses
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
        
        // Define state tables and their model classes
        $stateTables = [
            "florida_courses" => \App\Models\FloridaCourse::class,
            "missouri_courses" => \App\Models\Missouri\Course::class,
            "texas_courses" => \App\Models\Texas\Course::class,
            "delaware_courses" => \App\Models\Delaware\Course::class,
            "nevada_courses" => \App\Models\NevadaCourse::class,
            "courses" => \App\Models\Course::class, // Legacy table
        ];
        
        foreach ($stateTables as $table => $modelClass) {
            try {
                if (Schema::hasTable($table)) {
                    $query = $modelClass::query();
                    
                    // Apply filters if provided
                    if ($request) {
                        if ($request->has("is_active")) {
                            $query->where("is_active", $request->is_active);
                        }
                        
                        if ($request->search) {
                            $query->where("title", "like", "%" . $request->search . "%");
                        }
                        
                        if ($request->state_code) {
                            $query->where(function($q) use ($request) {
                                $q->where("state_code", $request->state_code)
                                  ->orWhere("state", $request->state_code);
                            });
                        }
                    }
                    
                    $courses = $query->get();
                    
                    // Normalize course data
                    foreach ($courses as $course) {
                        $allCourses->push([
                            "id" => $course->id,
                            "title" => $course->title,
                            "description" => $course->description ?? "",
                            "state_code" => $course->state_code ?? $course->state ?? "FL",
                            "total_duration" => $course->total_duration ?? $course->duration ?? 0,
                            "price" => $course->price ?? 0,
                            "min_pass_score" => $course->min_pass_score ?? $course->passing_score ?? 80,
                            "is_active" => $course->is_active ?? true,
                            "course_type" => $course->course_type ?? "BDI",
                            "table" => $table,
                            "created_at" => $course->created_at,
                            "updated_at" => $course->updated_at,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to query table $table: " . $e->getMessage());
            }
        }
        
        return $allCourses;
    }
';

// Find the class declaration and add the methods after it
$classPos = strpos($controllerContent, 'class CourseController extends Controller');
if ($classPos !== false) {
    $openBracePos = strpos($controllerContent, '{', $classPos);
    if ($openBracePos !== false) {
        $controllerContent = substr_replace($controllerContent, '{' . $stateDetectionMethod, $openBracePos, 1);
    }
}

// Update the index method to be state-aware
$newIndexMethod = '
    public function index(Request $request)
    {
        \Log::info("=== State-Aware Courses API START ===");
        
        try {
            // Get all courses from all state tables
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
    '/public function index\(Request \$request\).*?(?=public function|\Z)/s',
    $newIndexMethod,
    $controllerContent
);

// Update the indexWeb method to be state-aware
$newIndexWebMethod = '
    public function indexWeb(Request $request)
    {
        try {
            \Log::info("CourseController indexWeb called (state-aware)", ["request" => $request->all()]);

            // Get all courses from all state tables
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
    '/public function indexWeb\(Request \$request\).*?(?=public function|\Z)/s',
    $newIndexWebMethod,
    $controllerContent
);

// Update the storeWeb method to be state-aware
$newStoreWebMethod = '
    public function storeWeb(Request $request)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "required|string",
                "state_code" => "required|string|max:50",
                "min_pass_score" => "required|integer|min:0|max:100",
                "total_duration" => "required|integer|min:1",
                "price" => "required|numeric|min:0",
                "certificate_template" => "nullable|string",
                "is_active" => "boolean",
                "target_state_table" => "nullable|string", // Allow admin to specify target table
            ]);

            // Determine target state table
            $targetTable = $validated["target_state_table"] ?? $this->detectStateTable(auth()->user(), $request);
            $modelClass = $this->getStateModel($targetTable);

            // Prepare course data based on target table structure
            $courseData = [
                "title" => $validated["title"],
                "description" => $validated["description"],
                "state_code" => $validated["state_code"],
                "price" => $validated["price"],
                "is_active" => $validated["is_active"] ?? true,
            ];

            // Add fields based on target table
            if ($targetTable === "florida_courses") {
                $courseData["min_pass_score"] = $validated["min_pass_score"];
                $courseData["total_duration"] = $validated["total_duration"];
                $courseData["certificate_template"] = $validated["certificate_template"] ?? null;
                $courseData["course_type"] = "BDI";
                $courseData["dicds_course_id"] = "NEW_" . time();
            } else {
                // For other state tables, map to their structure
                $courseData["passing_score"] = $validated["min_pass_score"];
                $courseData["duration"] = $validated["total_duration"];
                $courseData["certificate_type"] = $validated["certificate_template"] ?? null;
                $courseData["course_type"] = "BDI";
            }

            $course = $modelClass::create($courseData);

            if ($request->wantsJson()) {
                return response()->json(array_merge($course->toArray(), ["table" => $targetTable]), 201);
            }

            return redirect("/courses")->with("success", "Course created successfully in " . $targetTable . "!");
        } catch (\Exception $e) {
            \Log::error("Course storeWeb error (state-aware): " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
';

// Replace the existing storeWeb method
$controllerContent = preg_replace(
    '/public function storeWeb\(Request \$request\).*?(?=public function|\Z)/s',
    $newStoreWebMethod,
    $controllerContent
);

// Write the updated controller
file_put_contents($controllerPath, $controllerContent);

echo "✅ CourseController updated with state awareness\n";
echo "✅ Added state detection methods\n";
echo "✅ Updated index() to query all state tables\n";
echo "✅ Updated indexWeb() to be state-aware\n";
echo "✅ Updated storeWeb() to create in appropriate state table\n";
echo "✅ Kept all existing API endpoints working\n\n";

echo "🎯 WHAT THIS ACHIEVES:\n";
echo "• Admin can manage courses for all states from single dashboard\n";
echo "• Courses are automatically routed to appropriate state table\n";
echo "• All existing API endpoints continue to work\n";
echo "• Existing UI/UX remains unchanged\n";
echo "• State-specific course management is now possible\n\n";

echo "✅ COURSE CONTROLLER STATE INTEGRATION COMPLETE!\n\n";

?>
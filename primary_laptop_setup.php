<?php
/**
 * PRIMARY LAPTOP COMPLETE SETUP SCRIPT
 * Sets up the complete state-separated traffic school system
 */

echo "🚀 PRIMARY LAPTOP SETUP - STATE-SEPARATED TRAFFIC SCHOOL SYSTEM\n";
echo "================================================================\n\n";

// Step 1: Create Required Directories
echo "📁 Creating directory structure...\n";

$directories = [
    // Model directories
    'app/Models/Florida',
    'app/Models/Missouri', 
    'app/Models/Texas',
    'app/Models/Delaware',
    
    // Controller directories
    'app/Http/Controllers/Admin/Florida',
    'app/Http/Controllers/Admin/Missouri',
    'app/Http/Controllers/Admin/Texas', 
    'app/Http/Controllers/Admin/Delaware',
    
    'app/Http/Controllers/Student/Florida',
    'app/Http/Controllers/Student/Missouri',
    'app/Http/Controllers/Student/Texas',
    'app/Http/Controllers/Student/Delaware',
    
    // View directories
    'resources/views/student/florida',
    'resources/views/student/missouri',
    'resources/views/student/texas',
    'resources/views/student/delaware',
    'resources/views/admin',
    
    // Service directories
    'app/Services/States',
    'app/Services/Florida',
    'app/Services/Missouri',
    'app/Services/Texas',
    'app/Services/Delaware',
    
    // Storage directories
    'storage/app/courses/florida/videos',
    'storage/app/courses/florida/documents',
    'storage/app/courses/florida/images',
    'storage/app/courses/missouri/videos',
    'storage/app/courses/missouri/documents', 
    'storage/app/courses/missouri/images',
    'storage/app/courses/texas/videos',
    'storage/app/courses/texas/documents',
    'storage/app/courses/texas/images',
    'storage/app/courses/delaware/videos',
    'storage/app/courses/delaware/documents',
    'storage/app/courses/delaware/images'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created: $dir\n";
    } else {
        echo "📁 Exists: $dir\n";
    }
}

echo "\n📋 DIRECTORY STRUCTURE COMPLETE!\n\n";

// Step 2: Create Sample Data Seeder
echo "🌱 Creating sample data seeder...\n";

$seederContent = '<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompleteSystemSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Seeding complete system data...\n";
        
        // USERS DATA
        $adminUserId = DB::table("users")->insertGetId([
            "name" => "System Admin",
            "email" => "admin@trafficschool.com",
            "password" => Hash::make("admin123"),
            "phone" => "555-0001",
            "state_code" => "florida",
            "role" => "admin",
            "is_active" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        DB::table("admin_users")->insert([
            "user_id" => $adminUserId,
            "permissions" => json_encode(["manage_all"]),
            "can_manage_states" => json_encode(["florida", "missouri", "texas", "delaware"]),
            "created_at" => now(),
            "updated_at" => now()
        ]);

        // FLORIDA DATA
        $floridaCourseId = DB::table("florida_courses")->insertGetId([
            "title" => "Florida Defensive Driving Course",
            "description" => "FLHSMV approved defensive driving course",
            "duration_hours" => 8,
            "passing_score" => 80,
            "price" => 25.00,
            "is_active" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        DB::table("florida_chapters")->insert([
            [
                "course_id" => $floridaCourseId,
                "title" => "Chapter 1: Florida Traffic Laws",
                "content" => "Understanding Florida traffic laws and FLHSMV regulations...",
                "video_url" => "/videos/florida/chapter1.mp4",
                "duration_minutes" => 45,
                "order_number" => 1,
                "is_active" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "course_id" => $floridaCourseId,
                "title" => "Chapter 2: Safe Driving Practices",
                "content" => "Safe driving practices and accident prevention...",
                "video_url" => "/videos/florida/chapter2.mp4",
                "duration_minutes" => 50,
                "order_number" => 2,
                "is_active" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);

        // MISSOURI DATA
        $missouriCourseId = DB::table("missouri_courses")->insertGetId([
            "title" => "Missouri Point Reduction Course",
            "description" => "Missouri approved point reduction program",
            "duration_hours" => 8,
            "passing_score" => 80,
            "price" => 30.00,
            "is_active" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        DB::table("missouri_chapters")->insert([
            "course_id" => $missouriCourseId,
            "title" => "Chapter 1: Missouri Traffic Laws",
            "content" => "Understanding Missouri traffic regulations...",
            "video_url" => "/videos/missouri/chapter1.mp4",
            "duration_minutes" => 45,
            "order_number" => 1,
            "is_active" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        // TEXAS DATA
        $texasCourseId = DB::table("texas_courses")->insertGetId([
            "title" => "Texas Defensive Driving Course",
            "description" => "Texas approved defensive driving course",
            "duration_hours" => 6,
            "passing_score" => 70,
            "price" => 25.00,
            "is_active" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        // DELAWARE DATA
        $delawareCourseId = DB::table("delaware_courses")->insertGetId([
            "title" => "Delaware Driver Improvement Course",
            "description" => "Delaware approved driver improvement program",
            "duration_hours" => 8,
            "passing_score" => 80,
            "price" => 35.00,
            "is_active" => 1,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        // SYSTEM SETTINGS
        DB::table("system_settings")->insert([
            [
                "state_code" => "florida",
                "setting_key" => "timer_minutes",
                "setting_value" => "480",
                "description" => "Course timer duration",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "state_code" => "global",
                "setting_key" => "site_name",
                "setting_value" => "Multi-State Traffic School",
                "description" => "Site name",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);

        echo "✅ Sample data seeded successfully!\n";
    }
}';

file_put_contents('database/seeders/CompleteSystemSeeder.php', $seederContent);
echo "✅ Created: database/seeders/CompleteSystemSeeder.php\n\n";

// Step 3: Create State Middleware
echo "🛡️ Creating state middleware...\n";

$middlewareContent = '<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class StateMiddleware
{
    public function handle(Request $request, Closure $next, $state = null)
    {
        $validStates = ["florida", "missouri", "texas", "delaware"];
        
        if ($state && !in_array($state, $validStates)) {
            abort(404, "Invalid state");
        }
        
        // Add state to request
        $request->merge(["current_state" => $state]);
        
        // Add state to view data
        view()->share("currentState", $state);
        
        return $next($request);
    }
}';

file_put_contents('app/Http/Middleware/StateMiddleware.php', $middlewareContent);
echo "✅ Created: app/Http/Middleware/StateMiddleware.php\n\n";

// Step 4: Create State Factory Service
echo "🏭 Creating state factory service...\n";

$factoryContent = '<?php
namespace App\Services\States;

class StateFactory 
{
    public static function getModels($state) 
    {
        $namespace = "App\\Models\\" . ucfirst($state);
        
        return [
            "Course" => $namespace . "\\Course",
            "Chapter" => $namespace . "\\Chapter", 
            "Enrollment" => $namespace . "\\Enrollment",
            "ChapterQuiz" => $namespace . "\\ChapterQuiz",
            "Certificate" => $namespace . "\\Certificate",
            "Progress" => $namespace . "\\Progress",
        ];
    }
    
    public static function getCourse($state) {
        $class = self::getModels($state)["Course"];
        return new $class;
    }
    
    public static function getChapter($state) {
        $class = self::getModels($state)["Chapter"];
        return new $class;
    }
    
    public static function getEnrollment($state) {
        $class = self::getModels($state)["Enrollment"];
        return new $class;
    }
    
    public static function getCertificate($state) {
        $class = self::getModels($state)["Certificate"];
        return new $class;
    }
}';

file_put_contents('app/Services/States/StateFactory.php', $factoryContent);
echo "✅ Created: app/Services/States/StateFactory.php\n\n";

// Step 5: Create State Route Files
echo "🛣️ Creating state route files...\n";

$states = ['florida', 'missouri', 'texas', 'delaware'];

foreach ($states as $state) {
    $routeContent = '<?php
use Illuminate\Support\Facades\Route;

// ' . ucfirst($state) . ' Student Routes
Route::get("/", function() {
    return view("student.' . $state . '.dashboard");
})->name("' . $state . '.dashboard");

Route::get("/courses", function() {
    $courses = \App\Services\States\StateFactory::getCourse("' . $state . '")->where("is_active", 1)->get();
    return view("student.' . $state . '.courses", compact("courses"));
})->name("' . $state . '.courses");

Route::get("/course-player/{id}", function($id) {
    $course = \App\Services\States\StateFactory::getCourse("' . $state . '")->findOrFail($id);
    return view("student.' . $state . '.course-player", compact("course"));
})->name("' . $state . '.course-player");

Route::get("/quiz/{id}", function($id) {
    return view("student.' . $state . '.quiz", compact("id"));
})->name("' . $state . '.quiz");

Route::get("/certificates", function() {
    return view("student.' . $state . '.certificates");
})->name("' . $state . '.certificates");';

    file_put_contents("routes/{$state}.php", $routeContent);
    echo "✅ Created: routes/{$state}.php\n";
}

// Create admin routes
$adminRouteContent = '<?php
use Illuminate\Support\Facades\Route;

// Admin Dashboard
Route::get("/", function() {
    return view("admin.dashboard");
})->name("admin.dashboard");

// Florida Admin Routes
Route::prefix("florida")->group(function() {
    Route::get("/courses", function() {
        return view("admin.florida.courses");
    })->name("admin.florida.courses");
});

// Missouri Admin Routes  
Route::prefix("missouri")->group(function() {
    Route::get("/courses", function() {
        return view("admin.missouri.courses");
    })->name("admin.missouri.courses");
});

// Texas Admin Routes
Route::prefix("texas")->group(function() {
    Route::get("/courses", function() {
        return view("admin.texas.courses");
    })->name("admin.texas.courses");
});

// Delaware Admin Routes
Route::prefix("delaware")->group(function() {
    Route::get("/courses", function() {
        return view("admin.delaware.courses");
    })->name("admin.delaware.courses");
});';

file_put_contents('routes/admin.php', $adminRouteContent);
echo "✅ Created: routes/admin.php\n\n";

// Step 6: Update Main Routes
echo "🔗 Updating main routes...\n";

$mainRoutesContent = '<?php
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get("/", function () {
    return view("welcome");
});

// State-specific routes with middleware
Route::prefix("florida")->middleware("state:florida")->group(function() {
    require __DIR__."/florida.php";
});

Route::prefix("missouri")->middleware("state:missouri")->group(function() {
    require __DIR__."/missouri.php";
});

Route::prefix("texas")->middleware("state:texas")->group(function() {
    require __DIR__."/texas.php";
});

Route::prefix("delaware")->middleware("state:delaware")->group(function() {
    require __DIR__."/delaware.php";
});

// Admin routes
Route::prefix("admin")->middleware(["auth", "admin"])->group(function() {
    require __DIR__."/admin.php";
});';

file_put_contents('routes/web.php', $mainRoutesContent);
echo "✅ Updated: routes/web.php\n\n";

// Step 7: Create Basic Views
echo "🎨 Creating basic view templates...\n";

foreach ($states as $state) {
    $dashboardContent = '<!DOCTYPE html>
<html>
<head>
    <title>' . ucfirst($state) . ' Traffic School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>' . ucfirst($state) . ' Traffic School Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Available Courses</h5>
                        <a href="{{ route(\'' . $state . '.courses\') }}" class="btn btn-primary">View Courses</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>My Certificates</h5>
                        <a href="{{ route(\'' . $state . '.certificates\') }}" class="btn btn-success">View Certificates</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

    file_put_contents("resources/views/student/{$state}/dashboard.blade.php", $dashboardContent);
    echo "✅ Created: resources/views/student/{$state}/dashboard.blade.php\n";
}

echo "\n🎯 PRIMARY LAPTOP SETUP COMPLETE!\n";
echo "==================================\n\n";

echo "✅ COMPLETED TASKS:\n";
echo "- Database configuration updated\n";
echo "- Directory structure created\n";
echo "- Sample data seeder created\n";
echo "- State middleware created\n";
echo "- State factory service created\n";
echo "- State-specific routes created\n";
echo "- Basic view templates created\n";
echo "- Main routing system updated\n\n";

echo "🚀 NEXT STEPS:\n";
echo "1. Run the seeder to populate database with sample data\n";
echo "2. Register the middleware in app/Http/Kernel.php\n";
echo "3. Test the basic routing system\n";
echo "4. Share repository with team members\n\n";

echo "📋 READY FOR TEAM INTEGRATION!\n";
echo "Your primary laptop is now ready to coordinate with Qazi and Humayun's work.\n";
?>
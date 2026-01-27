<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

Route::get('/debug/users', function () {
    $output = [];
    
    // Check roles
    try {
        $rolesCount = Role::count();
        $output[] = "Roles in database: $rolesCount";
        
        if ($rolesCount > 0) {
            $output[] = "Available roles:";
            Role::all()->each(function($role) use (&$output) {
                $output[] = "  - ID: {$role->id}, Name: {$role->name}, Slug: {$role->slug}";
            });
        }
    } catch (Exception $e) {
        $output[] = "Error checking roles: " . $e->getMessage();
    }
    
    $output[] = "";
    
    // Check users
    try {
        $usersCount = User::count();
        $output[] = "Users in database: $usersCount";
        
        if ($usersCount > 0) {
            $output[] = "Recent users (last 10):";
            User::with('role')->latest()->take(10)->get()->each(function($user) use (&$output) {
                $roleName = $user->role ? $user->role->name : 'No Role';
                $output[] = "  - ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Email: {$user->email}, Role ID: {$user->role_id}, Role: {$roleName}, Created: {$user->created_at}";
            });
        }
    } catch (Exception $e) {
        $output[] = "Error checking users: " . $e->getMessage();
    }
    
    $output[] = "";
    
    // Check users without roles
    try {
        $usersWithoutRoles = User::whereNull('role_id')->orWhere('role_id', 0)->count();
        $output[] = "Users without proper role_id: $usersWithoutRoles";
        
        if ($usersWithoutRoles > 0) {
            $output[] = "Users without roles:";
            User::whereNull('role_id')->orWhere('role_id', 0)->get()->each(function($user) use (&$output) {
                $output[] = "  - ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Email: {$user->email}, Role ID: {$user->role_id}";
            });
        }
    } catch (Exception $e) {
        $output[] = "Error checking users without roles: " . $e->getMessage();
    }
    
    return response('<pre>' . implode("\n", $output) . '</pre>');
});

Route::get('/test/admin-users-api', function () {
    $output = [];
    $output[] = "🧪 Testing Admin Users API...";
    
    try {
        // Test the API endpoint that the admin panel uses
        $users = \App\Http\Controllers\UserController::class;
        $controller = new $users();
        
        $request = new \Illuminate\Http\Request();
        $request->merge(['per_page' => 100]);
        
        $response = $controller->indexWeb($request);
        $data = json_decode($response->getContent(), true);
        
        $output[] = "✅ API Response:";
        $output[] = "  - Total users: " . ($data['total'] ?? 'N/A');
        $output[] = "  - Users returned: " . count($data['data'] ?? []);
        $output[] = "  - Current page: " . ($data['current_page'] ?? 'N/A');
        $output[] = "  - Last page: " . ($data['last_page'] ?? 'N/A');
        $output[] = "  - Per page: " . ($data['per_page'] ?? 'N/A');
        
        if (isset($data['data']) && count($data['data']) > 0) {
            $output[] = "";
            $output[] = "Sample users from API:";
            foreach (array_slice($data['data'], 0, 5) as $user) {
                $roleName = $user['role']['name'] ?? 'No Role';
                $output[] = "  - {$user['first_name']} {$user['last_name']} ({$user['email']}) - {$roleName}";
            }
        }
        
    } catch (Exception $e) {
        $output[] = "❌ Error testing API: " . $e->getMessage();
    }
    
    return response('<pre>' . implode("\n", $output) . '</pre>');
});

Route::get('/test/admin-enrollments-api', function () {
    $output = [];
    $output[] = "🧪 Testing Admin Enrollments API...";
    
    try {
        // Test the API endpoint that the admin panel uses
        $controller = new \App\Http\Controllers\EnrollmentController();
        
        $request = new \Illuminate\Http\Request();
        $request->merge(['per_page' => 100]);
        
        $response = $controller->indexWeb($request);
        $data = json_decode($response->getContent(), true);
        
        $output[] = "✅ API Response:";
        $output[] = "  - Total enrollments: " . ($data['total'] ?? 'N/A');
        $output[] = "  - Enrollments returned: " . count($data['data'] ?? []);
        $output[] = "  - Current page: " . ($data['current_page'] ?? 'N/A');
        $output[] = "  - Last page: " . ($data['last_page'] ?? 'N/A');
        $output[] = "  - Per page: " . ($data['per_page'] ?? 'N/A');
        
        if (isset($data['data']) && count($data['data']) > 0) {
            $output[] = "";
            $output[] = "Sample enrollments from API:";
            foreach (array_slice($data['data'], 0, 5) as $enrollment) {
                $studentName = ($enrollment['user']['first_name'] ?? '') . ' ' . ($enrollment['user']['last_name'] ?? '');
                $courseName = $enrollment['course']['title'] ?? 'N/A';
                $status = $enrollment['status'] ?? 'N/A';
                $paymentStatus = $enrollment['payment_status'] ?? 'N/A';
                $output[] = "  - ID: {$enrollment['id']}, Student: {$studentName}, Course: {$courseName}, Status: {$status}, Payment: {$paymentStatus}";
            }
        }
        
        // Also check total count in database
        $totalInDb = \App\Models\UserCourseEnrollment::count();
        $output[] = "";
        $output[] = "📊 Database Stats:";
        $output[] = "  - Total enrollments in DB: {$totalInDb}";
        
    } catch (Exception $e) {
        $output[] = "❌ Error testing API: " . $e->getMessage();
        $output[] = "Stack trace: " . $e->getTraceAsString();
    }
    
    return response('<pre>' . implode("\n", $output) . '</pre>');
});

Route::get('/test/dashboard-stats-api', function () {
    $output = [];
    $output[] = "🧪 Testing Dashboard Stats API...";
    
    try {
        // Test the dashboard stats endpoint
        $controller = new \App\Http\Controllers\DashboardController();
        
        $response = $controller->getStatsWeb();
        $data = json_decode($response->getContent(), true);
        
        $output[] = "✅ Dashboard Stats API Response:";
        if (isset($data['stats'])) {
            $stats = $data['stats'];
            $output[] = "  - Total Students: " . ($stats['total_students'] ?? 'N/A');
            $output[] = "  - Total Courses: " . ($stats['total_courses'] ?? 'N/A');
            $output[] = "  - Total Enrollments: " . ($stats['total_enrollments'] ?? 'N/A');
            $output[] = "  - Completed Courses: " . ($stats['completed_courses'] ?? 'N/A');
            $output[] = "  - Total Revenue: $" . ($stats['total_revenue'] ?? 'N/A');
            $output[] = "  - Certificates Issued: " . ($stats['certificates_issued'] ?? 'N/A');
            
            // Calculate completion rate
            $totalEnrollments = $stats['total_enrollments'] ?? 0;
            $completedCourses = $stats['completed_courses'] ?? 0;
            $completionRate = $totalEnrollments > 0 ? round(($completedCourses / $totalEnrollments) * 100, 2) : 0;
            $output[] = "  - Calculated Completion Rate: {$completionRate}%";
            
            // Show debug info if available
            if (isset($stats['debug_completion_methods'])) {
                $output[] = "";
                $output[] = "🔍 Debug Completion Methods:";
                foreach ($stats['debug_completion_methods'] as $method => $count) {
                    $output[] = "  - {$method}: {$count}";
                }
            }
        } else {
            $output[] = "  ❌ No stats data in response";
        }
        
        // Show full response for debugging
        $output[] = "";
        $output[] = "📄 Full API Response:";
        $output[] = json_encode($data, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        $output[] = "❌ Error testing Dashboard Stats API: " . $e->getMessage();
        $output[] = "Stack trace: " . $e->getTraceAsString();
    }
    
    return response('<pre>' . implode("\n", $output) . '</pre>');
});

Route::get('/debug/deep-enrollment-data', function () {
    $output = [];
    $output[] = "🔍 DEEP DEBUG: User Registration & Enrollment Data Flow";
    $output[] = "=" . str_repeat("=", 60);
    
    try {
        // Get the most recent user
        $recentUser = \App\Models\User::latest()->first();
        
        if (!$recentUser) {
            $output[] = "❌ No users found in database";
            return response('<pre>' . implode("\n", $output) . '</pre>');
        }
        
        $output[] = "👤 RECENT USER DATA (ID: {$recentUser->id})";
        $output[] = "-" . str_repeat("-", 40);
        $output[] = "Name: {$recentUser->first_name} {$recentUser->last_name}";
        $output[] = "Email: {$recentUser->email}";
        $output[] = "Gender: " . ($recentUser->gender ?? 'NULL');
        $output[] = "Birth: {$recentUser->birth_month}/{$recentUser->birth_day}/{$recentUser->birth_year}";
        $output[] = "Address: " . ($recentUser->mailing_address ?? 'NULL');
        $output[] = "City: " . ($recentUser->city ?? 'NULL');
        $output[] = "State: " . ($recentUser->state ?? 'NULL');
        $output[] = "ZIP: " . ($recentUser->zip ?? 'NULL');
        $output[] = "Phone Parts: {$recentUser->phone_1}-{$recentUser->phone_2}-{$recentUser->phone_3}";
        $output[] = "Phone Full: " . ($recentUser->phone ?? 'NULL');
        $output[] = "Driver License: " . ($recentUser->driver_license ?? 'NULL');
        $output[] = "License State: " . ($recentUser->license_state ?? 'NULL');
        $output[] = "License Class: " . ($recentUser->license_class ?? 'NULL');
        $output[] = "Citation Number: " . ($recentUser->citation_number ?? 'NULL');
        $output[] = "Court Selected: " . ($recentUser->court_selected ?? 'NULL');
        $output[] = "Security Q8: " . ($recentUser->security_q8 ?? 'NULL');
        $output[] = "Security Q9: " . ($recentUser->security_q9 ?? 'NULL');
        $output[] = "Security Q10: " . ($recentUser->security_q10 ?? 'NULL');
        
        // Check enrollments for this user
        $enrollments = \App\Models\UserCourseEnrollment::where('user_id', $recentUser->id)->get();
        
        $output[] = "";
        $output[] = "📚 ENROLLMENTS FOR THIS USER (" . $enrollments->count() . " found)";
        $output[] = "-" . str_repeat("-", 40);
        
        if ($enrollments->count() === 0) {
            $output[] = "❌ No enrollments found for this user";
        } else {
            foreach ($enrollments as $enrollment) {
                $output[] = "Enrollment ID: {$enrollment->id}";
                $output[] = "Course ID: {$enrollment->course_id}";
                $output[] = "Course Table: " . ($enrollment->course_table ?? 'NULL');
                $output[] = "Status: " . ($enrollment->status ?? 'NULL');
                $output[] = "Payment Status: " . ($enrollment->payment_status ?? 'NULL');
                $output[] = "Citation Number: " . ($enrollment->citation_number ?? 'NULL');
                $output[] = "Case Number: " . ($enrollment->case_number ?? 'NULL');
                $output[] = "Court State: " . ($enrollment->court_state ?? 'NULL');
                $output[] = "Court County: " . ($enrollment->court_county ?? 'NULL');
                $output[] = "Court Selected: " . ($enrollment->court_selected ?? 'NULL');
                $output[] = "Court Date: " . ($enrollment->court_date ?? 'NULL');
                $output[] = "Enrolled At: " . ($enrollment->enrolled_at ?? 'NULL');
                $output[] = "Started At: " . ($enrollment->started_at ?? 'NULL');
                $output[] = "Completed At: " . ($enrollment->completed_at ?? 'NULL');
                $output[] = "Progress: " . ($enrollment->progress_percentage ?? 'NULL') . "%";
                $output[] = "---";
            }
        }
        
        // Check what fields are actually in the users table
        $output[] = "";
        $output[] = "🗃️ USERS TABLE STRUCTURE";
        $output[] = "-" . str_repeat("-", 40);
        $userColumns = \DB::select("DESCRIBE users");
        foreach ($userColumns as $column) {
            $output[] = "- {$column->Field} ({$column->Type}) " . ($column->Null === 'YES' ? 'NULL' : 'NOT NULL');
        }
        
        // Check what fields are actually in the user_course_enrollments table
        $output[] = "";
        $output[] = "🗃️ USER_COURSE_ENROLLMENTS TABLE STRUCTURE";
        $output[] = "-" . str_repeat("-", 40);
        $enrollmentColumns = \DB::select("DESCRIBE user_course_enrollments");
        foreach ($enrollmentColumns as $column) {
            $output[] = "- {$column->Field} ({$column->Type}) " . ($column->Null === 'YES' ? 'NULL' : 'NOT NULL');
        }
        
        // Test the enrollment detail route
        if ($enrollments->count() > 0) {
            $testEnrollment = $enrollments->first();
            $output[] = "";
            $output[] = "🧪 TESTING ENROLLMENT DETAIL ROUTE";
            $output[] = "-" . str_repeat("-", 40);
            
            try {
                $enrollment = \App\Models\UserCourseEnrollment::with(['user', 'floridaCourse', 'progress.chapter'])
                    ->findOrFail($testEnrollment->id);
                
                $output[] = "✅ Enrollment loaded successfully";
                $output[] = "User loaded: " . ($enrollment->user ? 'YES' : 'NO');
                $output[] = "Florida Course loaded: " . ($enrollment->floridaCourse ? 'YES' : 'NO');
                $output[] = "Progress loaded: " . $enrollment->progress->count() . " records";
                
                if ($enrollment->user) {
                    $output[] = "User data available:";
                    $output[] = "  - Name: {$enrollment->user->first_name} {$enrollment->user->last_name}";
                    $output[] = "  - Gender: " . ($enrollment->user->gender ?? 'NULL');
                    $output[] = "  - Citation: " . ($enrollment->user->citation_number ?? 'NULL');
                    $output[] = "  - Court: " . ($enrollment->user->court_selected ?? 'NULL');
                }
                
            } catch (\Exception $e) {
                $output[] = "❌ Error loading enrollment: " . $e->getMessage();
            }
        }
        
        // Check registration controller to see what fields are being saved
        $output[] = "";
        $output[] = "📝 REGISTRATION CONTROLLER ANALYSIS";
        $output[] = "-" . str_repeat("-", 40);
        
        // Check if there are any recent users with complete data
        $usersWithData = \App\Models\User::whereNotNull('citation_number')
            ->orWhereNotNull('court_selected')
            ->orWhereNotNull('gender')
            ->take(5)
            ->get(['id', 'first_name', 'last_name', 'citation_number', 'court_selected', 'gender', 'created_at']);
            
        $output[] = "Users with citation/court data (" . $usersWithData->count() . " found):";
        foreach ($usersWithData as $user) {
            $output[] = "  - ID: {$user->id}, Name: {$user->first_name} {$user->last_name}";
            $output[] = "    Citation: " . ($user->citation_number ?? 'NULL');
            $output[] = "    Court: " . ($user->court_selected ?? 'NULL');
            $output[] = "    Gender: " . ($user->gender ?? 'NULL');
            $output[] = "    Created: {$user->created_at}";
        }
        
    } catch (Exception $e) {
        $output[] = "❌ Error in deep debug: " . $e->getMessage();
        $output[] = "Stack trace: " . $e->getTraceAsString();
    }
    
    return response('<pre>' . implode("\n", $output) . '</pre>');
});
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StateCourseMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Starting state course migration...');

        try {
            // Migrate existing courses to state-specific tables
            $this->migrateCoursesToStateTables();
            
            // Update chapter references
            $this->updateChapterReferences();
            
            // Update enrollment references
            $this->updateEnrollmentReferences();
            
            Log::info('State course migration completed successfully');
        } catch (\Exception $e) {
            Log::error('State course migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function migrateCoursesToStateTables()
    {
        // Migrate Missouri courses
        $missouriCourses = DB::table('courses')
            ->whereIn('state', ['Missouri', 'MO'])
            ->orWhere('state_code', 'MO')
            ->get();

        foreach ($missouriCourses as $course) {
            DB::table('missouri_courses')->updateOrInsert(
                ['course_id' => $course->id],
                [
                    'missouri_course_code' => 'MO-' . $course->id,
                    'course_type' => 'defensive_driving',
                    'required_hours' => 8.0,
                    'max_completion_days' => 90,
                    'is_active' => $course->is_active ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Migrate Texas courses
        $texasCourses = DB::table('courses')
            ->whereIn('state', ['Texas', 'TX'])
            ->orWhere('state_code', 'TX')
            ->get();

        foreach ($texasCourses as $course) {
            DB::table('texas_courses')->updateOrInsert(
                ['course_id' => $course->id],
                [
                    'texas_course_code' => 'TX-' . $course->id,
                    'course_type' => 'defensive_driving',
                    'defensive_driving_hours' => 6,
                    'required_hours' => 6.0,
                    'max_completion_days' => 90,
                    'is_active' => $course->is_active ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Migrate Delaware courses
        $delawareCourses = DB::table('courses')
            ->whereIn('state', ['Delaware', 'DE'])
            ->orWhere('state_code', 'DE')
            ->get();

        foreach ($delawareCourses as $course) {
            DB::table('delaware_courses')->updateOrInsert(
                ['course_id' => $course->id],
                [
                    'delaware_course_code' => 'DE-' . $course->id,
                    'course_type' => 'defensive_driving',
                    'required_hours' => 8.0,
                    'max_completion_days' => 90,
                    'quiz_rotation_enabled' => true,
                    'is_active' => $course->is_active ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Migrate Nevada courses
        $nevadaCourses = DB::table('courses')
            ->whereIn('state', ['Nevada', 'NV'])
            ->orWhere('state_code', 'NV')
            ->get();

        foreach ($nevadaCourses as $course) {
            DB::table('nevada_courses')->updateOrInsert(
                ['course_id' => $course->id],
                [
                    'nevada_course_code' => 'NV-' . $course->id,
                    'course_type' => 'defensive_driving',
                    'required_hours' => 4.0,
                    'max_completion_days' => 90,
                    'is_active' => $course->is_active ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Log::info('Course migration to state tables completed');
    }

    private function updateChapterReferences()
    {
        // Update chapters to reference correct course_table
        DB::statement("
            UPDATE chapters c
            JOIN courses co ON c.course_id = co.id
            SET c.course_table = CASE 
                WHEN co.state IN ('Missouri', 'MO') OR co.state_code = 'MO' THEN 'missouri_courses'
                WHEN co.state IN ('Texas', 'TX') OR co.state_code = 'TX' THEN 'texas_courses'
                WHEN co.state IN ('Delaware', 'DE') OR co.state_code = 'DE' THEN 'delaware_courses'
                WHEN co.state IN ('Nevada', 'NV') OR co.state_code = 'NV' THEN 'nevada_courses'
                ELSE 'florida_courses'
            END
            WHERE c.course_table = 'florida_courses' OR c.course_table IS NULL
        ");

        Log::info('Chapter references updated');
    }

    private function updateEnrollmentReferences()
    {
        // Update enrollments to reference correct course_table based on user state
        DB::statement("
            UPDATE user_course_enrollments e
            JOIN users u ON e.user_id = u.id
            SET e.course_table = CASE 
                WHEN u.state_code IN ('Missouri', 'MO') THEN 'missouri_courses'
                WHEN u.state_code IN ('Texas', 'TX') THEN 'texas_courses'
                WHEN u.state_code IN ('Delaware', 'DE') THEN 'delaware_courses'
                WHEN u.state_code IN ('Nevada', 'NV') THEN 'nevada_courses'
                ELSE 'florida_courses'
            END
            WHERE e.course_table = 'florida_courses' OR e.course_table IS NULL
        ");

        Log::info('Enrollment references updated');
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class FindCourse extends Command
{
    protected $signature = 'courses:find {search : Search term for course title}';
    protected $description = 'Find a course by searching its title';

    public function handle()
    {
        $search = $this->argument('search');
        
        // Search in both courses and florida_courses tables
        $regularCourses = Course::where('title', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->get(['id', 'title', 'state_code', 'description']);
        
        $floridaCourses = DB::table('florida_courses')
            ->where('title', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->get(['id', 'title', 'state_code', 'description']);
        
        $allCourses = $regularCourses->merge($floridaCourses->map(function($course) {
            $course->table = 'florida_courses';
            return $course;
        }));
        
        if ($allCourses->isEmpty()) {
            $this->warn("No courses found matching: {$search}");
            $this->info("Try a different search term or use: php artisan courses:list");
            return 1;
        }

        $this->info("Found " . $allCourses->count() . " course(s) matching '{$search}':");
        $this->newLine();
        
        foreach ($allCourses as $course) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("ID: " . $course->id);
            $this->line("Title: " . $course->title);
            $this->line("State: " . ($course->state_code ?? 'N/A'));
            if (isset($course->table)) {
                $this->line("Table: " . $course->table);
            }
            if ($course->description) {
                $this->line("Description: " . substr($course->description, 0, 100) . "...");
            }
            $this->newLine();
        }
        
        $this->info("To import chapters to this course, use:");
        $this->comment("php artisan chapters:import {$allCourses->first()->id}");

        return 0;
    }
}

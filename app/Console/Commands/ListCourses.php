<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;

class ListCourses extends Command
{
    protected $signature = 'courses:list';
    protected $description = 'List all courses with their IDs';

    public function handle()
    {
        $courses = Course::orderBy('id')->get(['id', 'title', 'state_code']);
        
        if ($courses->isEmpty()) {
            $this->warn('No courses found in the database.');
            return 1;
        }

        $this->info('Available Courses:');
        $this->newLine();
        
        $tableData = $courses->map(function($course) {
            return [
                'ID' => $course->id,
                'Title' => $course->title,
                'State' => $course->state_code ?? 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Title', 'State'],
            $tableData
        );

        $this->newLine();
        $this->info('To import chapters, use: php artisan chapters:import {ID}');
        $this->info('Example: php artisan chapters:import 5');

        return 0;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissouriCourseContentSeeder extends Seeder
{
    public function run()
    {
        // Delete old Missouri courses and create fresh one
        DB::table('courses')->where('state', 'Missouri')->delete();

        // Create Missouri Ticket Dismissal Course
        $course = DB::table('courses')->insertGetId([
            'title' => 'Missouri Driving/Ticket Dismissal - 8 Hour Driver Improvement Course',
            'description' => 'State-approved by the Missouri Safety Center. Complete this 8-hour course to reduce points on your driving record and meet court requirements. 100% online with unlimited retakes.',
            'state' => 'Missouri',
            'duration' => 480,
            'price' => 24.94,
            'passing_score' => 80,
            'is_active' => true,
            'course_type' => 'BDI',
            'delivery_type' => 'Internet',
            'certificate_type' => 'form_4444',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Missouri Insurance Discount Course
        $insuranceCourse = DB::table('courses')->insertGetId([
            'title' => 'Missouri Insurance Discount - Defensive Driving Course',
            'description' => 'State-approved defensive driving course for insurance discounts. 100% online with unlimited retakes.',
            'state' => 'Missouri',
            'duration' => 480,
            'price' => 24.95,
            'passing_score' => 80,
            'is_active' => true,
            'course_type' => 'BDI',
            'delivery_type' => 'Internet',
            'certificate_type' => 'form_4444',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create 11 Chapters
        $chapters = [
            [
                'title' => 'Chapter 1: Missouri Traffic Laws & City Driving',
                'description' => 'Learn about Missouri traffic laws, pedestrian rights, school zones, emergency vehicles, and safe city driving practices.',
                'content' => 'This chapter covers essential Missouri traffic laws including two-way left turn lanes, pedestrian duties, school crossing guards, Move Over Law, and sharing the road with motorcycles.',
                'order' => 1,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 2: Traffic Signs and Signals',
                'description' => 'Understanding traffic signs, road markings, railroad crossings, and parking regulations in Missouri.',
                'content' => 'Learn to recognize and obey traffic signs including warning signs, regulatory signs, railroad crossings, school bus signals, and pavement markings.',
                'order' => 2,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 3: Intersections and Right-of-Way',
                'description' => 'Master intersection navigation, U-turns, traffic signals, and right-of-way rules.',
                'content' => 'Covers intersection safety, making turns, four-way stops, roundabouts, traffic light rules, and right-of-way procedures.',
                'order' => 3,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 4: Licensing, Insurance & Responsibilities',
                'description' => 'Missouri licensing requirements, insurance laws, accident reporting, and driver responsibilities.',
                'content' => 'Learn about Class F licenses, intermediate licenses, insurance requirements, accident reporting, and Missouri point system.',
                'order' => 4,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 5: Highway and Interstate Driving',
                'description' => 'Safe highway driving techniques, passing, merging, and avoiding highway hypnosis.',
                'content' => 'Covers freeway driving, highway hypnosis, blind spots, lane changes, space cushion, passing safely, and exiting highways.',
                'order' => 5,
                'duration' => 45,
            ],
            [
                'title' => 'Chapter 6: Lane Usage and Road Positioning',
                'description' => 'Proper lane usage, bike lanes, reversible lanes, and road positioning rules.',
                'content' => 'Learn about lane selection, turn-outs, pavement markings, bike lane safety, and when you can cross solid lines.',
                'order' => 6,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 7: Speed Limits and Backing Safety',
                'description' => 'Missouri speed limits, minimum speed laws, and safe backing procedures.',
                'content' => 'Covers speed limits in different zones, school zones, reckless driving penalties, backing safety, and stopping distances.',
                'order' => 7,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 8: Vehicle Safety and Weather Conditions',
                'description' => 'Vehicle equipment requirements, weather driving, and safety restraint laws.',
                'content' => 'Learn about required vehicle equipment, driving in fog and water, traction control, child safety seats, and Missouri tinting laws.',
                'order' => 8,
                'duration' => 45,
            ],
            [
                'title' => 'Chapter 9: DUI Laws and Substance Abuse',
                'description' => 'Missouri DUI laws, BAC limits, penalties, and the dangers of impaired driving.',
                'content' => 'Covers BAC levels, DUI penalties, designated drivers, open container laws, and prescription drug DUI.',
                'order' => 9,
                'duration' => 45,
            ],
            [
                'title' => 'Chapter 10: Defensive Driving and Road Rage',
                'description' => 'Defensive driving techniques, avoiding road rage, and safe driving practices.',
                'content' => 'Learn about aggressive driving, road rage prevention, parking techniques, distracted driving, and defensive driving strategies.',
                'order' => 10,
                'duration' => 40,
            ],
            [
                'title' => 'Chapter 11: Final Exam',
                'description' => 'Comprehensive final exam covering all course material. 50 questions, 80% passing score required.',
                'content' => 'Final examination with 50 multiple-choice questions covering all chapters. You must score 80% or higher to pass. Unlimited retakes available.',
                'order' => 11,
                'duration' => 45,
                'is_quiz_only' => true,
            ],
        ];

        foreach ($chapters as $chapterData) {
            DB::table('chapters')->insert([
                'course_id' => $course,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'] ?? '',
                'order_index' => $chapterData['order'],
                'duration' => $chapterData['duration'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Missouri course and 11 chapters created successfully!');
        $this->command->info('Course ID: '.$course);
    }
}

<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaBDICourseSeeder extends Seeder
{
    public function run()
    {
        // Florida BDI Course
        $courseId = DB::table('florida_courses')->insertGetId([
            'course_type' => 'BDI',
            'title' => 'Florida Driving/Ticket Dismissal - 4-Hour Basic Driver Improvement Course (BDI)',
            'description' => 'Florida Basic Driver Improvement Course for ticket dismissal',
            'state_code' => 'FL',
            'min_pass_score' => 80,
            'total_duration' => 240,
            'price' => 19.95,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $chapters = $this->getChapters();

        foreach ($chapters as $index => $chapter) {
            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapter['title'],
                'content' => '',
                'order_index' => $index + 1,
                'duration' => $chapter['duration'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            foreach ($chapter['questions'] as $qIndex => $q) {
                // Get the correct answer text from options array using the index
                $correctAnswerText = $q['o'][$q['c']];

                DB::table('questions')->insert([
                    'chapter_id' => $chapterId,
                    'course_id' => $courseId,
                    'question_text' => $q['q'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($q['o']),
                    'correct_answer' => $correctAnswerText,
                    'order_index' => $qIndex + 1,
                    'points' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('Florida BDI Course created!');

        // Missouri Course
        $missouriCourseId = DB::table('florida_courses')->insertGetId([
            'course_type' => 'Driver Improvement',
            'title' => 'Missouri Driver Improvement Course',
            'description' => 'Missouri State-Approved Driver Improvement Course',
            'state_code' => 'MO',
            'min_pass_score' => 80,
            'total_duration' => 240,
            'price' => 29.99,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Missouri Course created!');
    }

    private function getChapters()
    {
        return [
            ['title' => 'Chapter 1: Traffic Safety Problem', 'duration' => 5, 'questions' => [
                ['q' => 'Which of the following is an example of a kind of change traffic laws must respond to?', 'o' => ['Changes car manufacturing methods', 'Changes in climate', 'Changes in taxes', 'Changes in technology', 'None of the above'], 'c' => 3],
                ['q' => 'What is an example of a driving technique one might need to learn to safely use the roads?', 'o' => ['Scanning', 'Avoiding no-zones', '3-second system', 'Signaling', 'All of the above'], 'c' => 4],
                ['q' => 'What is the primary reason traffic laws existing?', 'o' => ['Collecting citation fees', 'Punishing motorists', 'Maintaining social order', 'Preventing cost to cities', 'Ensuring driver safety'], 'c' => 4],
                ['q' => 'Which of the following is an example of a technological change that might affect how motor vehicles are driven?', 'o' => ['Faster processors', 'Rear-seat video displays', 'New drawbridge technology', 'Faster internet', 'None of the above'], 'c' => 4],
                ['q' => 'Traffic laws help to establish a sense of ________ so that all drivers can expect predictable driving behavior from each other and avoid collisions.', 'o' => ['competition', 'pleasantness', 'suspicion', 'common understanding', 'None of the above'], 'c' => 3],
            ]],
            ['title' => 'Chapter 2: Careless Driving', 'duration' => 37, 'questions' => [
                ['q' => 'Mixing various drugs with alcohol will usually ____ the effects of both.', 'o' => ['hinder', 'neutralize', 'magnify', 'stop', 'none of the above'], 'c' => 2],
                ['q' => 'Just one drink can impair your _____.', 'o' => ['judgment', 'response time', 'vision', 'coordination', 'all of the above'], 'c' => 4],
                ['q' => 'The same laws apply to _____ as drinking and driving.', 'o' => ['driving on a restricted license', 'falling asleep at the wheel', 'failing to yield', 'taking drugs and driving', 'all of the above'], 'c' => 3],
                ['q' => 'In general it is illegal for any driver under age 21 to _____.', 'o' => ['transport alcohol', 'consume alcohol', 'possess alcohol', 'drive with a BAC of 0.01 or higher', 'all of the above'], 'c' => 4],
                ['q' => 'Lane drifting, erratic behavior and speeding up and slowing down help identify _____.', 'o' => ['a person evading police', 'a drowsy driver', 'a drunk at a bar', 'a drunk on the road', 'None of the above'], 'c' => 3],
            ]],
            ['title' => 'Chapter 3: Operator Responsibilities', 'duration' => 41, 'questions' => [
                ['q' => 'When you feel fatigue set in as you are driving, it is best NOT to:', 'o' => ['Grab a cup of coffee', 'Continue driving', 'Pull over in a safe area and take a nap', 'Switch drivers', 'None of the above'], 'c' => 1],
                ['q' => 'When your car tires ride up on the surface of the water like skis, this is referred to as______.', 'o' => ['Hydroplaning', 'Skipping', 'Vortex', 'Motion', 'Slipping'], 'c' => 0],
                ['q' => 'When points on your driving record add up, it means _____.', 'o' => ['a state of grace', 'you may face penalties', 'you win', 'a visit to the local jail', 'you earn a bonus'], 'c' => 1],
                ['q' => 'A Class ____license allows operation of a standard passenger vehicle.', 'o' => ['A', 'B', 'C', 'D', 'E'], 'c' => 4],
                ['q' => 'A Florida Driver\'s License is usually valid for ____ years.', 'o' => ['6', '1', '3', '9', '4'], 'c' => 0],
            ]],
            ['title' => 'Chapter 4: Vulnerable Road Users', 'duration' => 8, 'questions' => [
                ['q' => 'When crossing a road as a pedestrian, you should:', 'o' => ['Cross at any point in the road', 'Cross at designated crosswalks', 'Hold up traffic as you walk across', 'Expect all traffic to always stop for you', 'None of the above'], 'c' => 1],
                ['q' => 'Blind pedestrians can usually be recognized by:', 'o' => ['Walking slowly', 'A white cane and seeing-eye dog', 'Dark glasses', 'Walking quickly', 'None of the above'], 'c' => 1],
                ['q' => 'A driver must stop for a pedestrian in the road:', 'o' => ['At all times', 'Only when they are crossing at a crosswalk', 'Only when they are crossing legally', 'Only when it is convenient', 'None of the above'], 'c' => 0],
                ['q' => 'The signal indicating it is safe to cross a street is:', 'o' => ['A red walking person', 'A red raised hand', 'A white raised hand', 'A white walking person', 'None of the above'], 'c' => 3],
                ['q' => '"Vulnerable Road users" includes:', 'o' => ['People who walk', 'People who use a wheelchair', 'People riding a bicycle', 'People who ride a skateboard', 'All the above'], 'c' => 4],
            ]],
            ['title' => 'Chapter 5: Driving Maneuvers', 'duration' => 17, 'questions' => [
                ['q' => 'The driver of the car being passed must not __________ until the pass is complete.', 'o' => ['Decrease speed', 'Increase speed', 'Move over', 'Lower the power', 'None of the above'], 'c' => 1],
                ['q' => 'At a speed of 55mph you need about _____ to pass.', 'o' => ['25 sec', '2 sec', '10 sec', '5 sec', '4 sec'], 'c' => 2],
                ['q' => 'It is best to _________ to help prevent skidding on slippery surfaces.', 'o' => ['Avoid fast turns', 'Slow down as you approach curves', 'Drive more slowly', 'Avoid slippery areas', 'All of the above'], 'c' => 4],
                ['q' => '_____ occurs when a driver tries to recover from over steering and they lose traction with the rear wheels.', 'o' => ['Acceleration Skids', 'Fishtailing', 'Locked Wheel Skids', 'Hydroplaning', 'None of the above'], 'c' => 1],
                ['q' => 'When the road is wet your tires can _____.', 'o' => ['still have good traction up to about 35 mph', 'hydroplane', 'lose all traction at higher speeds', 'lose traction faster if in poor condition', 'All of the above'], 'c' => 4],
            ]],
        ];
    }
}

<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaDefensiveDrivingCompleteAllSeeder extends Seeder
{
    public function run()
    {
        $course = DB::table('florida_courses')->where('course_type', 'BDI')->first();

        if (! $course) {
            $this->command->error('Florida Defensive Driving Course not found.');

            return;
        }

        $this->addFinalChapter($course->id);
        $this->updateFinalExam($course->id);
    }

    private function addFinalChapter($courseId)
    {
        $chapterId = DB::table('chapters')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Chapter 17: The Common Sense of Driving',
            'content' => 'This final chapter brings together all defensive driving principles with 13 essential tips for safe, courteous driving.

**Basic Tip #1 - Common Sense Means Common Courtesy**
Practice the Golden Rule: "Do unto others as you would have them do unto you." Being a safe driver requires knowing the law, driving skills, and common sense.

**Basic Tip #2 - Park Responsibly**
Proper parking prevents accidents even when your vehicle is unoccupied:
- Downhill: Point front wheels toward the curb
- Uphill: Point wheels away from curb, let vehicle roll back to "catch"
- Stay within 18 inches of the curb
- Never park in handicapped spaces, fire lanes, or within 15 feet of fire hydrants

**Basic Tip #3 - Stop Means STOP**
Stop signs are not suggestions. Come to a complete stop at the proper location. To yield means "to give up an advantage to another."

**Basic Tip #4 - Cell Phones and Driving Don\'t Mix**
Cell phone use while driving causes delayed reflexes similar to drunk drivers. Studies show cell phone users are twice as likely to rear-end another car.

**Basic Tip #5 - Identify Dangerous and Aggressive Driving**
Road rage statistics:
- 44% of road rage cases involve the car as a weapon
- 23% involve conventional weapons
- Aggressive driving contributes to 37% of fatal crashes

**Basic Tip #6 - Defensive Driving is a Lifestyle**
Five most common collision causes:
1. Unsafe speeds
2. Wrong side of road
3. Bad turns
4. Breaking right-of-way rules
5. Ignoring stop signs

**Basic Tip #7 - Right-of-Way Rules and Courtesy**
- Pedestrians (including those on skateboards, scooters, wheelchairs) have right-of-way at crosswalks
- At four-way stops, first to arrive goes first
- When turning left, yield to oncoming traffic

**Basic Tip #8 - Driver Condition**
- Don\'t drive when emotionally distressed, angry, or fatigued
- 31% of drivers have fallen asleep at the wheel
- 100,000 accidents yearly from drowsy driving
- 1,500 deaths from falling asleep while driving

**Basic Tip #9 - Adjust to Driving Environment**
- Day vs. night driving each have unique risks
- Weather requires speed adjustments: 5-10 mph slower on wet roads
- Road conditions: soft shoulders, drop-offs, worn pavement

**Basic Tip #10 - Learn Why Collisions Happen**
Collision causes: emotional reasons, physical impairment, vehicle problems, environmental factors, bad habits, other drivers, freeway issues.

**Basic Tip #11 - Plan to Avoid Collisions**
- Scan 10-15 seconds ahead
- Choose proper lanes
- Stay out of blind spots
- Follow speed limits and traffic flow

**Basic Tip #12 - Drive Defensively During Collisions**
Three collision avoidance options: Speed up, Stop, or Evade (usually best choice).

**Basic Tip #13 - Defensive Driving Philosophy**
Like Eastern philosophies:
1. Other drivers may not be fully alert (Maya)
2. Manage time and space efficiently (Feng Shui)
3. Courtesy comes back to you (Karma)
4. Cultivate good habits naturally (Zen)',

            'order_index' => 17,
            'duration' => 45,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $questions = [
            [
                'question' => 'Which choice below is NOT considered a pedestrian?',
                'options' => [
                    'A man in a wheelchair on the sidewalk',
                    'A girl on a skateboard in the road',
                    'A mom on a scooter in the parking lot',
                    'None of the above - all are pedestrians',
                ],
                'correct_answer' => 'None of the above - all are pedestrians',
                'explanation' => 'People on skateboards, scooters, wheelchairs, and other mobility devices are all considered pedestrians.',
            ],
            [
                'question' => 'What percentage of road rage cases involve the car being used as a weapon?',
                'options' => [
                    '23%',
                    '37%',
                    '44%',
                    '54%',
                ],
                'correct_answer' => '44%',
                'explanation' => 'In 44% of road rage incidents, the vehicle itself is used as a weapon, while 23% involve conventional weapons.',
            ],
            [
                'question' => 'When parking uphill, you should:',
                'options' => [
                    'Point front wheels toward the curb',
                    'Point front wheels away from the curb and let vehicle roll back',
                    'Keep wheels straight',
                    'Point wheels toward traffic',
                ],
                'correct_answer' => 'Point front wheels away from the curb and let vehicle roll back',
                'explanation' => 'When parking uphill, point wheels away from curb so if the vehicle rolls, it will be stopped by the curb.',
            ],
            [
                'question' => 'What percentage of drivers have fallen asleep at the wheel at least once?',
                'options' => [
                    '21%',
                    '31%',
                    '41%',
                    '51%',
                ],
                'correct_answer' => '31%',
                'explanation' => 'Studies show that 31% of all drivers have fallen asleep at the wheel at least once, highlighting the danger of drowsy driving.',
            ],
        ];

        foreach ($questions as $index => $questionData) {
            DB::table('questions')->insert([
                'chapter_id' => $chapterId,
                'course_id' => $courseId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'order_index' => $index + 1,
                'points' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    private function updateFinalExam($courseId)
    {
        // Update final exam with complete questions from the document
        $examChapter = DB::table('chapters')->where('course_id', $courseId)->where('title', 'Final Exam')->first();

        if ($examChapter) {
            // Delete existing final exam questions
            DB::table('questions')->where('chapter_id', $examChapter->id)->delete();

            $finalExamQuestions = [
                [
                    'question' => 'Drivers must obey signals from school crossing guards _____.',
                    'options' => ['during school hours', 'if they go to that school', 'at all times', 'if it isn\'t a school holiday'],
                    'correct_answer' => 'at all times',
                ],
                [
                    'question' => 'If you are traveling down a one way street, _____.',
                    'options' => ['watch for traffic going the wrong way', 'make sure you are going in the right direction', 'Both A and B are correct', 'keep your turn signal on'],
                    'correct_answer' => 'Both A and B are correct',
                ],
                [
                    'question' => 'The purpose of traffic signs are ____.',
                    'options' => ['to serve as traffic control', 'to communicate warnings', 'to express traffic regulations', 'all of the above'],
                    'correct_answer' => 'all of the above',
                ],
                [
                    'question' => 'You can make a right turn on red, provided _______.',
                    'options' => ['you have first stopped completely', 'there are no posted signs that prohibit it', 'you have checked for oncoming traffic', 'A, B, C are correct'],
                    'correct_answer' => 'A, B, C are correct',
                ],
                [
                    'question' => 'Cars that carry heavy loads, large vehicles, and trucks all need _____ distance to stop as regular cars.',
                    'options' => ['less', 'the same', 'more', 'diminishing'],
                    'correct_answer' => 'more',
                ],
                [
                    'question' => 'Many people will instinctively _____ an animal on the road, causing a hazard to other drivers.',
                    'options' => ['make friends with', 'catch', 'swerve around', 'stop'],
                    'correct_answer' => 'swerve around',
                ],
                [
                    'question' => 'If a person has had more than one drink an hour, ____ hour(s) of sobering up should be allowed for each extra drink.',
                    'options' => ['1', '2', '3', '½'],
                    'correct_answer' => '1',
                ],
                [
                    'question' => 'To yield means ____.',
                    'options' => ['to cease all action', 'to outmaneuver', 'to take possession of', 'to give up (an advantage, for example) to another'],
                    'correct_answer' => 'to give up (an advantage, for example) to another',
                ],
                [
                    'question' => 'Lane drifting, erratic behavior and speeding up and slowing down help identify _____.',
                    'options' => ['a person evading police', 'a drowsy driver', 'a drunk at a bar', 'a drunk on the road'],
                    'correct_answer' => 'a drunk on the road',
                ],
                [
                    'question' => '_____ can contribute to bad driving.',
                    'options' => ['Fatigue', 'Emotions', 'Cell phone use', 'All of the above'],
                    'correct_answer' => 'All of the above',
                ],
            ];

            foreach ($finalExamQuestions as $index => $questionData) {
                DB::table('questions')->insert([
                    'chapter_id' => $examChapter->id,
                    'course_id' => $courseId,
                    'question_text' => $questionData['question'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => '',
                    'order_index' => $index + 1,
                    'points' => 1,

                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('Final comprehensive chapter and updated exam created!');
    }
}

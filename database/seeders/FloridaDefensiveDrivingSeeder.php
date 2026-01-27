<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaDefensiveDrivingSeeder extends Seeder
{
    public function run()
    {
        // Delete existing Florida defensive driving florida_courses
        DB::table('florida_courses')->where('course_type', 'BDI')->where('title', 'LIKE', '%Defensive Driving%')->delete();

        // Create Florida Defensive Driving Course
        $courseId = DB::table('florida_courses')->insertGetId([
            'course_type' => 'Insurance Discount',
            'title' => 'Florida Insurance Discount - Defensive Driving Course',
            'description' => 'Complete this 6-hour course to reduce points, meet court requirements, or qualify for insurance discounts.',
            'state_code' => 'FL',
            'min_pass_score' => 80,
            'total_duration' => 360,
            'price' => 16.95,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->createChapters($courseId);
    }

    private function createChapters($courseId)
    {
        $chapters = $this->getChapterData();

        foreach ($chapters as $index => $chapterData) {
            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'],

                'order_index' => $index + 1,
                'duration' => $chapterData['duration'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Create questions for this chapter
            if (isset($chapterData['questions'])) {
                $this->createQuestions($chapterId, $courseId, $chapterData['questions']);
            }
        }

        // Create final exam
        $this->createFinalExam($courseId);
    }

    private function createQuestions($chapterId, $courseId, $questions)
    {
        foreach ($questions as $index => $questionData) {
            DB::table('questions')->insert([
                'chapter_id' => $chapterId,
                'course_id' => $courseId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'] ?? '',
                'order_index' => $index + 1,
                'points' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    private function getChapterData()
    {
        return [
            [
                'title' => 'Chapter 1: Course Introduction & Defensive Driving Basics',
                'description' => 'Introduction to defensive driving principles, course objectives, and Florida driving environment',
                'duration' => 30,
                'content' => 'This chapter introduces the fundamental concepts of defensive driving and explains how this course will help you become a safer, more responsible driver. You will learn about the course objectives including reducing traffic collision involvement, reducing traffic law violations, and understanding the responsibilities associated with operating a vehicle.

**What is Defensive Driving?**
Defensive driving is a comprehensive method of driving that has the intent of keeping the driver, and everyone around them, as safe and unhindered as possible. It combines techniques and attitudes that help you deal with almost any driving situation, including bad drivers, bad weather, and unexpected circumstances.

**Course Benefits:**
- Reduce risk of traffic collisions
- Increase familiarity with traffic laws
- Develop responsible driving habits
- Potential insurance discounts
- Point reduction on driving record

**Key Statistics:**
According to the World Health Organization (WHO), road traffic collisions cause over a million deaths and up to fifty million injuries each year worldwide. Most of these collisions are preventable and usually caused by driver or vehicular error.',
                'questions' => [
                    [
                        'question' => 'What is the primary purpose of defensive driving?',
                        'options' => [
                            'To drive faster than other vehicles',
                            'To keep the driver and everyone around them as safe as possible',
                            'To avoid getting traffic tickets',
                            'To save fuel while driving',
                        ],
                        'correct_answer' => 'To keep the driver and everyone around them as safe as possible',
                        'explanation' => 'Defensive driving is a comprehensive method focused on safety for all road users.',
                    ],
                    [
                        'question' => 'According to the WHO, approximately how many deaths are caused by road traffic collisions each year worldwide?',
                        'options' => [
                            'Over 500,000',
                            'Over 1 million',
                            'Over 2 million',
                            'Over 5 million',
                        ],
                        'correct_answer' => 'Over 1 million',
                        'explanation' => 'The World Health Organization reports that road traffic collisions cause over a million deaths annually worldwide.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 2: The Dangers of City Driving',
                'description' => 'Essential tips for safe city driving, managing urban traffic hazards, and sharing the road',
                'duration' => 35,
                'content' => 'City driving presents unique challenges with multiple types of road users, distractions, and frequent stops. This chapter provides 7 basic tips for safe city driving.

**City Driving Challenges:**
- Sharing roadway with pedestrians, cyclists, buses, taxis, and trucks
- Distractions from noise, advertisements, and people
- Frequent intersections and traffic signals
- Parking lot safety concerns
- Emergency vehicle encounters

**Basic Tip #1 - Slow Down:**
Reducing speed in the city gives you:
- More time to pick up details and understand their meaning
- More time to analyze and predict what could happen
- More reaction time to decide and act
- Gives other drivers more time to react safely

**Basic Tip #2 - Look Ahead:**
Scan 10-15 seconds ahead of your vehicle to see hazards early. Check blind spots and be aware of the whole scene around you.

**Basic Tip #3 - Cover vs. Riding the Brakes:**
Be prepared to brake in certain situations but don\'t rest your foot on the pedal, as this can desensitize following drivers to your brake lights.',
                'questions' => [
                    [
                        'question' => 'When driving in the city, how far ahead should you scan for hazards?',
                        'options' => [
                            '5-8 seconds',
                            '10-15 seconds',
                            '20-25 seconds',
                            '30 seconds',
                        ],
                        'correct_answer' => '10-15 seconds',
                        'explanation' => 'Scanning 10-15 seconds ahead allows you to see hazards early and make safe decisions.',
                    ],
                    [
                        'question' => 'Why should you avoid "riding the brakes" in city driving?',
                        'options' => [
                            'It wastes fuel',
                            'It wears out brake pads faster',
                            'Cars behind you may ignore your brake lights and be unprepared for emergencies',
                            'It makes steering more difficult',
                        ],
                        'correct_answer' => 'Cars behind you may ignore your brake lights and be unprepared for emergencies',
                        'explanation' => 'Constantly having brake lights on desensitizes following drivers, making them less likely to react when you actually need to brake.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 3: Following Distance and Space Management',
                'description' => 'Learn the 3-second rule, space cushion techniques, and safe following distances',
                'duration' => 30,
                'content' => 'Proper following distance is crucial for safe driving. This chapter covers the various "second rules" that help minimize collision risks.

**The Three-Second Rule:**
Establish a three-second gap between you and the vehicle you are following. When the vehicle ahead passes a stationary object, count "one-thousand-one, one-thousand-two, one-thousand-three." You should pass the same marker when you reach three.

**The Four-Second Rule:**
Apply the four-second rule when:
- Roads are wet or frosty
- You are towing a trailer
- Visibility is reduced
- Following large vehicles

**Space Cushion Management:**
- Keep space on all sides of your vehicle when possible
- Stay out of other drivers\' blind spots
- Don\'t crowd the center line or parked cars
- Leave room for emergency maneuvers

**Factors Affecting Stopping Distance:**
- Road surface friction
- Weather conditions
- Tire condition and tread depth
- Vehicle weight and braking system
- Speed and road gradient',
                'questions' => [
                    [
                        'question' => 'Under normal driving conditions, what is the minimum recommended following distance?',
                        'options' => [
                            '1 second',
                            '2 seconds',
                            '3 seconds',
                            '5 seconds',
                        ],
                        'correct_answer' => '3 seconds',
                        'explanation' => 'The three-second rule provides adequate time to react and stop safely under normal conditions.',
                    ],
                    [
                        'question' => 'When should you use the four-second rule instead of the three-second rule?',
                        'options' => [
                            'Only at night',
                            'When roads are wet, frosty, or when towing a trailer',
                            'Only on highways',
                            'When driving in the city',
                        ],
                        'correct_answer' => 'When roads are wet, frosty, or when towing a trailer',
                        'explanation' => 'The four-second rule provides extra safety margin when conditions require longer stopping distances.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 4: Pedestrians and Emergency Vehicles',
                'description' => 'Understanding pedestrian rights, emergency vehicle procedures, and sharing the road safely',
                'duration' => 35,
                'content' => 'Sharing the road with pedestrians and responding to emergency vehicles are critical safety skills covered in this chapter.

**Pedestrian Right-of-Way:**
- Pedestrians have right-of-way in marked and unmarked crosswalks
- Yield to pedestrians when they are on your half of the roadway or approaching closely
- Exercise due care to avoid colliding with any pedestrian
- Never block sidewalks, crosswalks, or intersections

**Emergency Vehicle Procedures:**
When approached by emergency vehicles with sirens and flashing lights:
- Immediately move as close as possible to the right-hand curb
- Remain clear of intersections
- Stay stopped until the emergency vehicle has passed
- Reduce speed and change lanes when approaching stationary emergency vehicles

**Special Situations:**
- School crossing guards must be obeyed at all times
- Construction zone flaggers have authority to direct traffic
- Move Over Law requires lane changes or speed reduction near emergency vehicles

**Parking Lot Safety:**
- 20% of automobile crash claims occur in parking lots
- Drive slowly and watch for pedestrians
- Be especially careful around shopping centers and schools
- Watch for "runaway" shopping carts and children',
                'questions' => [
                    [
                        'question' => 'When must you yield to pedestrians?',
                        'options' => [
                            'Only at marked crosswalks',
                            'Only when they have a walk signal',
                            'When they are on your half of the roadway or approaching closely from the opposite half',
                            'Only during daylight hours',
                        ],
                        'correct_answer' => 'When they are on your half of the roadway or approaching closely from the opposite half',
                        'explanation' => 'Florida law requires yielding to pedestrians when they are on your half of the roadway or approaching so closely as to be in danger.',
                    ],
                    [
                        'question' => 'What percentage of automobile crash claims occur in parking lots?',
                        'options' => [
                            '10%',
                            '15%',
                            '20%',
                            '25%',
                        ],
                        'correct_answer' => '20%',
                        'explanation' => 'The Insurance Institute for Highway Safety reports that parking lot accidents account for more than 20% of automobile crash claims.',
                    ],
                ],
            ],
        ];
    }

    private function createFinalExam($courseId)
    {
        // Create final exam chapter
        $examChapterId = DB::table('chapters')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Final Exam',
            'content' => 'This final exam tests your knowledge of defensive driving principles, traffic laws, and safe driving practices covered throughout the course. You must score 80% or higher to pass.',

            'order_index' => 99,
            'duration' => 30,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $finalExamQuestions = [
            [
                'question' => 'Drivers must obey signals from school crossing guards _____.',
                'options' => [
                    'during school hours',
                    'if they go to that school',
                    'at all times',
                    'if it isn\'t a school holiday',
                ],
                'correct_answer' => 'at all times',
                'explanation' => 'School crossing guards must be obeyed at all times when they are directing traffic.',
            ],
            [
                'question' => 'If you are traveling down a one way street, _____.',
                'options' => [
                    'watch for traffic going the wrong way',
                    'make sure you are going in the right direction',
                    'Both A and B are correct',
                    'keep your turn signal on',
                ],
                'correct_answer' => 'Both A and B are correct',
                'explanation' => 'On one-way streets, you should ensure you\'re going the correct direction and watch for confused drivers going the wrong way.',
            ],
            [
                'question' => 'The purpose of traffic signs are ____.',
                'options' => [
                    'to serve as traffic control',
                    'to communicate warnings',
                    'to express traffic regulations',
                    'all of the above',
                ],
                'correct_answer' => 'all of the above',
                'explanation' => 'Traffic signs serve multiple purposes including traffic control, warnings, and communicating regulations.',
            ],
            [
                'question' => 'When approaching an intersection, _____.',
                'options' => [
                    'be ready to stop or yield, even if there is no posted sign',
                    'always keep a lookout for pedestrians and bicyclists',
                    'be ready to stop if the light is yellow',
                    'All of the above',
                ],
                'correct_answer' => 'All of the above',
                'explanation' => 'All these actions are important safety measures when approaching intersections.',
            ],
            [
                'question' => 'Cars that carry heavy loads, large vehicles, and trucks all need _____ distance to stop as regular cars.',
                'options' => [
                    'less',
                    'the same',
                    'more',
                    'diminishing',
                ],
                'correct_answer' => 'more',
                'explanation' => 'Heavy vehicles require more distance to stop due to increased weight and momentum.',
            ],
            [
                'question' => 'You should always drive on the right side of the road except ____.',
                'options' => [
                    'when passing another vehicle',
                    'when making a left turn',
                    'when it\'s closed to traffic',
                    'All of the above',
                ],
                'correct_answer' => 'All of the above',
                'explanation' => 'These are all legitimate exceptions to driving on the right side of the road.',
            ],
            [
                'question' => 'Many people will instinctively _____ an animal on the road, causing a hazard to other drivers.',
                'options' => [
                    'make friends with',
                    'catch',
                    'swerve around',
                    'stop',
                ],
                'correct_answer' => 'swerve around',
                'explanation' => 'Drivers often swerve to avoid animals, which can create hazards for other vehicles.',
            ],
            [
                'question' => 'To yield means ____.',
                'options' => [
                    'to cease all action',
                    'to outmaneuver',
                    'to take possession of',
                    'to give up (an advantage, for example) to another',
                ],
                'correct_answer' => 'to give up (an advantage, for example) to another',
                'explanation' => 'Yielding means giving the right-of-way or advantage to another driver or pedestrian.',
            ],
            [
                'question' => 'Lane drifting, erratic behavior and speeding up and slowing down help identify _____.',
                'options' => [
                    'a person evading police',
                    'a drowsy driver',
                    'a drunk at a bar',
                    'a drunk on the road',
                ],
                'correct_answer' => 'a drunk on the road',
                'explanation' => 'These behaviors are classic signs of impaired driving due to alcohol or drugs.',
            ],
            [
                'question' => '_____ can contribute to bad driving.',
                'options' => [
                    'Fatigue',
                    'Emotions',
                    'Cell phone use',
                    'All of the above',
                ],
                'correct_answer' => 'All of the above',
                'explanation' => 'Fatigue, emotions, and cell phone use are all major contributors to unsafe driving behaviors.',
            ],
        ];

        foreach ($finalExamQuestions as $index => $questionData) {
            DB::table('questions')->insert([
                'chapter_id' => $examChapterId,
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
}

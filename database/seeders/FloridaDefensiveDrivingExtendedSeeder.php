<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaDefensiveDrivingExtendedSeeder extends Seeder
{
    public function run()
    {
        // Get the Florida DDC course
        $course = DB::table('florida_courses')->where('title', 'LIKE', '%Defensive Driving%')->first();

        if (! $course) {
            $this->command->error('Florida Defensive Driving Course not found. Run FloridaDefensiveDrivingSeeder first.');

            return;
        }

        $this->addExtendedChapters($course->id);
    }

    private function addExtendedChapters($courseId)
    {
        $extendedChapters = [
            [
                'title' => 'Chapter 5: Weather Conditions and Road Hazards',
                'description' => 'Driving safely in various weather conditions and handling road hazards',
                'duration' => 40,
                'content' => 'Weather conditions significantly impact driving safety. This chapter covers techniques for driving in rain, fog, snow, and other challenging conditions.

**Wet Weather Driving:**
- Use low beam headlights in rain to see and be seen
- Allow extra following distance (4-second rule minimum)
- Reduce speed by 5-10 mph on wet roads
- Watch for hydroplaning on thin sheets of water
- Roads are slickest during the first rainfall after a dry period

**Fog and Reduced Visibility:**
- Use low beam headlights, never high beams
- Reduce speed significantly
- Use fog lights if equipped
- Pull over if visibility becomes too poor

**Road Conditions to Watch For:**
- Soft shoulders (grass, dirt, or gravel edges)
- Drop-offs near construction or erosion areas
- Worn pavement with bumps and holes
- Seasonal hazards like flooding or debris

**Hydroplaning Prevention:**
- Maintain proper tire tread depth
- Reduce speed in wet conditions
- Avoid sudden steering or braking movements
- Drive in the tracks of vehicles ahead when safe',
                'questions' => [
                    [
                        'question' => 'When driving in wet weather, you should reduce your speed by approximately:',
                        'options' => [
                            '2-3 mph',
                            '5-10 mph',
                            '15-20 mph',
                            '25-30 mph',
                        ],
                        'correct_answer' => '5-10 mph',
                        'explanation' => 'Reducing speed by 5-10 mph in wet conditions helps maintain traction and control.',
                    ],
                    [
                        'question' => 'Roads are most slippery:',
                        'options' => [
                            'During heavy downpours',
                            'In the first rainfall after a dry period',
                            'When it has been raining for several hours',
                            'Only during winter storms',
                        ],
                        'correct_answer' => 'In the first rainfall after a dry period',
                        'explanation' => 'The first rain after dry weather loosens accumulated oil on the road surface, making it very slippery.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 6: Intersections and Right-of-Way',
                'description' => 'Mastering intersection navigation, traffic signals, and right-of-way rules',
                'duration' => 35,
                'content' => 'Intersections are among the most dangerous areas on the road. This chapter covers safe intersection navigation and right-of-way rules.

**Types of Intersections:**
- Controlled (with signals or signs)
- Uncontrolled (no signals or signs)
- T-intersections
- Roundabouts
- Multi-lane intersections

**Right-of-Way Rules:**
- At four-way stops: First to arrive goes first, or rightmost vehicle if simultaneous arrival
- At T-intersections: Through traffic has right-of-way
- When turning left: Yield to oncoming traffic
- Emergency vehicles always have right-of-way

**Intersection Safety Tips:**
- Look both directions before proceeding, even on green lights
- Be aware of red-light runners
- Position vehicle for maximum visibility
- Use turn signals at least 100 feet before turning
- Never block intersections

**Two-Way Left Turn Lanes:**
- Use only for turning left or making permitted U-turns
- Do not drive in them for more than 200 feet
- Never use for passing
- Enter only when safe and clear',
                'questions' => [
                    [
                        'question' => 'At a four-way stop, if two vehicles arrive simultaneously, which vehicle has the right-of-way?',
                        'options' => [
                            'The larger vehicle',
                            'The vehicle on the left',
                            'The vehicle on the right',
                            'The vehicle going straight',
                        ],
                        'correct_answer' => 'The vehicle on the right',
                        'explanation' => 'When vehicles arrive simultaneously at a four-way stop, the vehicle on the right has the right-of-way.',
                    ],
                    [
                        'question' => 'How far before an intersection should you activate your turn signal?',
                        'options' => [
                            '50 feet',
                            '75 feet',
                            '100 feet',
                            '150 feet',
                        ],
                        'correct_answer' => '100 feet',
                        'explanation' => 'Turn signals should be activated at least 100 feet before reaching an intersection to give other drivers adequate warning.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 7: Fatigue and Emotional Control',
                'description' => 'Managing driver fatigue, emotions, and maintaining focus while driving',
                'duration' => 35,
                'content' => 'Driver fatigue and emotions are major contributors to traffic collisions. This chapter addresses these critical safety factors.

**Driver Fatigue Statistics:**
- 31% of all drivers have fallen asleep at the wheel at least once
- 100,000 accidents each year are caused by sleeping at the wheel
- 1,500 people die as a result of falling asleep at the wheel
- 100 million people drive while drowsy each year

**Signs of Fatigue:**
- Heavy eyelids or frequent blinking
- Difficulty focusing or keeping eyes open
- Daydreaming or wandering thoughts
- Drifting from your lane
- Missing exits or traffic signs

**Long Road Trip Tips:**
- Get 6-8 hours of sleep before departure
- Take breaks every 2 hours or 100 miles
- Avoid driving late at night when your body wants to sleep
- Stay hydrated and avoid heavy meals
- Share driving responsibilities when possible

**Emotional Control:**
- Road rage is a criminal offense with serious penalties
- Take deep breaths and remain calm in traffic
- Don\'t take other drivers\' actions personally
- Pull over safely if you need to cool down
- Listen to calming music to reduce stress',
                'questions' => [
                    [
                        'question' => 'Approximately how many accidents each year are caused by drivers falling asleep at the wheel?',
                        'options' => [
                            '50,000',
                            '75,000',
                            '100,000',
                            '125,000',
                        ],
                        'correct_answer' => '100,000',
                        'explanation' => 'Statistics show that 100,000 accidents each year are caused by drivers falling asleep at the wheel.',
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
                        'explanation' => 'Studies show that 31% of all drivers have fallen asleep at the wheel at least once.',
                    ],
                ],
            ],
            [
                'title' => 'Chapter 8: Collision Avoidance and Defensive Techniques',
                'description' => 'Advanced defensive driving techniques and collision avoidance strategies',
                'duration' => 40,
                'content' => 'This chapter covers advanced defensive driving techniques and strategies for avoiding collisions.

**Collision Avoidance Options:**
When facing a potential collision, you have three choices:
1. **Speed Up** - Sometimes accelerating can avoid a collision, but this can be risky
2. **Stop** - Braking may avoid one collision but could cause rear-end collisions
3. **Evade** - Often the best choice; always leave yourself an escape route

**Scanning Techniques:**
- Scan 10-15 seconds ahead at highway speeds (about 1/4 mile)
- Keep eyes moving to avoid tunnel vision
- Check mirrors every 2-5 seconds
- Be aware of vehicles in your blind spots
- Watch for hazards from all directions

**Lane Selection:**
- Choose lanes with smoothest traffic flow
- Middle lanes are usually best for steady flow
- Left lane for passing only
- Right lane for slower traffic and exits
- Avoid driving in clusters of vehicles

**Space Management:**
- Maintain largest possible space cushion around your vehicle
- Don\'t drive in other drivers\' blind spots
- Position yourself between multiple hazards
- Give more room to the greater danger

**The "No Zone":**
Large trucks have significant blind spots:
- Directly in front and behind
- Along both sides, especially the right side
- Stay visible to truck drivers at all times',
                'questions' => [
                    [
                        'question' => 'At highway speeds, how far ahead should you scan for hazards?',
                        'options' => [
                            '5-8 seconds (about 500 feet)',
                            '10-15 seconds (about 1/4 mile)',
                            '20-25 seconds (about 1/2 mile)',
                            '30 seconds (about 1 mile)',
                        ],
                        'correct_answer' => '10-15 seconds (about 1/4 mile)',
                        'explanation' => 'At highway speeds, scanning 10-15 seconds ahead (about 1/4 mile) allows adequate time to identify and respond to hazards.',
                    ],
                    [
                        'question' => 'When facing a potential collision, which option is often the best choice?',
                        'options' => [
                            'Speed up',
                            'Stop suddenly',
                            'Evade (steer away)',
                            'Close your eyes and hope',
                        ],
                        'correct_answer' => 'Evade (steer away)',
                        'explanation' => 'Evasive steering is often the best collision avoidance technique, which is why defensive drivers always maintain escape routes.',
                    ],
                ],
            ],
        ];

        foreach ($extendedChapters as $index => $chapterData) {
            // Start chapter numbering from 5 (since main seeder has chapters 1-4)
            $chapterOrder = $index + 5;

            $chapterId = DB::table('chapters')->insertGetId([
                'course_id' => $courseId,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'],

                'order_index' => $chapterOrder,
                'duration' => $chapterData['duration'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Create questions for this chapter
            if (isset($chapterData['questions'])) {
                foreach ($chapterData['questions'] as $qIndex => $questionData) {
                    DB::table('questions')->insert([
                        'chapter_id' => $chapterId,
                        'course_id' => $courseId,
                        'question_text' => $questionData['question'],
                        'question_type' => 'multiple_choice',
                        'options' => json_encode($questionData['options']),
                        'correct_answer' => $questionData['correct_answer'],
                        'explanation' => $questionData['explanation'],
                        'order_index' => $qIndex + 1,
                        'points' => 1,

                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }

        $this->command->info('Extended Florida Defensive Driving Course chapters created successfully!');
    }
}

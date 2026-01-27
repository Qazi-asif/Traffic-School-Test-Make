<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissouriQuestionsSeeder extends Seeder
{
    public function run()
    {
        // Get Missouri course chapters
        $missouriCourse = DB::table('florida_courses')->where('state', 'Missouri')->first();
        if (! $missouriCourse) {
            $this->command->error('Missouri course not found!');

            return;
        }

        $chapters = DB::table('chapters')->where('course_id', $missouriCourse->id)->orderBy('order_index')->get();

        $quizData = $this->getQuizData();

        foreach ($quizData as $chapterIndex => $questions) {
            $chapter = $chapters[$chapterIndex] ?? null;
            if (! $chapter) {
                continue;
            }

            foreach ($questions as $index => $q) {
                $options = array_filter([$q['a'], $q['b'], $q['c'], $q['d'], $q['e'] ?? null]);
                $correctIndex = ord($q['correct']) - ord('A');
                $correctAnswer = $options[$correctIndex];

                DB::table('questions')->insert([
                    'chapter_id' => $chapter->id,
                    'course_id' => $missouriCourse->id,
                    'question_text' => $q['q'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode(array_values($options)),
                    'correct_answer' => $correctAnswer,
                    'order_index' => $index + 1,
                    'points' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Missouri questions seeded successfully!');
    }

    private function getQuizData()
    {
        return [
            // Chapter 1
            [
                ['q' => 'The two-way left turn lane_______.', 'a' => 'may not be used for passing.', 'b' => 'Can never be used for U-turns.', 'c' => 'Are set aside for the use of vehicles turning left or right.', 'd' => 'Both C and B are correct.', 'e' => null, 'correct' => 'A'],
                ['q' => 'You should scan the road__________ ahead of your vehicle.', 'a' => '1 to 2 seconds', 'b' => '30 to 35 seconds', 'c' => '10-15 seconds', 'd' => '½ mile', 'e' => null, 'correct' => 'C'],
                ['q' => 'The following is incorrect. Pedestrians have a duty to__________.', 'a' => 'Cross at an intersection or crosswalk', 'b' => 'Stay out of bike lanes.', 'c' => 'Yield to vehicles when in crosswalk', 'd' => 'Not cross diagonally at an intersection.', 'e' => null, 'correct' => 'C'],
                ['q' => 'Drivers must obey signals from school crossing guards____________.', 'a' => 'During school hours', 'b' => 'If they go to that school', 'c' => 'At all times', 'd' => 'If it isn\'t a school holiday', 'e' => null, 'correct' => 'C'],
                ['q' => 'Drivers in the city face_________.', 'a' => 'A greater space cushion.', 'b' => 'Higher speeds and faster decision times', 'c' => 'Slower vehicles in special lanes', 'd' => 'Distractions from noise, advertisements and traffic signs', 'e' => null, 'correct' => 'D'],
                ['q' => 'Blind pedestrians____________.', 'a' => 'Always have a white cane and a dog for easy recognition.', 'b' => 'May or may not have a white cane or dog with them.', 'c' => 'Must follow all regular pedestrian rules.', 'd' => 'Must yield the right of way in heavy traffic.', 'e' => null, 'correct' => 'B'],
                ['q' => 'When emerging from an alley, you are not suppose to____________.', 'a' => 'your right of way to make a right turn onto the roadway.', 'b' => 'permitted to sit over a sidewalk before you carefully make a turn.', 'c' => 'illegal to stop on the sidewalk while you check traffic to make your turn.', 'd' => 'all of the above.', 'e' => null, 'correct' => 'D'],
                ['q' => 'Endangerment of a highway worker is now a________________.', 'a' => 'problem debated in the state legislature.', 'b' => 'Crime punishable by a fine of up to $2000, if no one is hurt.', 'c' => 'Crime punishable by a fine of up to $10,000 if there is injury.', 'd' => 'B and C are correct.', 'e' => null, 'correct' => 'D'],
                ['q' => 'Missouri\'s "Move Over Law" states in part that,', 'a' => 'When an emergency vehicle approaches, motorists must move over.', 'b' => 'When law enforcement flashes a blue light, traffic must move over.', 'c' => 'When a trucker is in your blind spot, the motorist must move over.', 'd' => 'When a slow-moving vehicle blocks 5 or more cars it must move over.', 'e' => null, 'correct' => 'A'],
                ['q' => 'Motorcycles are on city streets, and:', 'a' => '98% of accidents with motorcycles and bikes result in injury.', 'b' => 'Drivers of cars often violate the motorcyclist right of way.', 'c' => 'It can be harder to see motorcyclists on the road because of their size.', 'd' => 'All of the above.', 'e' => null, 'correct' => 'D'],
            ],
            // Chapter 2
            [
                ['q' => 'The purpose of traffic signs are', 'a' => 'to serve as traffic control', 'b' => 'to communicate warnings', 'c' => 'to Express traffic regulations', 'd' => 'all of the above', 'e' => null, 'correct' => 'D'],
                ['q' => 'A circular sign with letters R R alerts the driver of', 'a' => 'approaching railroad crossing', 'b' => 'rough road conditions', 'c' => 'road construction', 'd' => 'none of the above', 'e' => null, 'correct' => 'A'],
                ['q' => 'if the lights are flashing red on a school bus, drivers must:', 'a' => 'pass the bus at a safely reduce speed', 'b' => 'stop and wait until the lights stop flashing', 'c' => 'stop briefly then proceed with extreme caution', 'd' => 'pull over immediately to help the children off the bus', 'e' => null, 'correct' => 'B'],
                ['q' => 'One set of solid yellow lines:', 'a' => 'served as a speed zone.', 'b' => 'separates opposing traffic on two lane roads.', 'c' => 'means do not pass.', 'd' => 'B and C', 'e' => null, 'correct' => 'D'],
                ['q' => 'thick white lines:', 'a' => 'show where a bicycle lane runs.', 'b' => 'Mark where the car is and it should stop on the road at intersections.', 'c' => 'are used only for crosswalks.', 'd' => 'means "road clear, roll through slowly."', 'e' => null, 'correct' => 'B'],
                ['q' => 'Parking violations for parking in disabled spaces:', 'a' => 'can start at $250 for first offense.', 'b' => 'are usually about $25.', 'c' => 'Are given after a warning ticket has been issued.', 'd' => 'are rarely given.', 'e' => null, 'correct' => 'A'],
                ['q' => 'At railroad crossings:', 'a' => 'drivers of hazardous materials are required to stop. Be patient.', 'b' => 'drivers should never, ever stop on railroad tracks.', 'c' => 'drivers need to stop at least 20 feet in front of the crossing guard when it\'s flashing.', 'd' => 'All of the above', 'e' => null, 'correct' => 'C'],
                ['q' => 'Yellow traffic sign serves as:', 'a' => 'an indication that a yield or stop ahead', 'b' => 'A warning sign', 'c' => 'A reminder of speed limits.', 'd' => 'indicates motorist services and rest stops.', 'e' => null, 'correct' => 'B'],
                ['q' => 'Signs with a red circle with a diagonal line through it always means', 'a' => 'proceed with caution', 'b' => 'yield', 'c' => 'traffic signal ahead', 'd' => '"NO" (not allowed)', 'e' => null, 'correct' => 'D'],
                ['q' => 'Failure to stop one a school buses lights are flashing red:', 'a' => 'carries a 90 to 120 day license suspension.', 'b' => 'carries a maximum 180 day license suspension for first offenders', 'c' => 'A and B', 'd' => 'carries a minimum two month license suspension', 'e' => null, 'correct' => 'A'],
            ],
            // Chapter 3
            [
                ['q' => 'you may not make a U-turn______.', 'a' => 'on a one-way street.', 'b' => 'in front of a fire station', 'c' => 'when vehicles may hit you.', 'd' => 'all of the above', 'e' => 'none of the above', 'correct' => 'D'],
                ['q' => 'chapter 2 begins by stating that_______.', 'a' => 'driving through disaster areas is a unique hazard.', 'b' => 'following emergency vehicles is a no – no.', 'c' => 'almost one out of every three car accidents is at an intersection.', 'd' => 'Time restrictions on carpool lanes are responsible for traffic jams.', 'e' => 'most drivers would be better off just staying home.', 'correct' => 'C'],
                ['q' => 'In the turn signal review and the end of the chapter, the car illustrated is______.', 'a' => 'White', 'b' => 'Black', 'c' => 'yellow', 'd' => 'Green', 'e' => 'Red', 'correct' => 'C'],
                ['q' => 'make a full stop for a red light __________ the crosswalk.', 'a' => 'in', 'b' => 'in front of', 'c' => 'after', 'd' => 'inside', 'e' => null, 'correct' => 'B'],
                ['q' => 'you can make a right turn on red, provided ______.', 'a' => 'you have first stopped completely.', 'b' => 'there is nobody signaling across the road.', 'c' => 'you have checked to make sure there is no oncoming traffic.', 'd' => 'A and C are correct', 'e' => null, 'correct' => 'D'],
                ['q' => 'when approaching a four-way stop with others:', 'a' => 'The driver on the right has the right of way.', 'b' => 'The driver on the left has the right away.', 'c' => 'drivers signal to determine who has the right of way.', 'd' => 'all of the above.', 'e' => null, 'correct' => 'A'],
                ['q' => 'The speed limit in an alley is:', 'a' => '15 mph', 'b' => '20 mph', 'c' => '25 mph', 'd' => '30 mph', 'e' => null, 'correct' => 'A'],
                ['q' => 'when approaching an intersection,', 'a' => 'be ready to stop or yield, even if there is no posted sign.', 'b' => 'always keep a lookout for pedestrians and bicyclists.', 'c' => 'The rule of thumb is to be courteous and be safe.', 'd' => 'all of the above.', 'e' => null, 'correct' => 'D'],
                ['q' => 'when turning left at an intersection,', 'a' => 'you may have to turn even into oncoming traffic, in order to clear it.', 'b' => 'you may turn only when it is safe to do so.', 'c' => 'you may block traffic if you need to get across.', 'd' => 'all of the above.', 'e' => null, 'correct' => 'B'],
                ['q' => 'roundabout are used:', 'a' => 'more often now in Missouri.', 'b' => 'to create safer high speed limits.', 'c' => 'we are low traffic doesn\'t justify a 4 way stop.', 'd' => 'A and C.', 'e' => null, 'correct' => 'D'],
            ],
            // Chapters 4-10 with minimal questions
            [['q' => 'Class F licenses allow drivers to operate________.', 'a' => 'Any noncommercial vehicles.', 'b' => 'Only motorcycles.', 'c' => 'Any four-axle vehicle.', 'd' => 'None of the above.', 'e' => null, 'correct' => 'A']],
            [['q' => 'Freeway drivers should always be familiar with______.', 'a' => 'alternative routes', 'b' => 'exits', 'c' => 'Side streets', 'd' => 'A and B are correct', 'e' => 'none of the above', 'correct' => 'D']],
            [['q' => 'You should always drive on the right-most lane of the road except______.', 'a' => 'when passing another vehicle.', 'b' => 'when making a left turn.', 'c' => 'when it\'s closed to traffic.', 'd' => 'all of the above.', 'e' => 'none of the above.', 'correct' => 'D']],
            [['q' => 'One of Chapter 7\'s tips states that backing up is ______ at any speed.', 'a' => 'Easy', 'b' => 'Unnecessary', 'c' => 'Unsafe', 'd' => 'Safe', 'e' => 'Reckless', 'correct' => 'C']],
            [['q' => 'If you run into water on the road, don\'t ______.', 'a' => 'Drive in the tracks of the car ahead.', 'b' => 'Judge how deep floodwater is before you enter it.', 'c' => 'Accelerate at a good pace through rushing water.', 'd' => 'Test your brakes by gently pushing down on them.', 'e' => null, 'correct' => 'C']],
            [['q' => 'In Chapter 9, we discuss that the __________ are a myth.', 'a' => 'Stages of intoxication', 'b' => 'Explanations of BAC charts', 'c' => 'intoxicating effects of brown vs. clear drinks', 'd' => 'severe penalties for any DUI related charge', 'e' => null, 'correct' => 'C']],
            [['q' => 'Which of the following statements are true?', 'a' => 'Honking can be considered aggressive driving.', 'b' => 'Forcing a driver to pull over to have a chat or get a phone number is friendly', 'c' => 'Cutting off a driver is acceptable because it happens everyday.', 'd' => 'Making obscene gestures is basically harmless.', 'e' => null, 'correct' => 'A']],
        ];
    }
}

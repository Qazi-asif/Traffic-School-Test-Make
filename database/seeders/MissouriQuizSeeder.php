<?php

namespace Database\Seeders;

use App\Models\MissouriCourseStructure;
use App\Models\MissouriQuizBank;
use Illuminate\Database\Seeder;

class MissouriQuizSeeder extends Seeder
{
    public function run()
    {
        $chapters = MissouriCourseStructure::orderBy('chapter_number')->get();

        $quizzes = [
            // Chapter 1 Quiz
            [1, 'The two-way left turn lane_______.', 'may not be used for passing.', 'Can never be used for U-turns.', 'Are set aside for the use of vehicles turning left or right.', 'Both C and B are correct.', null, 'A', 'medium'],
            [1, 'You should scan the road__________ ahead of your vehicle.', '1 to 2 seconds', '30 to 35 seconds', '10-15 seconds', '½ mile', null, 'C', 'medium'],
            [1, 'The following is incorrect. Pedestrians have a duty to__________.', 'Cross at an intersection or crosswalk', 'Stay out of bike lanes.', 'Yield to vehicles when in crosswalk', 'Not cross diagonally at an intersection.', null, 'C', 'hard'],
            [1, 'Drivers must obey signals from school crossing guards____________.', 'During school hours', 'If they go to that school', 'At all times', 'If it isn\'t a school holiday', null, 'C', 'easy'],
            [1, 'Drivers in the city face_________.', 'A greater space cushion.', 'Higher speeds and faster decision times', 'Slower vehicles in special lanes', 'Distractions from noise, advertisements and traffic signs', null, 'D', 'medium'],
            [1, 'Blind pedestrians____________.', 'Always have a white cane and a dog for easy recognition.', 'May or may not have a white cane or dog with them.', 'Must follow all regular pedestrian rules.', 'Must yield the right of way in heavy traffic.', null, 'B', 'medium'],
            [1, 'When emerging from an alley, you are not suppose to____________.', 'your right of way to make a right turn onto the roadway.', 'permitted to sit over a sidewalk before you carefully make a turn.', 'illegal to stop on the sidewalk while you check traffic to make your turn.', 'all of the above.', null, 'D', 'hard'],
            [1, 'Endangerment of a highway worker is now a________________.', 'problem debated in the state legislature.', 'Crime punishable by a fine of up to $2000, if no one is hurt.', 'Crime punishable by a fine of up to $10,000 if there is injury.', 'B and C are correct.', null, 'D', 'medium'],
            [1, 'Missouri\'s "Move Over Law" states in part that,', 'When an emergency vehicle approaches, motorists must move over.', 'When law enforcement flashes a blue light, traffic must move over.', 'When a trucker is in your blind spot, the motorist must move over.', 'When a slow-moving vehicle blocks 5 or more cars it must move over.', null, 'A', 'easy'],
            [1, 'Motorcycles are on city streets, and:', '98% of accidents with motorcycles and bikes result in injury.', 'Drivers of cars often violate the motorcyclist right of way.', 'It can be harder to see motorcyclists on the road because of their size.', 'All of the above.', null, 'D', 'easy'],

            // Chapter 2 Quiz
            [2, 'The purpose of traffic signs are', 'to serve as traffic control', 'to communicate warnings', 'to Express traffic regulations', 'all of the above', null, 'D', 'easy'],
            [2, 'A circular sign with letters R R alerts the driver of', 'approaching railroad crossing', 'rough road conditions', 'road construction', 'none of the above', null, 'A', 'easy'],
            [2, 'if the lights are flashing red on a school bus, drivers must:', 'pass the bus at a safely reduce speed', 'stop and wait until the lights stop flashing', 'stop briefly then proceed with extreme caution', 'pull over immediately to help the children off the bus', null, 'B', 'easy'],
            [2, 'One set of solid yellow lines:', 'served as a speed zone.', 'separates opposing traffic on two lane roads.', 'means do not pass.', 'B and C', null, 'D', 'medium'],
            [2, 'thick white lines:', 'show where a bicycle lane runs.', 'Mark where the car is and it should stop on the road at intersections.', 'are used only for crosswalks.', 'means "road clear, roll through slowly."', null, 'B', 'medium'],
            [2, 'Parking violations for parking in disabled spaces:', 'can start at $250 for first offense.', 'are usually about $25.', 'Are given after a warning ticket has been issued.', 'are rarely given.', null, 'A', 'medium'],
            [2, 'At railroad crossings:', 'drivers of hazardous materials are required to stop. Be patient.', 'drivers should never, ever stop on railroad tracks.', 'drivers need to stop at least 20 feet in front of the crossing guard when it\'s flashing.', 'All of the above', null, 'C', 'hard'],
            [2, 'Yellow traffic sign serves as:', 'an indication that a yield or stop ahead', 'A warning sign', 'A reminder of speed limits.', 'indicates motorist services and rest stops.', null, 'B', 'easy'],
            [2, 'Signs with a red circle with a diagonal line through it always means', 'proceed with caution', 'yield', 'traffic signal ahead', '"NO" (not allowed)', null, 'D', 'easy'],
            [2, 'Failure to stop one a school buses lights are flashing red:', 'carries a 90 to 120 day license suspension.', 'carries a maximum 180 day license suspension for first offenders', 'A and B', 'carries a minimum two month license suspension', null, 'A', 'hard'],

            // Chapter 3 Quiz
            [3, 'you may not make a U-turn______.', 'on a one-way street.', 'in front of a fire station', 'when vehicles may hit you.', 'all of the above', 'none of the above', 'D', 'medium'],
            [3, 'chapter 2 begins by stating that_______.', 'driving through disaster areas is a unique hazard.', 'following emergency vehicles is a no – no.', 'almost one out of every three car accidents is at an intersection.', 'Time restrictions on carpool lanes are responsible for traffic jams.', 'most drivers would be better off just staying home.', 'C', 'easy'],
            [3, 'In the turn signal review and the end of the chapter, the car illustrated is______.', 'White', 'Black', 'yellow', 'Green', 'Red', 'C', 'easy'],
            [3, 'make a full stop for a red light __________ the crosswalk.', 'in', 'in front of', 'after', 'inside', null, 'B', 'medium'],
            [3, 'you can make a right turn on red, provided ______.', 'you have first stopped completely.', 'there is nobody signaling across the road.', 'you have checked to make sure there is no oncoming traffic.', 'A and C are correct', null, 'D', 'medium'],
            [3, 'when approaching a four-way stop with others:', 'The driver on the right has the right of way.', 'The driver on the left has the right away.', 'drivers signal to determine who has the right of way.', 'all of the above.', null, 'A', 'medium'],
            [3, 'The speed limit in an alley is:', '15 mph', '20 mph', '25 mph', '30 mph', null, 'A', 'hard'],
            [3, 'when approaching an intersection,', 'be ready to stop or yield, even if there is no posted sign.', 'always keep a lookout for pedestrians and bicyclists.', 'The rule of thumb is to be courteous and be safe.', 'all of the above.', null, 'D', 'easy'],
            [3, 'when turning left at an intersection,', 'you may have to turn even into oncoming traffic, in order to clear it.', 'you may turn only when it is safe to do so.', 'you may block traffic if you need to get across.', 'all of the above.', null, 'B', 'medium'],
            [3, 'roundabout are used:', 'more often now in Missouri.', 'to create safer high speed limits.', 'we are low traffic doesn\'t justify a 4 way stop.', 'A and C.', null, 'D', 'medium'],
        ];

        foreach ($quizzes as $quiz) {
            $chapterNum = $quiz[0];
            $chapter = $chapters->where('chapter_number', $chapterNum)->first();

            if ($chapter) {
                MissouriQuizBank::create([
                    'chapter_id' => $chapter->id,
                    'question_text' => $quiz[1],
                    'option_a' => $quiz[2],
                    'option_b' => $quiz[3],
                    'option_c' => $quiz[4],
                    'option_d' => $quiz[5],
                    'option_e' => $quiz[6],
                    'correct_answer' => $quiz[7],
                    'difficulty_level' => $quiz[8],
                ]);
            }
        }
    }
}

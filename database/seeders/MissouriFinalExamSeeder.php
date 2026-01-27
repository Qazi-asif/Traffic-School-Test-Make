<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissouriFinalExamSeeder extends Seeder
{
    public function run()
    {
        $course = DB::table('courses')
            ->where('title', 'like', '%Missouri%')
            ->first();

        if (! $course) {
            $this->command->error('Missouri course not found!');

            return;
        }

        $finalExamChapter = DB::table('chapters')
            ->where('course_id', $course->id)
            ->where(function ($query) {
                $query->where('title', 'like', '%Final Exam%')
                    ->orWhere('order_index', 11);
            })
            ->first();

        if (! $finalExamChapter) {
            $this->command->error('Final Exam chapter not found!');

            return;
        }

        $questions = [
            ['question' => 'Class F licenses allow drivers to operate ______.', 'options' => ['Any non-commercial vehicle.', 'only motorcycles', 'Any four-axle vehicle', 'None of the above.'], 'correct_answer' => 'Any non-commercial vehicle.'],
            ['question' => 'When you obtain your license it is a big commitment. It is very much like a ______!', 'options' => ['Marriage.', 'conquest.', 'shotgun wedding', 'trip around the world.', 'honeymoon'], 'correct_answer' => 'Marriage.'],
            ['question' => 'Which of the following statements is true regarding intermediate licenses?', 'options' => ['From 16 to 18 years of age you may apply for an intermediate license.', 'You may not have more than 1 other person under 19 in the car with you for the first 6 months on an intermediate license.', 'You must have 40 hours of instruction, 10 hours behind the wheel to apply.', 'All of the above are true.'], 'correct_answer' => 'All of the above are true.'],
            ['question' => 'When a police officer signals for you to pull over:', 'options' => ['you must slow down while driving home.', 'correct any poor driving problem you were exhibiting.', 'you must direct your car to the side of the road and stop at the first safe place.', 'you should immediately call 911 if you have a cell phone.', 'None of the above.'], 'correct_answer' => 'you must direct your car to the side of the road and stop at the first safe place.'],
            ['question' => 'The purpose of traffic signs are', 'options' => ['To serve as traffic control', 'To communicate warnings', 'To express traffic regulations', 'All of the above'], 'correct_answer' => 'All of the above'],
            ['question' => 'A circular sign with the letters R R alerts the driver of', 'options' => ['Approaching railroad crossing', 'Rough road conditions', 'Road construction', 'None of the above'], 'correct_answer' => 'Approaching railroad crossing'],
            ['question' => 'If the lights are flashing red on a school bus, drivers must:', 'options' => ['Pass the bus at a safely reduced speed', 'Stop and wait until the lights stop flashing', 'Stop briefly then proceed with extreme caution', 'Pull over immediately to help the children off the bus'], 'correct_answer' => 'Stop and wait until the lights stop flashing'],
            ['question' => 'One set of solid yellow lines:', 'options' => ['served as a speed zone.', 'separates opposing traffic on two lane roads.', 'means do not pass.', 'B and C'], 'correct_answer' => 'B and C'],
            ['question' => 'Thick white lines:', 'options' => ['show where a bicycle lane runs.', 'Mark where the car is and it should stop on the road at intersections.', 'are used only for crosswalks.', 'means " road clear, roll through slowly."'], 'correct_answer' => 'Mark where the car is and it should stop on the road at intersections.'],
            ['question' => 'you may not make a U-turn______.', 'options' => ['on a one-way street.', 'in front of a fire station', 'when vehicles may hit you.', 'all of the above', 'none of the above'], 'correct_answer' => 'all of the above'],
            ['question' => 'chapter 2 begins by stating that_______.', 'options' => ['driving through disaster areas is a unique hazard.', 'following emergency vehicles is a no – no.', 'almost one out of every three car accidents is at an intersection.', 'Time restrictions on carpool lanes are responsible for traffic jams.', 'most drivers would be better off just staying home.'], 'correct_answer' => 'almost one out of every three car accidents is at an intersection.'],
            ['question' => 'In the turn signal review and the end of the chapter, the car illustrated is_______.', 'options' => ['White', 'Black', 'yellow', 'Green', 'Red'], 'correct_answer' => 'yellow'],
            ['question' => 'Make a full stop for a red light __________ the crosswalk.', 'options' => ['in', 'in front of', 'after', 'inside'], 'correct_answer' => 'in front of'],
            ['question' => 'You can make a right turn on red, provided ______.', 'options' => ['you have first stopped completely.', 'there is nobody signaling across the road.', 'you have checked to make sure there is no oncoming traffic.', 'A and C are correct'], 'correct_answer' => 'A and C are correct'],
            ['question' => 'A two-way left turn lane ______.', 'options' => ['may not be used for passing.', 'can never be used for u-turns.', 'are set aside for the use of vehicles turning left or right.', 'Both C and B are correct.'], 'correct_answer' => 'may not be used for passing.'],
            ['question' => 'You should scan down the road __________ ahead of your vehicle.', 'options' => ['1 to 2 seconds', '30 to 35 seconds', '10 to 15 seconds', '½ mile'], 'correct_answer' => '10 to 15 seconds'],
            ['question' => 'The following is incorrect. Pedestrians have a duty to __________.', 'options' => ['cross at an intersection or crosswalk.', 'stay out of bike lanes.', 'yield to vehicles when in a crosswalk.', 'not cross diagonally at an intersection.'], 'correct_answer' => 'yield to vehicles when in a crosswalk.'],
            ['question' => 'Drivers must obey signals from school crossing guards _________.', 'options' => ['during school hours', 'if they go to school', 'at all times', 'if it isn\'t a school holiday.'], 'correct_answer' => 'at all times'],
            ['question' => 'Drivers in the city face ______ .', 'options' => ['A greater space cushion', 'Higher speeds and faster decision times.', 'Slower vehicles in special lanes.', 'Distractions from noise, advertisements and traffic signs.'], 'correct_answer' => 'Distractions from noise, advertisements and traffic signs.'],
            ['question' => 'Freeway drivers should always be familiar with______.', 'options' => ['alternative routes', 'exits', 'Side streets', 'A and B are correct', 'none of the above'], 'correct_answer' => 'A and B are correct'],
            ['question' => 'Our course describes Highway Hypnosis as______.', 'options' => ['drivers looking deep into each other\'s eyes as they pass.', 'A trance-like condition brought on by continuous or monotonous driving.', 'No one feels the need to signal to change lanes.', 'Something ahead that captures the attention of a group of drivers.', 'using a cell phone while trying to read a map.'], 'correct_answer' => 'A trance-like condition brought on by continuous or monotonous driving.'],
            ['question' => 'A nickname for a trucker\'s Blind Spot is the______.', 'options' => ['Dead Zone', 'Danger Zone', 'Dark Territory', 'No Zone'], 'correct_answer' => 'No Zone'],
            ['question' => 'Lane changes should be______.', 'options' => ['avoided whenever possible.', 'fast, effortless and without the need for a signal.', 'efficient, quick, and more than one at a time.', 'gradual, one at a time, and with your turn signal on.', 'None of the above'], 'correct_answer' => 'gradual, one at a time, and with your turn signal on.'],
            ['question' => 'Scan the road from side to side to take in the whole scene about every_______.', 'options' => ['15 seconds', '60 seconds', '6 seconds', '15 minutes'], 'correct_answer' => '15 seconds'],
            ['question' => 'You should always drive on the right-most lane of the road except______.', 'options' => ['when passing another vehicle.', 'when making a left turn.', 'when it\'s closed to traffic.', 'all of the above.', 'none of the above.'], 'correct_answer' => 'all of the above.'],
            ['question' => 'A designated place where you can stop to let other vehicles pass you is called?', 'options' => ['A roadway', 'A slow-moving vehicle lane', 'A turn-out', 'A grade', 'A toll both'], 'correct_answer' => 'A turn-out'],
            ['question' => '_________ act as a wall, do not cross over them.', 'options' => ['broken yellow lines', 'broken white lines', 'solid white lines', 'all of the above'], 'correct_answer' => 'solid white lines'],
            ['question' => '__________ divide two way roads and separate traffic moving in opposite directions.', 'options' => ['broken yellow lines', 'solid yellow lines', 'solid white lines', 'A and B'], 'correct_answer' => 'A and B'],
            ['question' => 'The law requires you to drive on the ________ of the road at all times, with a few exceptions.', 'options' => ['left side and center', 'right side and center', 'right side', 'A and C'], 'correct_answer' => 'right side'],
            ['question' => 'One of Chapter\'s  "Tips" states that backing up is ______ at any speed.', 'options' => ['Easy', 'Unnecessary', 'Unsafe', 'Safe', 'Reckless'], 'correct_answer' => 'Unsafe'],
            ['question' => 'Driving too slow can cause gridlock, so there are________speed laws.', 'options' => ['maximum', 'medium', 'minimum', 'moderate'], 'correct_answer' => 'minimum'],
            ['question' => 'Most interstate highways in Missouri\'s urban areas have a speed limit of ________.', 'options' => ['60 mph', '70 mph', '55 mph', '45 mph'], 'correct_answer' => '60 mph'],
            ['question' => 'When lights are flashing and children are present, the speed around a school zone is:', 'options' => ['35 mph', '15 mph', '25 mph', 'none of the above.'], 'correct_answer' => '25 mph'],
            ['question' => 'The basic fine for a first time reckless driving offense is______.', 'options' => ['$80', '$180', '$280', '$580'], 'correct_answer' => '$580'],
            ['question' => 'If you run into water on the road, don\'t ______.', 'options' => ['Drive in the tracks of the car ahead.', 'Judge how deep floodwater is before you enter it.', 'Accelerate at a good pace through rushing water.', 'Test your brakes by gently pushing down on them.'], 'correct_answer' => 'Accelerate at a good pace through rushing water.'],
            ['question' => 'Your vehicle must have ______.', 'options' => ['studded snow tires.', 'a white light that shines on your license plate.', 'a muffler and tail pipe.', 'B and C'], 'correct_answer' => 'B and C'],
            ['question' => 'If you have airbags on the passenger side of the car NEVER _______.', 'options' => ['place a child 12 or under in the front seat.', 'place a child 12 or over in the front seat.', 'let anyone over 100 pounds sit there.', 'none of the above.'], 'correct_answer' => 'place a child 12 or under in the front seat.'],
            ['question' => 'To maintain good traction:', 'options' => ['never accelerate suddenly on slippery surfaces.', 'make sure you have good tires with enough tread on them.', 'gently tap your brakes, do not slam down on them', 'all of the above.'], 'correct_answer' => 'all of the above.'],
            ['question' => 'When driving through fog_______.', 'options' => ['make yourself as visible as possible.', 'use headlights and fog lights', 'A and B', 'you may blind other drivers with your lights.'], 'correct_answer' => 'A and B'],
            ['question' => 'The ___________ are a myth.', 'options' => ['Stages of intoxication', 'Explanations of BAC charts', 'intoxicating effects of brown vs. clear drinks', 'severe penalties for any DUI related charge'], 'correct_answer' => 'intoxicating effects of brown vs. clear drinks'],
            ['question' => 'If a person has had more than one drink an hour, ______ hour(s) of sobering up should be allowed for each extra drink.', 'options' => ['1', '2', '3', '½', '1 ½'], 'correct_answer' => '1'],
            ['question' => 'A designated driver should:', 'options' => ['be 21 year of age', 'be abstaining from alcohol completely', 'possess a valid drivers license', 'All of the above'], 'correct_answer' => 'All of the above'],
            ['question' => 'Legally, a DUI for prescription drugs ______ a DUI for alcohol.', 'options' => ['is very different from', 'is pretty much the same as', 'is somewhat unlike', 'all of the above is true'], 'correct_answer' => 'is pretty much the same as'],
            ['question' => 'In Missouri, you can drive with an open container of alcohol in the car_______.', 'options' => ['in some places', 'nowhere', 'when you cross county lines', 'everywhere'], 'correct_answer' => 'in some places'],
            ['question' => 'Which of the following statements are true?', 'options' => ['Honking can be considered aggressive driving.', 'Forcing a driver to pull over to have a chat or get a phone number is friendly', 'Cutting off a driver is acceptable because it happens everyday.', 'Making obscene gestures is basically harmless.'], 'correct_answer' => 'Honking can be considered aggressive driving.'],
            ['question' => 'Stop signs are just suggestions when you:', 'options' => ['Meet up with other cars in an intersection.', 'Stop signs are not suggestions.', 'Roll forward to look around the wall.', 'At night when you know no one is driving around.'], 'correct_answer' => 'Stop signs are not suggestions.'],
            ['question' => 'Driver\'s failure to pay attention is responsible for an estimated:', 'options' => ['100,000 auto accidents', '300,000 auto accidents', '1.2 million auto accidents', '.2 million'], 'correct_answer' => '1.2 million auto accidents'],
            ['question' => 'When parking:', 'options' => ['Downhill: point your front tires towards the side of the road and roll forward to hit the curb.', 'Uphill: point your tires towards the middle of the road and roll back to hit the curb.', 'Anytime: your tires shouldn\'t be more than 18 inches from the curb.', 'All of the above', 'None of the above'], 'correct_answer' => 'All of the above'],
            ['question' => 'The following is not a road rage offense:', 'options' => ['Making obscene gestures', 'Blocking a vehicle that is trying to pass', 'Breaking suddenly to "punish" a tailgater.', 'Driving with BAC of .08%', 'Using a vehicle to intimidate another driver.'], 'correct_answer' => 'Driving with BAC of .08%'],
            ['question' => 'You should never pass a solid yellow line, with these few important exceptions:', 'options' => ['When you are turning left at an intersection', 'When you are turning into or out of a private road, or a driveway', 'When the right half of the road is closed, or blocked by an obstacle', 'Certain carpool lanes allow drivers to cross but you must enter and exit at designated places only', 'All of the above'], 'correct_answer' => 'All of the above'],
        ];

        foreach ($questions as $index => $question) {
            DB::table('questions')->insert([
                'course_id' => $course->id,
                'chapter_id' => $finalExamChapter->id,
                'question_text' => $question['question'],
                'options' => json_encode($question['options']),
                'correct_answer' => $question['correct_answer'],
                'order_index' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeded 50 Final Exam questions for Missouri Chapter 11');
    }
}

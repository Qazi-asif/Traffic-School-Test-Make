<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TexasDefensiveDrivingCompleteSeeder extends Seeder
{
    public function run()
    {
        // Create Texas course first
        $courseId = $this->createTexasCourse();

        // Add all chapters
        $this->addChapters($courseId);

        // Add quiz questions for each chapter
        $this->addQuizQuestions($courseId);

        // Add final exam questions
        $this->addFinalExamQuestions($courseId);

        $this->command->info('Texas Defensive Driving Course seeded successfully!');
    }

    private function createTexasCourse()
    {
        // Check if course already exists
        $existingCourse = DB::table('florida_courses')->where('title', 'LIKE', 'Texas%Ticket Dismissal%')->first();

        if ($existingCourse) {
            $this->command->info('Texas Ticket Dismissal course already exists, updating...');

            return $existingCourse->id;
        }

        // Create Ticket Dismissal Course
        $courseId = DB::table('florida_courses')->insertGetId([
            'title' => 'Texas Driving/Ticket Dismissal - 6 Hour Defensive Driving Course',
            'description' => 'Complete 6-hour Texas Defensive Driving Course approved by TDLR for ticket dismissal. License Number: CP007',
            'state_code' => 'TX',
            'course_type' => 'Ticket Dismissal',
            'duration' => 360,
            'price' => 28.00,
            'min_pass_score' => 70,
            'passing_score' => 70,
            'is_active' => true,
            'certificate_template' => 'CP007',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Create Insurance Discount Course
        $insuranceCourseId = DB::table('florida_courses')->insertGetId([
            'title' => 'Texas Insurance Discount - 6 Hour Defensive Driving Course',
            'description' => 'Complete 6-hour Texas Defensive Driving Course approved by TDLR for insurance discounts. License Number: CP007',
            'state_code' => 'TX',
            'course_type' => 'Insurance Discount',
            'duration' => 360,
            'price' => 28.00,
            'min_pass_score' => 70,
            'passing_score' => 70,
            'is_active' => true,
            'certificate_template' => 'CP007',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info("Created Texas Ticket Dismissal course with ID: {$courseId}");
        $this->command->info("Created Texas Insurance Discount course with ID: {$insuranceCourseId}");

        return $courseId;
    }

    private function addChapters($courseId)
    {
        $chapters = [
            [
                'title' => 'Course Introduction',
                'content' => $this->getChapter1Content(),
                'duration' => 30,
                'order_index' => 1,
            ],
            [
                'title' => 'The Traffic Safety Problem',
                'content' => $this->getChapter2Content(),
                'duration' => 45,
                'order_index' => 2,
            ],
            [
                'title' => 'Careless Driving and Its Consequences',
                'content' => $this->getChapter3Content(),
                'duration' => 40,
                'order_index' => 3,
            ],
            [
                'title' => 'Driving Under the Influence',
                'content' => $this->getChapter4Content(),
                'duration' => 60,
                'order_index' => 4,
            ],
            [
                'title' => 'Operator Responsibilities',
                'content' => $this->getChapter5Content(),
                'duration' => 50,
                'order_index' => 5,
            ],
            [
                'title' => 'The Pedestrian',
                'content' => $this->getChapter6Content(),
                'duration' => 35,
                'order_index' => 6,
            ],
            [
                'title' => 'Driving Maneuvers and Hazardous Conditions',
                'content' => $this->getChapter7Content(),
                'duration' => 45,
                'order_index' => 7,
            ],
            [
                'title' => 'Final Exam',
                'content' => 'Complete the final exam with 25 questions covering all course material. You must score 70% or higher to pass.',
                'duration' => 30,
                'order_index' => 8,
            ],
        ];

        foreach ($chapters as $chapter) {
            DB::table('chapters')->insert([
                'course_id' => $courseId,
                'title' => $chapter['title'],
                'content' => $chapter['content'],
                'duration' => $chapter['duration'],
                'order_index' => $chapter['order_index'],
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Added all chapters to Texas course');
    }

    private function getChapter1Content()
    {
        return '
# Course Introduction

## Course Objectives

### Reduce Traffic Collision Involvement
According to the World Health Organization (WHO), road traffic collisions cause over a million deaths and up to fifty million injuries each year worldwide. The impact extends beyond just the individuals involved. This course aims to reduce the risk of being involved in a collision, which can result in financial loss, injury, or death.

The term "traffic collisions" is used instead of "accidents" to emphasize that they are preventable and usually caused by driver or vehicular error. The philosophy of the Texas state agencies that oversee traffic and enforce the laws holds that driving "accidents" can be traced to the driver or vehicular error and are preventable.

### Reduce Traffic Law Violations
Completing this course will increase your familiarity with the legal code, helping you make informed choices and avoid driving offenses. Ignorance of the law is not an excuse in court. Drivers must know the Texas Vehicle Code, local regulations, and rules on traffic signals, right of way, pedestrians, turning, and speeding to drive without violating traffic laws.

### Responsibilities Associated with Operating a Vehicle
This course teaches responsible driving behavior, including monitoring actions and reactions, identifying consequences of poor habits, and establishing safe driving routines. Drivers are also reminded of the importance of ongoing vehicle maintenance and meeting state requirements for emissions testing, inspections, license renewal, and insurance.

## Course Requirements

### Attend the Entire Class
To get the most out of DummiesTrafficSchool.com, it\'s essential to attend the whole course and complete all quizzes and the final exam within the given time frame. The Completion Certificate will only be valid if the course is completed at least 24 hours before the court deadline and without assistance from anyone else.

### Pass the Final Exam
After completing the chapter readings and quizzes, a Final Exam of 25 questions covering all aspects of the course will be administered. The exam is an open book, and a minimum of 70% correct answers are required to pass. Most students pass on their first attempt, satisfying court requirements and improving their driving knowledge.

### Complete the TDLR Course Evaluation Form
Each student who takes and passes our course is given an opportunity to evaluate the course and the instructor on an official evaluation form. This information will be passed on to the Texas Department of Licensing and Regulations, which oversees the performance of all Driver Safety course providers in Texas.

## Important Notes
- Course is structured in chapters, following Texas Highway Safety and Motor Vehicles guidelines
- Takes about six hours to complete and includes reading material
- Timers are in place to keep you on track
- Each chapter ends with a brief quiz based on the content
- Provider License: CP007
- Student information must be kept for 3 years
';
    }

    private function getChapter2Content()
    {
        return '
# The Traffic Safety Problem

## Reasons for Traffic Laws

### Safety
The number one reason traffic laws have developed is to keep you, your passengers and everyone else on the road - including those on two feet and two wheels - safe. The public roads are meant for everyone\'s use and enjoyment, and so that commerce can be conducted without incident or interruption. Traffic laws are what facilitate this.

The difference between safe and orderly roads and a chaotic mess of honking horns and collisions on every other block is often in the laws that every driver adheres to as they drive. Certain traffic laws reflecting larger society changes have risen above normal regulations to have a definite impact on public safety – seat belt laws, child restraint laws, impaired driving laws, motorcycle helmet and speed laws are some examples.

### Common Understanding
Most everyone in life wants to find where he or she belongs. In many ways, driving on the roadways is no different. The reason that we have been able to adapt the driving system to higher speeds and vast changes in technology is simple – through individual driver education combined with ongoing public awareness campaigns, everyone basically comes to a common understanding of how the roadways are to be used.

This common understanding creates a sort of equilibrium every time we drive. For example, as you get out on the road today, you will know not go over the posted speed limits, and you will also know not to drive too slowly because blocking the flow of traffic can be as dangerous as well.

### Keeping Order and Movement in Traffic
When everyone cooperates, the traffic laws and physical road controls work together remarkably well to keep everyone moving in the right direction on a daily basis. Unless a uniformed officer is actively directing the flow of traffic, drivers and pedestrians must follow the "rules of the road" instinctively to keep the roads orderly.

## Changes in Driving

### Traffic Law Driven Changes
Like any body of laws, the Texas Vehicle Code is a living system which grows and evolves with time. Every year there are new traffic laws on the books which drivers must become familiar with. Make sure you are up to date!

### Technology Driven Changes
When a driver enters a vehicle, the car interior becomes his interface to the world. Car interiors today would be almost unrecognizable to the average driver 50 years ago – more Star Trek than Herbie the Lovebug. GPS systems, video screens, digital and LED dashboard displays, voice activated interfaces and automatic seatbelts all reflect changes in technology that impact the everyday driver.

### Changes in Driving Techniques
Changes in technology ultimately result in changes to driving techniques. Today\'s highways and fast-paced traffic demand a thorough knowledge of and use of driving and accident prevention techniques – the newest forms of Defensive Driving. Drivers must be familiar with how to safely negotiate HOV lanes, highway entrance meters, toll booths, engineered exit ramps and cloverleaf interchanges.

The Space Cushion, the 3 Count System, the No Zone, constant scanning, advanced mirror positioning, and collision avoidance techniques have been developed to cope with today\'s headlong rush into daily traffic on highways and expressways designed to move millions of vehicles in a day.
';
    }

    private function getChapter3Content()
    {
        return '
# Careless Driving and Its Consequences

The first thing one must recognize as a student of traffic school is why they are here: to become more educated on the dangers of poor driving and what harm it may cause. Preventing collisions starts with the driver (that is, you) and they must recognize the reasons why driving with care and discipline is vital.

## Damage to or Loss of Vehicle

Modern civilization has come to depend upon the motorized vehicle, and driving is a vital part of many people\'s lives. Chances are that you rely on a vehicle for transportation or work, and that makes it a very valuable object to your livelihood. Responsible driving is the first thing you must do to ensure that object is well taken care of.

The last thing any driver wants is dinged-up car, or worse, one that is rendered completely non-functional because of a collision that could have been avoided. Everything from the ability to easily get to work, buy and transport groceries, see friends and family, or having the freedom to go wherever, whenever you please, will suddenly be taken away because of a foolish mistake made on the road.

## Injury or Death to You, Passengers and Others

Beyond simple damages to your car, the most dire consequence of vehicular incidents is bodily harm, and it is also the biggest thing traffic laws and driving courses attempt to prevent. Avoiding the physical and emotional suffering caused by crippling injuries and the loss of life sustained in traffic collisions should also be the biggest concern for each and every driver out on the road.

It is something no one likes to contemplate, but every driver MUST realize that they are in control of a potentially death-causing device when they are behind the wheel. There is a reason why Texas law considers motor vehicles as deadly weapons when they are handled improperly. Consider it your duty to protect both yourself and others by driving cautiously and in accordance with traffic safety law: it exists to keep you safe.

## Possible Auto Insurance Increases

All automobiles must have a guarantee of financial responsibility should it be involved in a collision with another vehicle. This guarantee comes in the form of auto insurance, which makes payments to various parties depending on the circumstances of the incident, instead of the drivers having to work everything out and pay out of their own pockets.

While handy, it is no secret that insurance companies increase their coverage charges based upon how "high risk" a customer is. The way they judge this is by looking at your driving record, both for incidents and traffic violations. Even minor infractions can end up costing a driver a considerable sum when considered with this factor, acting over time.

## Financial Impact/Legal Actions/DMV Actions

Nobody likes to get a traffic ticket. Nobody likes to be involved in a car crash either. A huge reason for this, many would agree, is the cost. Paying a traffic ticket can range from a nuisance to a serious financial concern, while repairing or outright replacing a car is an even more serious problem.

Beyond the immediate costs such as these, even more burdens may be placed upon your shoulders as the result of poor driving; other drivers may sue for damages, a collection of overdue fines may result in prison time, or the DMV may suspend your license under certain circumstances.

## Impact on Quality of Life

All of these costs and unpleasantries can end up having a sizeable, even crippling and lasting impact on your life overall. It is not unheard of for one bad crash to disrupt everything from one\'s health and financial stability, to reputation, personal relationships and more. In fact, if you are found to have been criminally negligent in a collision, you face the possibility of losing your freedom outright, with prison wardens answering all questions relating to your "quality of life".
';
    }

    private function getChapter4Content()
    {
        return '
# Driving Under the Influence

## Drunk Driving Is Real

**Key Statistics:**
- Alcohol-related car crashes kill someone every 45 minutes and injure someone every two minutes
- In the United States, drunk driving is one of the leading criminal causes of death
- In 2020, 11,654 people died in alcohol-impaired driving crashes, accounting for nearly one-third (30%) of all traffic-related deaths in the United States
- In 2023, 1,138 people died in alcohol-related crashes in Texas
- Alcohol-related fatalities comprised 42 percent of all highway fatalities in Texas, which is the second highest percentage in the nation after Montana

## Effects of Alcohol on Driving

Even small amounts of alcohol will affect your driving. After only one drink:
- You may not be able to judge speed and distance accurately
- Your ability to respond and react may be affected
- Your judgment will be affected, leading you to make riskier choices
- Your physical responses such as vision, coordination, attention span and memory will all be negatively affected

Alcohol can begin to impair your reaction time at as low a BAC as .01%. There is no other way to look at it, alcohol is poisonous to your body, and it affects you in unhealthy ways.

## Blood Alcohol Content (BAC)

To see if someone is under the influence of a drug, police have to give tests. One way to measure "driver impairment" is by measuring a person\'s blood alcohol content (BAC).

### BAC Chart:
- **.01 - .05**: Sub clinical - By ordinary observation, behavior appears normal
- **.03 - .12**: Euphoria - Stimulant stage. Increases talkativeness, decreases judgment, attention and control
- **.09 - .25**: Excitement - Emotional instability, loss of judgment, memory, understanding; impaired balance, drowsiness
- **.18 - .30**: Confusion - Dizziness, staggering, slurred speech, disorientation, mental confusion and apathy
- **.25 - .40**: Coma - Unconsciousness. Impairment to circulation and respiration
- **.45+**: Death - Breathing ceases

## Alcohol Absorption

Unlike food, alcohol is not digested, instead it is absorbed. It goes directly into the blood through the stomach and its effects are quick and sudden. The speed in which alcohol can impair you is affected by:

### Time
The body can metabolize about one ounce of alcohol per hour. This rate may be affected by other body content or activity.

### Body Weight
Body mass and blood volume is another factor regarding the quantity of alcohol that can be tolerated. The smaller the body mass, the less alcohol it will take for the individual to become inebriated.

### Stomach Content
The contents of the stomach will not prevent the absorption of alcohol; it will only delay the absorption.

## The Designated Driver Program

The national Designated Driver Program is an anti-DUI effort that works. To be a designated driver for your friends, an individual:
- Must possess a valid driver license
- Must be part of a group of two or more persons
- Must verbally identify himself or herself as the designated driver to the server
- Must abstain from consuming alcoholic beverages for the duration of the outing
- Must not be an otherwise impaired driver

## The Implied Consent Law (TTC 724.011)

The DMV reminds drivers that a driver\'s license or a commercial driver\'s license is not a basic right guaranteed by the Constitution. It is a privilege that is issued and may be withdrawn at the discretion of the State.

The implied consent law means basically that when you agree to take on the responsibility of driving, you are in effect "implying" that you will give consent to the state to test your BAC upon the request of an officer.

## Zero Tolerance Law

The State has a ZERO TOLERANCE attitude toward drunk drivers of any age, but the law is stricter for drivers under 21 years of age. If you are under 21, one drink puts you "over the limit" for driving.

### Penalties for Minors:
- **First Offense DUI by a Minor (17+ but less than 21)**: Class C misdemeanor, fine up to $500, 20-40 hours community service, license suspended for 120 days
- **Second Offense**: Fine up to $500, 40-60 hours community service
- **Third Offense**: Class B misdemeanor, fine $500-$2,000, 40-60 hours community service, possible jail time up to 180 days

## Know the Punishments

Even on a first offense, a DUI can be a disaster. You will:
- Lose your license for some period of time
- Spend some time – at least overnight - in jail
- Be required to pay thousands of dollars for legal defense, court costs and other fees
- Pay surcharges: $1000 a year for 3 years for first DWI, $1500 for second, $2000 if BAC is 0.16 or more
- Raise your vehicle insurance rates through the roof – if your policy isn\'t cancelled
- Have a criminal conviction on your record

## How to Spot a Drunk Driver

If you see these behaviors, stay away and report to 911:
- **Lane Drifting**: Vehicle drifting from one side of a lane to the other
- **Speed and Braking Variations**: Repeated braking for no apparent reason
- **Erratic Behavior**: Unpredictable and unreasonable driving maneuvers
';
    }

    private function getChapter5Content()
    {
        return '
# Operator Responsibilities

In the United States, legal precedent has formed the opinion that driving is a privilege that is granted by the government and can be withdrawn by the government. While many people view driving as both a necessity and a right, it is important to remember that operating a motor vehicle is a privilege which, should you abuse it, may be taken away.

## License Requirements and Responsibilities

By obtaining a driver\'s license, you agree to:
- Respect your fellow driver by being courteous
- Honor all DMV rules and Texas Vehicle Code driving laws
- Obey the indicated speed limits, traffic signs, and uniformed personnel
- Assume your financial responsibility in case of collision
- Engage in a blood and/or breath test if requested by a police officer

## Obtaining a License in Texas

### New Residents
Any person other than a nonresident student, a tourist, or a nonresident member of the Armed Forces who has lived in this state for 30 consecutive days is considered a resident for the purpose of driver licensing.

### Steps to Obtaining a Driver\'s License:
1. Complete an application and show proper identification papers
2. Pass a written exam testing your knowledge of the rules of the road and traffic signs
3. Pass a driving test - demonstration of your ability to apply the rules of the road
4. Pass a vision test
5. Have your photo taken
6. Provide a thumbprint (required by Texas law)

### Required Identification Documents:
- Social Security card or acceptable proof of Social Security number
- Primary identification (valid Texas driver license, US Passport, etc.)
- Secondary identification (birth certificate, court order, etc.)

## Classes of Licenses

### Class A
Permits driving any vehicle or combination of vehicles with a gross combination weight rating of 26,001 pounds or more. Minimum age: 18, or 17 with completion of approved driver ed course.

### Class B
Permits driving single unit vehicle with gross vehicle weight rating of 26,001 pounds or more, buses with 24+ passenger capacity. Minimum age: 18, or 17 with completion of approved driver education course.

### Class C
Permits driving single unit vehicle or combination of vehicles that is not Class A or B. Minimum age: 18, 16 with completion of approved course, or 15 with approval of minor\'s hardship application.

### Class M
Permits driving a motorcycle or moped. Minimum age varies by vehicle type.

## Commercial Driver Licenses (CDL)

Drivers of commercial vehicles require special skills. CDL classes include:
- **Class A CDL**: Combination vehicles 26,001+ pounds
- **Class B CDL**: Single unit vehicles 26,001+ pounds, buses 24+ passengers
- **Class C CDL**: Vehicles designed to transport 16-23 passengers or hazardous materials

### CDL Endorsements:
- **H**: Hazardous materials transportation
- **N**: Tank vehicle operation
- **P**: Passenger vehicle operation
- **S**: School bus operation
- **T**: Towing multiple trailers
- **X**: Combination of H and N

## License Suspension and Revocation

### Revocation
Your driving privilege is terminated, and you will have to apply for a new license after a specified period. Reasons include:
- Multiple cases of reckless driving within one year
- Felony in which a motor vehicle is used
- Hit and Run
- Lying about vehicle ownership or operation
- Drug possession felony
- Medical condition causing blackouts or seizures

### Suspension
Your driving privileges are temporarily withdrawn. Automatic suspensions include:
- Failure to stop for school bus (2nd offense)
- Unpaid traffic fines in another state
- Failure to pay liability judgment
- Lending your license to another person
- Using cancelled, revoked or altered license

## The Points System

Texas has developed a violation point system to identify problem drivers:
- **Two points**: Moving violation conviction
- **Three points**: Moving violation conviction that resulted in a crash

### Surcharges:
- $100 surcharge for first six points, $25 for each additional point
- Annual surcharges for specific offenses:
  - Failure to maintain insurance: $250/year
  - Driving while license suspended: $250/year
  - DWI-related offense: $1000/year (first), $1500/year (second)

## Drowsy Driving

Exhausted drivers cause approximately 200,000 collisions per year. To reduce risk:
- Avoid driving between midnight and 6 am if you normally sleep during those hours
- Never drive when sleep deprived
- Don\'t plan trips requiring driving on little sleep
- Never drive after taking sedating medications

**Fast Fact**: After 17-19 hours of sleep deprivation, you will drive about as well as someone with a BAC of 0.05.

## Emotional Driving and Other Responsibilities

Your responsibility as a driver is to keep yourself and those around your vehicle safe. If you are too stressed, angry, sad, or even happy and excited, do not get behind the wheel!

### Other Important Responsibilities:
- **Poor Vision**: If you require sight correction, ensure you have what you need to see correctly
- **Unfavorable Driving Conditions**: Refrain from driving in dangerous weather conditions
- **Vehicle Condition**: Ensure engine, horn, brakes, and all lights are in proper working order
- **Pets and Animals**: Secure animals properly when transporting
';
    }

    private function getChapter6Content()
    {
        return '
# The Pedestrian

**Fast Fact**: Pedestrian deaths account for eighteen percent of total motor vehicle deaths. - National Highway Traffic Safety Administration (2023)

With a statistic like that, it should be clear that drivers AND pedestrians need to be aware of their surroundings at all times. At some point in the day, every driver leaves his car behind and becomes a pedestrian, even if only for a short while.

## Driver Responsibilities to Pedestrians

Drivers MUST stop for pedestrians that are crossing at marked crosswalks, or crossing from corner to corner in an intersection – an unmarked crosswalk. Any intersection with roads meeting approximately at right angles and with sidewalks means that a crosswalk is there, even if no white lines are drawn.

### Key Requirements:
- Stop before the thick white limit line that defines a crosswalk
- It is illegal to stop within a crosswalk and block pedestrian traffic
- Always stop for a person carrying a white cane (with or without a red tip) or being led by a seeing-eye dog
- Never call out to a seeing-eye dog, feed it or distract it in any way

## Texas Transportation Code Requirements

The Texas Transportation Code states: "…the operator of a vehicle shall…exercise due care to avoid colliding with a pedestrian on a roadway." (TTC 552.008)

In legal terms "due care" means: "Conduct that a reasonable man or woman will exercise in a particular situation, in looking out for the safety of others."

### Driver Must:
- Give warning by sounding the horn if necessary
- Exercise proper precaution on observing a child or obviously confused person on the roadway
- Even if a pedestrian is jaywalking, you still must brake for them and not hit them

## Pedestrian Responsibilities

When you become a pedestrian, you have responsibilities too:

### Must Do:
- Obey all traffic signs and signals, pavement markings and official traffic personnel
- Always check traffic in both directions before crossing any street
- Cross the street only at intersections and marked crosswalks when provided
- Walk on sidewalk if available
- If no sidewalk, walk on left side of road facing traffic
- Yield right-of-way to vehicles when crossing outside of crosswalks

### Must Not Do:
- Walk in the road (except as specified above)
- Solicit rides or business from vehicles
- Walk on railroad tracks, certain toll bridges, tunnels
- Suddenly leave curb and proceed into crosswalk in path of close vehicle

## Areas Requiring Extra Caution

Always be ready to yield to pedestrians in these areas:
- Shopping mall parking lots
- Construction areas
- Popular and/or populated areas
- Any place where children gather (schools and parks)
- Senior centers
- Highway scenic overlooks, picnic areas and turnouts

## Visually Handicapped Pedestrians

A totally or partially blind pedestrian who is carrying a predominantly white cane (with or without a red tip), or using a guide dog, has the right-of-way AT ALL TIMES. You may be cited, fined and imprisoned should you fail to yield and cause a collision with such a person.

No one, other than those totally or partially blind, can carry or use a white cane (with or without a red tip).

## Crosswalks

At all crosswalks, whether they are marked or unmarked, people walking have the right of way. Therefore, as a driver, you must always stop for pedestrians crossing.

### Parking Restrictions:
You may not park:
- On a bridge
- On a sidewalk
- On a bicycle path
- On a crosswalk
- Within an intersection
- In front of a private driveway
- Within 15 feet of a fire hydrant
- Within 20 feet of a crosswalk or intersection
- Within 50 feet of a railroad crossing
- Within 30 feet of an approach to a flashing signal

### Fire Station Requirements:
- Do not park within 20 feet of the driveway entrance to a fire station
- Do not park on the side of a street opposite the entrance to a fire station within 75 feet of the entrance

## Pedestrian Signals

Pedestrian traffic is controlled by special signals posted near traffic lights:
- **"WALK" or Walking Person Symbol**: Legal to start crossing
- **"DON\'T WALK" or Raised Hand**: NOT legal to start crossing
- **Flashing Signal**: Don\'t start crossing but may finish crossing
- **Steady Raised Hand**: Vehicle traffic about to receive green signal

If there are no pedestrian signals, pedestrians must obey the red, yellow, and green signal lights for vehicles.
';
    }

    private function getChapter7Content()
    {
        return '
# Driving Maneuvers and Hazardous Conditions

During any commute, you will need to perform a variety of common maneuvers and handle hazardous conditions. These skills are vital to safe and efficient driving.

## Hazardous Conditions

"Knowing Your Vehicle" means knowing how it is likely to respond on the road when the environment or road conditions are not optimal. The basic rule is: **slow down as conditions take a turn for the worse**.

In any bad weather condition such as rain, fog, ice, wind, and dust you should always slow your speed to meet the condition. No one is exempt - these conditions affect ALL types of vehicles including front wheel drive, sport utility, 4-wheel drive, and all-wheel drive vehicles.

## Tire Condition

The only contact your vehicle has with the road is through the tires. The quality of the grip depends on:
- Type and condition of the tires
- Type and condition of the road surface

Many drivers do not pay enough attention to their tires. It is important that tires:
- Be of good quality to begin with
- Be in good condition
- Be rotated on schedule
- Have enough air in them

## Specific Hazardous Conditions

### Curves
Your vehicle travels much faster in a straight line than it can in a curve. Always slow down before you enter the curve, so you do not have to brake in the curve. Braking in a curve can cause your vehicle to skid.

### Water on the Roadway
When the road is wet, most tires have good traction up to about 35 mph. As you go faster, your tires will start to ride up on the surface of the water like water skis. This is called **"hydroplaning."**

A vehicle may hydroplane at speeds as low as 50 mph in water one-eighth of an inch deep. When your tires leave the surface of the roadway, you have little or no control of your vehicle.

**Prevention**: On a wet road, reduce your speed by about 10 mph. Follow the Basic Speed Law - drive no faster than is safe for the conditions.

### Slippery Roads
Even a small amount of moisture can cause hazardous conditions. When roads get wet, especially after a long dry spell, they can become very slippery when water mixes with oil, grease, and exhaust particles on the roadway.

If your tires are losing traction:
- Ease your foot off the gas pedal
- Keep the steering wheel straight
- Only try to turn if it\'s an emergency
- Do not try to stop or turn until your tires are gripping the road again

### Fog
The best suggestion for dealing with fog is to avoid driving in it altogether. Driving through fog has proven to be extremely dangerous, even at very slow speeds.

**If you must drive in fog:**
- Have your headlights and fog lights on (use LOW beams, not high beams)
- Use your defroster and windshield wipers
- Turn off radio and heater when making turns to listen for other cars
- If you need to pull off the road, pull completely off and turn off your lights

**Fog Light Rules:**
- No more than two fog lamps per vehicle
- May be used with but not in place of headlamps
- Must be front-mounted between 12-30 inches high
- Should not blind other drivers

### Snow and Ice
Streets and highways covered with snow, snowpack or ice are extremely hazardous. They are most hazardous when the snow or ice begins to melt.

**Precautions for snow and ice:**
- Make sure your tires have good tread
- Ensure brakes are in good condition and properly adjusted
- Keep windows clear with defrosters and wipers
- Be alert for snowplows and sanding trucks
- Maintain extra-large space between you and the car ahead
- Start gradually using low gear and gentle acceleration
- On packed snow, cut your speed in half
- On ice, slow to a crawl

**Ice Conditions to Watch:**
- Shady spots freeze first and dry out last
- Overpasses and bridges can be icy when other pavement is not
- When temperature is around freezing, ice becomes wet and more slippery

## Communication and Courteous Driving

### Communication Methods:
- Turn indicators (signal intentions clearly)
- Horn (warn others when collision might be imminent)
- Facial expressions and hand gestures
- Courteous driving behavior

### Courteous Driving Practices:
- Allow other drivers to merge
- Give others plenty of space
- Drive slower in congested areas, residential areas, around schools and parks
- Make friendly eye contact and smile
- Signal intentions clearly
- Be patient with others
- Use horn only when necessary to avoid collisions
- Wave and indicate thanks when warranted

## Basic Driving Maneuvers

### Backing Up
- Check all mirrors and blind spots
- Look over your shoulder in the direction you\'re backing
- Back slowly and be prepared to stop
- Use a spotter when available

### Parking
- Parallel parking requires practice and patience
- Allow adequate space for other vehicles
- Check local parking regulations and restrictions
- Never block driveways, crosswalks, or fire hydrants

### Lane Changes
- Check mirrors and blind spots
- Signal your intention
- Make sure there\'s adequate space
- Change lanes gradually, not abruptly
- Cancel your signal after completing the maneuver

### Turning
- Signal well in advance
- Position your vehicle properly
- Yield to oncoming traffic when turning left
- Complete turns into the proper lane
- Watch for pedestrians and cyclists

Remember: The key to safe driving is being predictable, courteous, and always aware of your surroundings and the conditions around you.
';
    }

    private function addQuizQuestions($courseId)
    {
        $chapters = DB::table('chapters')->where('course_id', $courseId)->get();

        foreach ($chapters as $chapter) {
            if ($chapter->title === 'Final Exam') {
                continue;
            } // Skip final exam chapter

            $questions = $this->getQuizQuestionsForChapter($chapter->order_index);

            foreach ($questions as $index => $question) {
                $options = array_values(array_filter([
                    $question['options']['A'],
                    $question['options']['B'],
                    $question['options']['C'],
                    $question['options']['D'],
                    $question['options']['E'] ?? null,
                ]));

                // Convert letter to actual answer text
                $correctIndex = ord($question['correct']) - ord('A');
                $correctAnswer = $options[$correctIndex];

                DB::table('questions')->insert([
                    'course_id' => $courseId,
                    'chapter_id' => $chapter->id,
                    'question_text' => $question['question'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($options),
                    'correct_answer' => $correctAnswer,
                    'explanation' => $question['explanation'] ?? '',
                    'points' => 1,
                    'order_index' => $index + 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('Added quiz questions for all chapters');
    }

    private function getQuizQuestionsForChapter($chapterNumber)
    {
        switch ($chapterNumber) {
            case 1: // Course Introduction
                return [
                    [
                        'question' => 'Which of the following is an example of a kind of change traffic laws must respond to?',
                        'options' => [
                            'A' => 'Changes in car manufacturing methods',
                            'B' => 'Changes in climate',
                            'C' => 'Changes in taxes',
                            'D' => 'Changes in technology',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'D',
                        'explanation' => 'Traffic laws must adapt to technological changes that affect driving.',
                    ],
                    [
                        'question' => 'What is the primary reason traffic laws exist?',
                        'options' => [
                            'A' => 'Collecting citation fees',
                            'B' => 'Punishing motorists',
                            'C' => 'Maintaining social order',
                            'D' => 'Preventing cost to cities',
                            'E' => 'Ensuring driver safety',
                        ],
                        'correct' => 'E',
                        'explanation' => 'The primary purpose of traffic laws is to ensure the safety of all road users.',
                    ],
                    [
                        'question' => 'Traffic laws help to establish a sense of ________ so that all drivers can expect predictable driving behavior from each other.',
                        'options' => [
                            'A' => 'competition',
                            'B' => 'pleasantness',
                            'C' => 'suspicion',
                            'D' => 'common understanding',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'D',
                        'explanation' => 'Traffic laws create common understanding among drivers about expected behaviors.',
                    ],
                ];

            case 2: // Traffic Safety Problem
                return [
                    [
                        'question' => 'What is an example of a driving technique one might need to learn to safely use the roads?',
                        'options' => [
                            'A' => 'Scanning',
                            'B' => 'Avoiding no-zones',
                            'C' => '3-second system',
                            'D' => 'Signaling',
                            'E' => 'All of the above',
                        ],
                        'correct' => 'E',
                        'explanation' => 'All listed techniques are important for safe driving.',
                    ],
                    [
                        'question' => 'The Space Cushion and 3 Count System are examples of:',
                        'options' => [
                            'A' => 'Old driving techniques',
                            'B' => 'Modern defensive driving techniques',
                            'C' => 'Traffic law violations',
                            'D' => 'Vehicle maintenance procedures',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'B',
                        'explanation' => 'These are modern defensive driving techniques developed for today\'s traffic conditions.',
                    ],
                ];

            case 3: // Careless Driving
                return [
                    [
                        'question' => 'Which is NOT a consequence of careless driving mentioned in this chapter?',
                        'options' => [
                            'A' => 'Vehicle damage or loss',
                            'B' => 'Injury or death',
                            'C' => 'Insurance increases',
                            'D' => 'Improved driving skills',
                            'E' => 'Legal actions',
                        ],
                        'correct' => 'D',
                        'explanation' => 'Careless driving does not improve driving skills; it leads to negative consequences.',
                    ],
                    [
                        'question' => 'Texas law considers motor vehicles as ________ when handled improperly.',
                        'options' => [
                            'A' => 'toys',
                            'B' => 'deadly weapons',
                            'C' => 'transportation tools',
                            'D' => 'luxury items',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Texas law recognizes that improperly operated vehicles can be deadly weapons.',
                    ],
                ];

            case 4: // DUI
                return [
                    [
                        'question' => 'Mixing various drugs with alcohol will usually ____ the effects of both.',
                        'options' => [
                            'A' => 'hinder',
                            'B' => 'neutralize',
                            'C' => 'magnify',
                            'D' => 'stop',
                            'E' => 'none of the above',
                        ],
                        'correct' => 'C',
                        'explanation' => 'Mixing drugs with alcohol typically magnifies the effects of both substances.',
                    ],
                    [
                        'question' => 'Just one drink can impair your _____.',
                        'options' => [
                            'A' => 'judgment',
                            'B' => 'response time',
                            'C' => 'vision',
                            'D' => 'coordination',
                            'E' => 'all of the above',
                        ],
                        'correct' => 'E',
                        'explanation' => 'Even one drink can impair all aspects of driving ability.',
                    ],
                    [
                        'question' => 'In general it is illegal for any driver under age 21 to _____.',
                        'options' => [
                            'A' => 'transport alcohol',
                            'B' => 'consume alcohol',
                            'C' => 'possess alcohol',
                            'D' => 'drive with a BAC of .01% or higher',
                            'E' => 'all of the above',
                        ],
                        'correct' => 'E',
                        'explanation' => 'Texas has zero tolerance laws for drivers under 21.',
                    ],
                    [
                        'question' => 'Lane drifting, erratic behavior and speeding up and slowing down help identify _____.',
                        'options' => [
                            'A' => 'a person evading police',
                            'B' => 'a drowsy driver',
                            'C' => 'a drunk at a bar',
                            'D' => 'a drunk on the road',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'D',
                        'explanation' => 'These are classic signs of an impaired driver on the road.',
                    ],
                ];

            case 5: // Operator Responsibilities
                return [
                    [
                        'question' => 'When you feel fatigue set in as you are driving, it is best NOT to:',
                        'options' => [
                            'A' => 'Grab a cup of coffee',
                            'B' => 'Continue driving',
                            'C' => 'Pull over in a safe area and take a nap',
                            'D' => 'Switch drivers, if driving with a passenger',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Continuing to drive while fatigued is dangerous and should be avoided.',
                    ],
                    [
                        'question' => 'When points on your driving record add up, it means _____.',
                        'options' => [
                            'A' => 'a state of grace',
                            'B' => 'a losing score',
                            'C' => 'you win',
                            'D' => 'a visit to the local jail',
                            'E' => 'you earn a bonus',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Points on your driving record are bad - they represent a losing score.',
                    ],
                    [
                        'question' => 'A Class ____ license allows operation of a standard passenger vehicle.',
                        'options' => [
                            'A' => 'A',
                            'B' => 'B',
                            'C' => 'C',
                            'D' => 'D',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'C',
                        'explanation' => 'A Class C license is for standard passenger vehicles.',
                    ],
                    [
                        'question' => 'A Texas Driver\'s License is usually valid for ____ years.',
                        'options' => [
                            'A' => '4',
                            'B' => '5',
                            'C' => '6',
                            'D' => '8',
                            'E' => '10',
                        ],
                        'correct' => 'C',
                        'explanation' => 'Texas driver\'s licenses are typically valid for 6 years.',
                    ],
                ];

            case 6: // Pedestrians
                return [
                    [
                        'question' => 'When crossing a road as a pedestrian, you should:',
                        'options' => [
                            'A' => 'Cross at any point in the road',
                            'B' => 'Cross at designated crosswalks',
                            'C' => 'Hold up traffic as you walk across',
                            'D' => 'Expect all traffic to stop for you at all times',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Pedestrians should cross at designated crosswalks for safety.',
                    ],
                    [
                        'question' => 'Blind pedestrians can usually be recognized by:',
                        'options' => [
                            'A' => 'Walking slowly',
                            'B' => 'A white cane and seeing-eye dog',
                            'C' => 'Dark glasses',
                            'D' => 'Walking quickly',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'B',
                        'explanation' => 'White canes and seeing-eye dogs are the primary identifiers of blind pedestrians.',
                    ],
                    [
                        'question' => 'A driver must stop for a pedestrian in the road:',
                        'options' => [
                            'A' => 'At all times',
                            'B' => 'Only when they are crossing at a crosswalk',
                            'C' => 'Only when they are crossing legally',
                            'D' => 'Only when it is convenient',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'A',
                        'explanation' => 'Drivers must exercise due care and stop for pedestrians at all times.',
                    ],
                    [
                        'question' => 'The signal indicating it is safe to cross a street is:',
                        'options' => [
                            'A' => 'A red walking person',
                            'B' => 'A red raised hand',
                            'C' => 'A white raised hand',
                            'D' => 'A white walking person',
                            'E' => 'None of the above',
                        ],
                        'correct' => 'D',
                        'explanation' => 'A white walking person symbol indicates it is safe for pedestrians to cross.',
                    ],
                ];

            case 7: // Driving Maneuvers
                return [
                    [
                        'question' => 'When your car tires ride up on the surface of the water like skis, this is referred to as______.',
                        'options' => [
                            'A' => 'Hydroplaning',
                            'B' => 'Skipping',
                            'C' => 'Vortex',
                            'D' => 'Motion',
                            'E' => 'Slipping',
                        ],
                        'correct' => 'A',
                        'explanation' => 'Hydroplaning occurs when tires lose contact with the road surface due to water.',
                    ],
                    [
                        'question' => 'In fog, you should use:',
                        'options' => [
                            'A' => 'High beam headlights',
                            'B' => 'Low beam headlights',
                            'C' => 'No headlights',
                            'D' => 'Hazard lights only',
                            'E' => 'Parking lights only',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Low beam headlights should be used in fog to avoid glare reflection.',
                    ],
                    [
                        'question' => 'On a wet road, you should reduce your speed by about:',
                        'options' => [
                            'A' => '5 mph',
                            'B' => '10 mph',
                            'C' => '15 mph',
                            'D' => '20 mph',
                            'E' => '25 mph',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Reducing speed by about 10 mph on wet roads helps prevent hydroplaning.',
                    ],
                ];

            default:
                return [];
        }
    }

    private function addFinalExamQuestions($courseId)
    {
        $finalExamChapter = DB::table('chapters')
            ->where('course_id', $courseId)
            ->where('title', 'Final Exam')
            ->first();

        if (! $finalExamChapter) {
            $this->command->error('Final Exam chapter not found');

            return;
        }

        $finalExamQuestions = [
            [
                'question' => 'The primary reason traffic laws exist is to:',
                'options' => [
                    'A' => 'Generate revenue for the state',
                    'B' => 'Ensure driver safety',
                    'C' => 'Control traffic flow',
                    'D' => 'Punish bad drivers',
                    'E' => 'None of the above',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Which BAC level is considered legally intoxicated in Texas for drivers 21 and over?',
                'options' => [
                    'A' => '0.05%',
                    'B' => '0.06%',
                    'C' => '0.08%',
                    'D' => '0.10%',
                    'E' => '0.12%',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'For drivers under 21, the legal BAC limit in Texas is:',
                'options' => [
                    'A' => '0.02%',
                    'B' => '0.04%',
                    'C' => '0.08%',
                    'D' => 'Any detectable amount',
                    'E' => 'Same as adults',
                ],
                'correct' => 'D',
            ],
            [
                'question' => 'Hydroplaning occurs when:',
                'options' => [
                    'A' => 'Tires are overinflated',
                    'B' => 'Tires ride up on water surface',
                    'C' => 'Brakes are applied too hard',
                    'D' => 'Vehicle speed is too slow',
                    'E' => 'Road is completely dry',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'In Texas, a regular driver\'s license is valid for:',
                'options' => [
                    'A' => '4 years',
                    'B' => '5 years',
                    'C' => '6 years',
                    'D' => '8 years',
                    'E' => '10 years',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'When approaching a pedestrian with a white cane, you should:',
                'options' => [
                    'A' => 'Honk to alert them',
                    'B' => 'Speed up to pass quickly',
                    'C' => 'Stop and yield right of way',
                    'D' => 'Flash your lights',
                    'E' => 'Maintain normal speed',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'The "3 Count System" refers to:',
                'options' => [
                    'A' => 'Counting to three before starting',
                    'B' => 'Following distance technique',
                    'C' => 'Number of mirrors to check',
                    'D' => 'Gear shifting pattern',
                    'E' => 'Turn signal timing',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Points on your Texas driving record remain for:',
                'options' => [
                    'A' => '1 year',
                    'B' => '2 years',
                    'C' => '3 years',
                    'D' => '5 years',
                    'E' => 'Permanently',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'The minimum passing score for this course is:',
                'options' => [
                    'A' => '60%',
                    'B' => '65%',
                    'C' => '70%',
                    'D' => '75%',
                    'E' => '80%',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'In fog, you should use _______ headlights.',
                'options' => [
                    'A' => 'high beam',
                    'B' => 'low beam',
                    'C' => 'no',
                    'D' => 'flashing',
                    'E' => 'parking',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Drowsy driving impairs your abilities similar to:',
                'options' => [
                    'A' => 'Texting while driving',
                    'B' => 'Drunk driving',
                    'C' => 'Speeding',
                    'D' => 'Aggressive driving',
                    'E' => 'None of the above',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'A Class C license allows you to drive:',
                'options' => [
                    'A' => 'Commercial vehicles',
                    'B' => 'Motorcycles only',
                    'C' => 'Standard passenger vehicles',
                    'D' => 'Buses',
                    'E' => 'Trucks over 26,000 lbs',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'The Implied Consent Law means:',
                'options' => [
                    'A' => 'You consent to follow traffic laws',
                    'B' => 'You consent to BAC testing when requested',
                    'C' => 'You consent to vehicle searches',
                    'D' => 'You consent to pay fines',
                    'E' => 'You consent to license suspension',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Pedestrians have the right of way:',
                'options' => [
                    'A' => 'Only in crosswalks',
                    'B' => 'Only when signals permit',
                    'C' => 'At all times',
                    'D' => 'Only during daylight',
                    'E' => 'Never',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'On wet roads, reduce speed by approximately:',
                'options' => [
                    'A' => '5 mph',
                    'B' => '10 mph',
                    'C' => '15 mph',
                    'D' => '20 mph',
                    'E' => '25 mph',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'The Space Cushion technique helps with:',
                'options' => [
                    'A' => 'Parking',
                    'B' => 'Following distance',
                    'C' => 'Lane changing',
                    'D' => 'Turning',
                    'E' => 'Backing up',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Texas requires student information to be kept for:',
                'options' => [
                    'A' => '1 year',
                    'B' => '2 years',
                    'C' => '3 years',
                    'D' => '5 years',
                    'E' => '7 years',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'DummiesTrafficSchool.com\'s Texas provider license number is:',
                'options' => [
                    'A' => 'CP005',
                    'B' => 'CP006',
                    'C' => 'CP007',
                    'D' => 'CP008',
                    'E' => 'CP009',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'Alcohol begins to impair reaction time at a BAC as low as:',
                'options' => [
                    'A' => '0.01%',
                    'B' => '0.02%',
                    'C' => '0.05%',
                    'D' => '0.08%',
                    'E' => '0.10%',
                ],
                'correct' => 'A',
            ],
            [
                'question' => 'The body can metabolize approximately _____ of alcohol per hour.',
                'options' => [
                    'A' => 'half an ounce',
                    'B' => 'one ounce',
                    'C' => 'one and a half ounces',
                    'D' => 'two ounces',
                    'E' => 'three ounces',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Driving is considered a _______ in Texas.',
                'options' => [
                    'A' => 'right',
                    'B' => 'privilege',
                    'C' => 'necessity',
                    'D' => 'requirement',
                    'E' => 'guarantee',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'You may not park within _____ feet of a fire hydrant.',
                'options' => [
                    'A' => '10',
                    'B' => '15',
                    'C' => '20',
                    'D' => '25',
                    'E' => '30',
                ],
                'correct' => 'B',
            ],
            [
                'question' => 'Exhausted drivers cause approximately _______ collisions per year.',
                'options' => [
                    'A' => '100,000',
                    'B' => '150,000',
                    'C' => '200,000',
                    'D' => '250,000',
                    'E' => '300,000',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'This course must be completed at least _____ hours before your court deadline.',
                'options' => [
                    'A' => '12',
                    'B' => '18',
                    'C' => '24',
                    'D' => '48',
                    'E' => '72',
                ],
                'correct' => 'C',
            ],
            [
                'question' => 'Common understanding among drivers helps create:',
                'options' => [
                    'A' => 'Competition',
                    'B' => 'Confusion',
                    'C' => 'Equilibrium',
                    'D' => 'Chaos',
                    'E' => 'Conflict',
                ],
                'correct' => 'C',
            ],
        ];

        foreach ($finalExamQuestions as $index => $question) {
            $options = array_values(array_filter([
                $question['options']['A'],
                $question['options']['B'],
                $question['options']['C'],
                $question['options']['D'],
                $question['options']['E'] ?? null,
            ]));

            // Convert letter to actual answer text
            $correctIndex = ord($question['correct']) - ord('A');
            $correctAnswer = $options[$correctIndex];

            DB::table('questions')->insert([
                'course_id' => $courseId,
                'chapter_id' => $finalExamChapter->id,
                'question_text' => $question['question'],
                'question_type' => 'multiple_choice',
                'options' => json_encode($options),
                'correct_answer' => $correctAnswer,
                'explanation' => '',
                'points' => 1,
                'order_index' => $index + 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Added final exam questions');
    }
}

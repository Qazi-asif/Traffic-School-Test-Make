<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting course details update...\n\n";

// Delaware Course Details
$delawareCourseDetails = "Course Details

Who Can Take the Delaware Defensive Driving Course?

Our online Delaware Defensive Driving Courses are designed for licensed Delaware drivers who want to improve their driving skills, reduce insurance costs, or meet court requirements.

You can take one of our approved Delaware courses if you've:
• Received a court order to complete an aggressive driving or defensive driving course.
• Want to earn a 10% auto insurance discount for 3 years.
• Need to renew your 3-year discount and boost it to 15%.
• Want to earn a 3-point credit on your Delaware driving record.
• Prefer to take the course voluntarily to refresh your safe-driving knowledge.

All courses are approved by the Delaware Division of Motor Vehicles (DMV) and meet all state requirements for insurance reduction and point credits.

Whether you're taking it by choice or by requirement, our courses are 100% online, self-paced, and stress-free — complete them anytime, anywhere.

What to Expect from Our Online Delaware Defensive Driving Courses

Our Delaware courses are built for busy drivers who want convenience and flexibility. You'll get 24/7 access, clear lessons, and unlimited retakes on all quizzes and exams.

Why You'll Love This Course
• Learn Anywhere: Access from any computer, tablet, or smartphone with internet access.
• Learn Anytime: Study on your schedule — start and stop as often as you like.
• Learn at Your Own Pace: Take the entire course in one sitting or spread it out; your progress is saved automatically.

Available Courses
• Basic Insurance Reduction Course (6 hours): Earn a 10% insurance discount for 3 years plus a 3-point credit on your record.
• 3-Year Refresher Course (3 hours): Renew your discount for another 3 years and increase it to 15%.
• Aggressive Driver Course: Complete your court-ordered requirement for aggressive driving violations.

What's Included
• 10-12 short, easy-to-read chapters covering Delaware traffic laws, highway safety, and defensive driving techniques.
• Chapter quizzes (10 questions each) — must score 70% or higher to continue.
• Final Exam: 25 multiple-choice questions — 70% or higher to pass.
• Unlimited Retakes: Retake any quiz or the final exam until you pass — no penalties.

Certificate Delivery & Submission

Will You Send My Certificate to the Delaware DMV?
Yes! Your completion is automatically reported to the Delaware DMV within 24-48 hours for point credit and verification.

How Will I Get My Certificate?
You'll receive your certificate of completion by email at no extra cost.

How to Use Your Certificate
• Insurance Discount: Send your certificate to your insurance provider to apply your 10% or 15% discount.
• Court Requirement: If ordered by the court, follow the submission instructions provided at your hearing.

A Friendly Reminder
It's your responsibility to confirm that your insurance provider, court, or the Delaware DMV accepts this course for your specific situation and to submit your certificate on time if required.";

try {
    // Update Delaware courses in courses table
    $delawareCoursesUpdated = DB::table('courses')
        ->where('state', 'DE')
        ->orWhere('state', 'Delaware')
        ->orWhere('title', 'LIKE', '%Delaware%')
        ->update(['course_details' => $delawareCourseDetails]);
    
    echo "✓ Updated {$delawareCoursesUpdated} Delaware courses in 'courses' table\n";

    // Update Delaware courses in florida_courses table (if any exist with Delaware reference)
    $delawareFloridaCoursesUpdated = DB::table('florida_courses')
        ->where('state_code', 'DE')
        ->orWhere('title', 'LIKE', '%Delaware%')
        ->update(['course_details' => $delawareCourseDetails]);
    
    echo "✓ Updated {$delawareFloridaCoursesUpdated} Delaware courses in 'florida_courses' table\n";

    // Florida Course Details
    $floridaCourseDetails = "Course Details

Who Can Take the 4-Hour Florida Basic Driver Improvement (BDI) Course?

Our 4-hour BDI course is designed for Florida drivers who want or need to refresh their safe-driving skills. You can take this course if you've:

• Received a non-criminal minor traffic ticket.
• Been ordered by a court to complete a 4-hour driver improvement course.
• Been at fault in an accident that caused injury to another person.
• Been involved in two or more crashes within a 12-month period.
• Chosen to take the course voluntarily to earn a possible insurance discount.

Whether you're taking the course by choice or by requirement, we make it easy, flexible, and stress-free to complete.

What to Expect from Our 4-Hour Online Florida Driving School

Our 100% online Florida Basic Driver Improvement (BDI) Course is approved by Florida courts and meets all state requirements. We've built it to fit around your schedule — no classrooms, no commuting, and no pressure.

Why You'll Love This Course
• Learn Anywhere: Take your course on any computer, tablet, or smartphone with internet access.
• Learn Anytime: Enjoy 24/7 access — start and stop whenever you want, and pick up right where you left off.
• Learn at Your Own Pace: Work through the course all at once or one chapter at a time — your progress is saved automatically.

What's Included
• 10 Short, Easy-to-Read Chapters with quick multiple-choice review questions.
• Final Exam: 25 multiple-choice questions — you'll need 80% or higher to pass.
• Unlimited Retakes: Don't stress! You can retake the quizzes and final exam as many times as needed to pass.

Certificate Delivery & Submission

Will You Send My Dummies Traffic School / Defensive Driving School Certificate to the Court for Me?
Once you finish your course, your completion certificate will be emailed to you within 48 hours.

Before reaching out to us, please make sure to check your spam or junk folder — sometimes our emails get filtered there by mistake.

How to Submit Your Certificate in Florida
You'll need to submit your completion certificate yourself to the clerk's office in the county where you received your ticket. Each county has its own process, so we recommend contacting the traffic division of your local clerk's office to confirm how they'd like you to submit your certificate (email, in person, or online).

A Friendly Reminder
It's your responsibility to verify that this course is accepted by the organization requiring it (such as your court, DMV, or insurance company) and to make sure your certificate gets turned in on time.

Sign up today and start driving smarter!";

    // Update Florida courses in courses table
    $floridaCoursesUpdated = DB::table('courses')
        ->where('state', 'FL')
        ->orWhere('state', 'Florida')
        ->orWhere('title', 'LIKE', '%Florida%')
        ->update(['course_details' => $floridaCourseDetails]);
    
    echo "✓ Updated {$floridaCoursesUpdated} Florida courses in 'courses' table\n";

    // Update Florida courses in florida_courses table
    $floridaFloridaCoursesUpdated = DB::table('florida_courses')
        ->where('state_code', 'FL')
        ->orWhere('state_code', 'Florida')
        ->orWhere('title', 'LIKE', '%Florida%')
        ->update(['course_details' => $floridaCourseDetails]);
    
    echo "✓ Updated {$floridaFloridaCoursesUpdated} Florida courses in 'florida_courses' table\n";

    // Missouri Course Details
    $missouriCourseDetails = "Course Details

Who Can Take the Missouri Online Driver Improvement Program?

Our Missouri Driver Improvement Course is designed for drivers who need to meet court requirements, reduce points on their driving record, or simply improve their driving skills. You can take this course if you've:

• Received a moving violation and want to avoid points on your driving record.
• Been ordered by a Missouri court to complete a driver-improvement program.
• Voluntarily want to take the course to refresh your safe-driving skills or qualify for an insurance discount.

Whether you're taking the course by choice or by requirement, our flexible online format makes it easy and stress-free.

What to Expect from Our Missouri Online Driver Improvement Course

The course is 100% online, state-approved by the Missouri Safety Center and meets all requirements for point reduction on your Missouri driver's license.

Why You'll Love This Course:
• Learn Anywhere: Access on any computer, tablet or smartphone with internet.
• Learn Anytime: Available 24/7 – start, stop and resume as your schedule allows.
• Go at Your Own Pace: Dive in one sitting or spread it out over time; your progress is saved automatically.

What's Included:
• 11 easy-to-understand chapters covering topics like Missouri traffic law, defensive driving, highway & night driving, vehicle maintenance, DUI/substance abuse laws.
• At the end of each chapter: a 10-question multiple-choice quiz.
• Final exam: 50 multiple-choice questions. Score 80% or higher to pass.
• Unlimited retakes: You can retake quizzes and the final exam as many times as needed.

The Role of MO Form 4444: Your Certificate & Submission Instructions

When you complete the course, you'll receive MO Form 4444 (the \"Record of Participation and Completion of Driver Improvement Program\" form). This form is crucial because it is the official document that shows you completed the required program. Here's how to handle it:

If you are taking the course to reduce points after a moving violation (and the court granted you permission): you must print or download your Form 4444, then have your court or judge sign it (if required), and then send it to the Missouri Department of Revenue (DOR) within 15 days of completing the course.

If you are taking it for voluntary reasons (e.g., insurance discount) or the Fine Collection Center (\"FCC\") authorized it: you must submit the Form 4444 according to the instructions — often to the insurance company or FCC, or to the DOR if required. The form must arrive at DOR within 15 days of course completion when required for point removal.

Important Notes:
• You may only take a driver improvement course for point reduction once in a 36-month period. mosafetycenter.com
• You must usually have the court's permission before enrolling in the course if you're doing it to avoid points.

Certificate Delivery & Submission

Once you've completed the online course and passed all required quizzes and the final exam, you'll receive your Form 4444 electronically (PDF) or by mail, depending on the provider. The form serves as your certificate of completion.

How to Submit It:
• For point reduction: Submit the signed Form 4444 to DOR within 15 days of course completion.
• For court-ordered completion: Submit it to the court or clerk's office as instructed.
• For insurance/voluntary: Submit to your insurance provider or other entity as instructed.

A Friendly Reminder

It's your responsibility to:
• Confirm that the court or agency handling your case accepts the online driver improvement course and your provider's certificate.
• Follow the submission instructions for Form 4444 carefully and meet any deadlines (e.g., 15-day timeframe) to ensure your points are removed.
• Understand that the course removes points, not the violation itself.

Sign up today and start driving smarter!";

    // Update Missouri courses in courses table
    $missouriCoursesUpdated = DB::table('courses')
        ->where('state', 'MO')
        ->orWhere('state', 'Missouri')
        ->orWhere('title', 'LIKE', '%Missouri%')
        ->update(['course_details' => $missouriCourseDetails]);
    
    echo "✓ Updated {$missouriCoursesUpdated} Missouri courses in 'courses' table\n";

    // Update Missouri courses in florida_courses table (if any exist with Missouri reference)
    $missouriFloridaCoursesUpdated = DB::table('florida_courses')
        ->where('state_code', 'MO')
        ->orWhere('title', 'LIKE', '%Missouri%')
        ->update(['course_details' => $missouriCourseDetails]);
    
    echo "✓ Updated {$missouriFloridaCoursesUpdated} Missouri courses in 'florida_courses' table\n";

    // Texas Course Details
    $texasCourseDetails = "Course Details

Who Can Take the Texas Defensive Driving Course?

Our Texas Defensive Driving Course is designed for drivers who need to dismiss a ticket, meet court requirements, or refresh their driving skills.

You can take this course if you've:
• Received a moving violation and want to get your ticket dismissed.
• Been ordered by a Texas court to complete a defensive driving course.
• Want to keep your driving record clean and avoid higher insurance rates.
• Voluntarily want to improve your safe-driving skills or qualify for an insurance discount.

Whether you're taking this course by choice or by requirement, our flexible 100% online format makes it easy, fast, and stress-free.

What to Expect from Our Texas Defensive Driving Course

Our course is approved by the Texas Department of Licensing and Regulation (TDLR) and meets all requirements for ticket dismissal and insurance discounts.

Why You'll Love This Course
• Learn Anywhere: Access on any computer, tablet or smartphone with internet.
• Learn Anytime: Available 24/7 — start, stop and resume as your schedule allows.
• Go at Your Own Pace: Dive in one sitting or spread it out over time; your progress is saved automatically.
• Engaging Content: Short chapters, animations, and multiple-choice quizzes make learning easy and enjoyable.

What's Included
• Easy-to-follow lessons covering Texas traffic laws, defensive driving strategies, safe following distances, and distracted driving awareness.
• Short quizzes after each section to help reinforce what you've learned.
• Final exam: 25 multiple-choice questions. Score 70% or higher to pass.
• Unlimited retakes — no pressure, just progress!

How to Use This Course for Ticket Dismissal

Step 1. Get Court Approval
Before you begin, contact your court to request permission to take a defensive driving course. You can do this by phone, in writing (on the back of your citation), or in person before your appearance date.

To qualify, you must:
• Have a valid, non-commercial Texas driver's license.
• Have been ticketed for less than 25 mph over the speed limit.
• Not have completed a defensive driving course in the past 12 months.
• Not have received the violation in a construction or work zone.

Tip: Many courts have downloadable forms or affidavits on their websites — check there before visiting in person.

Step 2. Submit Required Documents
Mail or deliver the following items to your court:
• Proof of a valid Texas driver's license.
• Proof of auto insurance.
• Your signed citation (pleading guilty or no contest).
• Any court-specific affidavits or forms.
• The court's administrative fee (varies by county).

Step 3. Enroll in and Complete Your Course
Once approved, enroll in your TDLR-approved Texas Defensive Driving Course. The course is self-paced, but your court may assign a completion deadline — be sure to finish on time.

Step 4. Submit Your Completion Documents
After finishing the course, send or deliver these to your court:
• Your Dummies Traffic School Certificate of Completion.
• An official copy of your Texas driving record (you can order this online from the Texas DPS website).

Once the court receives your documents, they will process your dismissal. We recommend checking your Texas driving record afterward to confirm it's been updated.

Certificate Delivery & Submission

When you complete your course, you'll receive your Certificate of Completion electronically (PDF) or by mail, depending on your chosen delivery method.

How to submit it:
• For ticket dismissal: Send your certificate and driving record to your court by the required deadline.
• For insurance discounts: Submit your certificate directly to your insurance provider.

A Friendly Reminder

It's your responsibility to:
• Confirm that your court or agency approves online defensive driving courses and accepts your provider's certificate.
• Follow all submission deadlines set by your court to ensure your ticket is dismissed properly.
• Remember that the course dismisses the citation but does not remove the violation from all records unless processed by the court.

Sign up today and start driving smarter!";

    // Update Texas courses in courses table
    $texasCoursesUpdated = DB::table('courses')
        ->where('state', 'TX')
        ->orWhere('state', 'Texas')
        ->orWhere('title', 'LIKE', '%Texas%')
        ->update(['course_details' => $texasCourseDetails]);
    
    echo "✓ Updated {$texasCoursesUpdated} Texas courses in 'courses' table\n";

    // Update Texas courses in florida_courses table (if any exist with Texas reference)
    $texasFloridaCoursesUpdated = DB::table('florida_courses')
        ->where('state_code', 'TX')
        ->orWhere('title', 'LIKE', '%Texas%')
        ->update(['course_details' => $texasCourseDetails]);
    
    echo "✓ Updated {$texasFloridaCoursesUpdated} Texas courses in 'florida_courses' table\n";

    $totalUpdated = $delawareCoursesUpdated + $delawareFloridaCoursesUpdated + $floridaCoursesUpdated + $floridaFloridaCoursesUpdated + $missouriCoursesUpdated + $missouriFloridaCoursesUpdated + $texasCoursesUpdated + $texasFloridaCoursesUpdated;

    echo "\n✅ Course details update completed successfully!\n";
    echo "Total courses updated: {$totalUpdated}\n";
    echo "  - Delaware: " . ($delawareCoursesUpdated + $delawareFloridaCoursesUpdated) . "\n";
    echo "  - Florida: " . ($floridaCoursesUpdated + $floridaFloridaCoursesUpdated) . "\n";
    echo "  - Missouri: " . ($missouriCoursesUpdated + $missouriFloridaCoursesUpdated) . "\n";
    echo "  - Texas: " . ($texasCoursesUpdated + $texasFloridaCoursesUpdated) . "\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

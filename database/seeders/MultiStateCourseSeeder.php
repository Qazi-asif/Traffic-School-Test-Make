<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\FloridaCourse;
use App\Models\Missouri\Course as MissouriCourse;
use App\Models\Texas\Course as TexasCourse;
use App\Models\Delaware\Course as DelawareCourse;
use App\Models\Chapter;

class MultiStateCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding multi-state courses...');

        // Seed Florida Courses
        $this->seedFloridaCourses();
        
        // Seed Missouri Courses
        $this->seedMissouriCourses();
        
        // Seed Texas Courses
        $this->seedTexasCourses();
        
        // Seed Delaware Courses
        $this->seedDelawareCourses();

        $this->command->info('Multi-state courses seeded successfully!');
    }

    private function seedFloridaCourses()
    {
        $this->command->info('Seeding Florida courses...');

        $floridaCourses = [
            [
                'title' => 'Florida Basic Driver Improvement (BDI) Course',
                'description' => 'State-approved 4-hour Basic Driver Improvement course for Florida drivers.',
                'course_details' => 'This course meets Florida DHSMV requirements for traffic citation dismissal and point reduction.',
                'state_code' => 'FL',
                'min_pass_score' => 80,
                'duration' => 240, // 4 hours
                'price' => 24.95,
                'dicds_course_id' => 'FL-BDI-001',
                'certificate_template' => 'florida_bdi_certificate',
                'passing_score' => 80,
                'course_type' => 'BDI',
                'delivery_type' => 'Internet',
                'certificate_type' => 'florida_certificate',
                'strict_duration_enabled' => true,
                'is_active' => true,
            ],
            [
                'title' => 'Florida Advanced Driver Improvement (ADI) Course',
                'description' => 'State-approved 12-hour Advanced Driver Improvement course for serious traffic violations.',
                'course_details' => 'Required for drivers with serious traffic violations or court-ordered attendance.',
                'state_code' => 'FL',
                'min_pass_score' => 80,
                'duration' => 720, // 12 hours
                'price' => 49.95,
                'dicds_course_id' => 'FL-ADI-001',
                'certificate_template' => 'florida_adi_certificate',
                'passing_score' => 80,
                'course_type' => 'ADI',
                'delivery_type' => 'Internet',
                'certificate_type' => 'florida_certificate',
                'strict_duration_enabled' => true,
                'is_active' => true,
            ]
        ];

        foreach ($floridaCourses as $courseData) {
            $course = FloridaCourse::create($courseData);
            $this->createSampleChapters($course->id, 'florida_courses', 'FL', $courseData['course_type']);
        }
    }

    private function seedMissouriCourses()
    {
        $this->command->info('Seeding Missouri courses...');

        $missouriCourses = [
            [
                'title' => 'Missouri Defensive Driving Course',
                'description' => 'State-approved 8-hour defensive driving course for Missouri drivers.',
                'state_code' => 'MO',
                'missouri_course_code' => 'MO-DD-001',
                'course_type' => 'defensive_driving',
                'form_4444_template' => 'missouri_form_4444',
                'requires_form_4444' => true,
                'required_hours' => 8.00,
                'max_completion_days' => 90,
                'approval_number' => 'MO-2024-001',
                'passing_score' => 70,
                'price' => 29.95,
                'duration' => 480, // 8 hours
                'certificate_type' => 'missouri_certificate',
                'strict_duration_enabled' => false,
                'is_active' => true,
            ]
        ];

        foreach ($missouriCourses as $courseData) {
            $course = MissouriCourse::create($courseData);
            $this->createSampleChapters($course->id, 'missouri_courses', 'MO', 'defensive_driving');
        }
    }

    private function seedTexasCourses()
    {
        $this->command->info('Seeding Texas courses...');

        $texasCourses = [
            [
                'title' => 'Texas Defensive Driving Course',
                'description' => 'State-approved 6-hour defensive driving course for Texas drivers.',
                'state_code' => 'TX',
                'texas_course_code' => 'TX-DD-001',
                'tdlr_course_id' => 'TDLR-2024-001',
                'course_type' => 'defensive_driving',
                'requires_proctoring' => false,
                'defensive_driving_hours' => 6,
                'required_hours' => 6.00,
                'max_completion_days' => 90,
                'approval_number' => 'TX-2024-001',
                'certificate_template' => 'texas_dd_certificate',
                'passing_score' => 75,
                'price' => 25.95,
                'duration' => 360, // 6 hours
                'certificate_type' => 'texas_certificate',
                'strict_duration_enabled' => true,
                'is_active' => true,
            ]
        ];

        foreach ($texasCourses as $courseData) {
            $course = TexasCourse::create($courseData);
            $this->createSampleChapters($course->id, 'texas_courses', 'TX', 'defensive_driving');
        }
    }

    private function seedDelawareCourses()
    {
        $this->command->info('Seeding Delaware courses...');

        $delawareCourses = [
            [
                'title' => 'Delaware Defensive Driving Course (6-Hour)',
                'description' => 'State-approved 6-hour defensive driving course for Delaware drivers.',
                'state_code' => 'DE',
                'delaware_course_code' => 'DE-DD-6HR',
                'course_type' => 'defensive_driving',
                'required_hours' => 6.00,
                'max_completion_days' => 90,
                'approval_number' => 'DE-2024-001',
                'certificate_template' => 'delaware_dd_certificate',
                'quiz_rotation_enabled' => true,
                'quiz_pool_size' => 50,
                'passing_score' => 80,
                'price' => 24.95,
                'duration' => 360, // 6 hours
                'certificate_type' => 'delaware_certificate',
                'strict_duration_enabled' => true,
                'duration_type' => '6hr',
                'is_active' => true,
            ],
            [
                'title' => 'Delaware Defensive Driving Course (3-Hour)',
                'description' => 'State-approved 3-hour defensive driving course for Delaware drivers.',
                'state_code' => 'DE',
                'delaware_course_code' => 'DE-DD-3HR',
                'course_type' => 'defensive_driving',
                'required_hours' => 3.00,
                'max_completion_days' => 90,
                'approval_number' => 'DE-2024-002',
                'certificate_template' => 'delaware_dd_certificate',
                'quiz_rotation_enabled' => true,
                'quiz_pool_size' => 25,
                'passing_score' => 80,
                'price' => 19.95,
                'duration' => 180, // 3 hours
                'certificate_type' => 'delaware_certificate',
                'strict_duration_enabled' => true,
                'duration_type' => '3hr',
                'is_active' => true,
            ]
        ];

        foreach ($delawareCourses as $courseData) {
            $course = DelawareCourse::create($courseData);
            $this->createSampleChapters($course->id, 'delaware_courses', 'DE', 'defensive_driving');
        }
    }

    private function createSampleChapters($courseId, $courseTable, $stateCode, $courseType)
    {
        $chapters = $this->getChaptersByStateAndType($stateCode, $courseType);

        foreach ($chapters as $index => $chapterData) {
            Chapter::create([
                'course_id' => $courseId,
                'course_table' => $courseTable,
                'state_code' => $stateCode,
                'title' => $chapterData['title'],
                'content' => $chapterData['content'],
                'order_index' => $index + 1,
                'duration' => $chapterData['duration'],
                'required_min_time' => $chapterData['duration'],
                'is_active' => true,
            ]);
        }
    }

    private function getChaptersByStateAndType($stateCode, $courseType)
    {
        switch ($stateCode) {
            case 'FL':
                return $this->getFloridaChapters($courseType);
            case 'MO':
                return $this->getMissouriChapters();
            case 'TX':
                return $this->getTexasChapters();
            case 'DE':
                return $this->getDelawareChapters();
            default:
                return [];
        }
    }

    private function getFloridaChapters($courseType)
    {
        if ($courseType === 'BDI') {
            return [
                [
                    'title' => 'Introduction to Defensive Driving',
                    'content' => '<h2>Welcome to Florida Basic Driver Improvement</h2><p>This course is designed to help you become a safer, more responsible driver while meeting Florida DHSMV requirements.</p><p><strong>Course Objectives:</strong></p><ul><li>Understand Florida traffic laws</li><li>Learn defensive driving techniques</li><li>Reduce risk of accidents</li><li>Improve driving skills</li></ul>',
                    'duration' => 30
                ],
                [
                    'title' => 'Florida Traffic Laws and Regulations',
                    'content' => '<h2>Florida Traffic Laws</h2><p>Understanding Florida traffic laws is essential for safe driving and avoiding citations.</p><p><strong>Key Topics:</strong></p><ul><li>Speed limits and regulations</li><li>Right-of-way rules</li><li>Traffic signals and signs</li><li>Parking regulations</li></ul>',
                    'duration' => 45
                ],
                [
                    'title' => 'Defensive Driving Techniques',
                    'content' => '<h2>Defensive Driving Strategies</h2><p>Learn proven techniques to anticipate and avoid dangerous situations on the road.</p><p><strong>Key Concepts:</strong></p><ul><li>Maintaining safe following distance</li><li>Scanning for hazards</li><li>Managing speed and space</li><li>Dealing with aggressive drivers</li></ul>',
                    'duration' => 60
                ],
                [
                    'title' => 'Impaired and Distracted Driving',
                    'content' => '<h2>Avoiding Impaired and Distracted Driving</h2><p>Understanding the dangers and legal consequences of impaired and distracted driving.</p><p><strong>Topics Covered:</strong></p><ul><li>Alcohol and drug impairment</li><li>Cell phone and texting dangers</li><li>Other distractions</li><li>Legal penalties</li></ul>',
                    'duration' => 45
                ],
                [
                    'title' => 'Course Review and Final Exam',
                    'content' => '<h2>Course Review</h2><p>Review all key concepts covered in this course before taking your final exam.</p><p><strong>Final Exam Requirements:</strong></p><ul><li>Must score 80% or higher to pass</li><li>40 questions covering all course material</li><li>Multiple attempts allowed</li><li>Certificate issued upon passing</li></ul>',
                    'duration' => 60
                ]
            ];
        } else {
            // ADI course chapters
            return [
                [
                    'title' => 'Advanced Driver Improvement Introduction',
                    'content' => '<h2>Welcome to Florida Advanced Driver Improvement</h2><p>This comprehensive 12-hour course addresses serious traffic violations and advanced driving concepts.</p>',
                    'duration' => 60
                ],
                [
                    'title' => 'Traffic Law Violations and Consequences',
                    'content' => '<h2>Understanding Serious Traffic Violations</h2><p>Learn about the most serious traffic violations and their consequences.</p>',
                    'duration' => 90
                ],
                // Add more ADI chapters as needed
            ];
        }
    }

    private function getMissouriChapters()
    {
        return [
            [
                'title' => 'Missouri Defensive Driving Introduction',
                'content' => '<h2>Welcome to Missouri Defensive Driving</h2><p>This 8-hour course meets Missouri state requirements for defensive driving education.</p>',
                'duration' => 60
            ],
            [
                'title' => 'Missouri Traffic Laws',
                'content' => '<h2>Missouri Traffic Laws and Regulations</h2><p>Understanding Missouri-specific traffic laws and regulations.</p>',
                'duration' => 90
            ],
            [
                'title' => 'Defensive Driving Principles',
                'content' => '<h2>Core Defensive Driving Principles</h2><p>Learn the fundamental principles of defensive driving.</p>',
                'duration' => 90
            ],
            [
                'title' => 'Hazard Recognition and Response',
                'content' => '<h2>Recognizing and Responding to Hazards</h2><p>Develop skills to identify and respond to driving hazards.</p>',
                'duration' => 90
            ],
            [
                'title' => 'Final Review and Examination',
                'content' => '<h2>Course Review and Final Exam</h2><p>Review course material and complete your final examination.</p>',
                'duration' => 90
            ]
        ];
    }

    private function getTexasChapters()
    {
        return [
            [
                'title' => 'Texas Defensive Driving Introduction',
                'content' => '<h2>Welcome to Texas Defensive Driving</h2><p>This 6-hour course meets Texas TDLR requirements for defensive driving.</p>',
                'duration' => 45
            ],
            [
                'title' => 'Texas Traffic Laws and Safety',
                'content' => '<h2>Texas Traffic Laws</h2><p>Understanding Texas-specific traffic laws and safety requirements.</p>',
                'duration' => 75
            ],
            [
                'title' => 'Defensive Driving Strategies',
                'content' => '<h2>Defensive Driving Strategies</h2><p>Learn proven defensive driving strategies for Texas roads.</p>',
                'duration' => 75
            ],
            [
                'title' => 'Risk Management and Hazard Awareness',
                'content' => '<h2>Managing Risk on Texas Roads</h2><p>Develop risk management skills for safe driving.</p>',
                'duration' => 75
            ],
            [
                'title' => 'Final Review and Testing',
                'content' => '<h2>Course Completion</h2><p>Review course material and complete your final examination.</p>',
                'duration' => 90
            ]
        ];
    }

    private function getDelawareChapters()
    {
        return [
            [
                'title' => 'Delaware Defensive Driving Introduction',
                'content' => '<h2>Welcome to Delaware Defensive Driving</h2><p>This course meets Delaware state requirements for defensive driving education.</p>',
                'duration' => 45
            ],
            [
                'title' => 'Delaware Traffic Laws',
                'content' => '<h2>Delaware Traffic Laws and Regulations</h2><p>Understanding Delaware-specific traffic laws.</p>',
                'duration' => 60
            ],
            [
                'title' => 'Defensive Driving Techniques',
                'content' => '<h2>Defensive Driving Techniques</h2><p>Learn effective defensive driving techniques.</p>',
                'duration' => 60
            ],
            [
                'title' => 'Accident Prevention',
                'content' => '<h2>Accident Prevention Strategies</h2><p>Strategies for preventing traffic accidents.</p>',
                'duration' => 60
            ],
            [
                'title' => 'Course Review and Final Exam',
                'content' => '<h2>Final Review and Examination</h2><p>Complete your course review and final examination.</p>',
                'duration' => 75
            ]
        ];
    }
}
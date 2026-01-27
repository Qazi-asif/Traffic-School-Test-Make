<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StateTransmission;
use App\Models\User;
use App\Models\Course;
use App\Models\UserCourseEnrollment;
use Carbon\Carbon;

class TestFloridaTransmissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test Florida transmissions...');

        // Get or create a Florida course
        $course = Course::where('state', 'Florida')->first();
        if (!$course) {
            $course = Course::create([
                'title' => 'Florida Basic Driver Improvement Course',
                'description' => 'Test course for Florida BDI certificate submission',
                'state' => 'Florida',
                'course_type' => 'BDI',
                'price' => 29.95,
                'duration' => 4,
                'passing_score' => 80,
                'is_active' => true,
                'delivery_type' => 'Internet',
                'certificate_type' => 'BDI',
            ]);
        }

        // Create test users and enrollments
        $testData = [
            [
                'user' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'driver_license' => 'D123456789012',
                    'date_of_birth' => '1990-01-15',
                ],
                'citation' => '1234567',
                'status' => 'pending',
                'county' => 'LEON',
            ],
            [
                'user' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'driver_license' => 'S987654321098',
                    'date_of_birth' => '1985-06-22',
                ],
                'citation' => '7654321',
                'status' => 'success',
                'county' => 'MIAMI-DADE',
            ],
            [
                'user' => [
                    'first_name' => 'Mike',
                    'last_name' => 'Johnson',
                    'email' => 'mike.johnson@example.com',
                    'driver_license' => 'J456789012345',
                    'date_of_birth' => '1992-03-10',
                ],
                'citation' => '9876543',
                'status' => 'error',
                'county' => 'ORANGE',
            ],
        ];

        foreach ($testData as $index => $data) {
            // Create or find user
            $user = User::where('email', $data['user']['email'])->first();
            if (!$user) {
                $birthDate = Carbon::parse($data['user']['date_of_birth']);
                $user = User::create([
                    'role_id' => 4, // Student role
                    'first_name' => $data['user']['first_name'],
                    'last_name' => $data['user']['last_name'],
                    'email' => $data['user']['email'],
                    'password' => bcrypt('password'),
                    'driver_license' => $data['user']['driver_license'],
                    'birth_month' => $birthDate->month,
                    'birth_day' => $birthDate->day,
                    'birth_year' => $birthDate->year,
                    'phone' => '555-0' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'address' => '123 Test Street',
                    'city' => 'Tallahassee',
                    'state' => 'FL',
                    'zip' => '32301',
                    'gender' => 'M',
                    'license_state' => 'FL',
                    'status' => 'active',
                ]);
            }

            // Create enrollment
            $enrollment = UserCourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'citation_number' => $data['citation'],
                'court_county' => $data['county'],
                'enrolled_at' => Carbon::now()->subDays(rand(1, 30)),
                'completed_at' => $data['status'] !== 'pending' ? Carbon::now()->subDays(rand(1, 7)) : null,
                'status' => $data['status'] === 'pending' ? 'active' : 'completed',
                'progress_percentage' => $data['status'] === 'pending' ? rand(10, 80) : 100,
                'payment_status' => 'paid',
                'amount_paid' => 29.95,
                'payment_method' => 'credit_card',
            ]);

            // Create state transmission
            $transmission = StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'FL',
                'system' => 'FLHSMV',
                'status' => $data['status'],
                'payload_json' => json_encode([
                    'mvUserid' => config('services.florida.username', 'test_user'),
                    'mvSchoolid' => config('services.florida.school_id', '30981'),
                    'mvSchoolIns' => config('services.florida.instructor_id', '76397'),
                    'mvSchoolCourse' => '40585',
                    'mvFirstName' => $data['user']['first_name'],
                    'mvLastName' => $data['user']['last_name'],
                    'mvDriversLicense' => $data['user']['driver_license'],
                    'mvCitationNumber' => $data['citation'],
                    'mvCitationCounty' => $data['county'],
                    'mvClassDate' => Carbon::now()->format('mdY'),
                    'mvDob' => Carbon::parse($data['user']['date_of_birth'])->format('mdY'),
                    'mvSex' => 'M',
                    'mvReasonAttending' => 'B1',
                ]),
                'response_code' => $this->getResponseCode($data['status']),
                'response_message' => $this->getResponseMessage($data['status']),
                'sent_at' => $data['status'] !== 'pending' ? Carbon::now()->subDays(rand(1, 7)) : null,
                'retry_count' => $data['status'] === 'error' ? rand(1, 3) : 0,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 7)),
            ]);

            $this->command->info("Created transmission #{$transmission->id} for {$user->first_name} {$user->last_name} - Status: {$data['status']}");
        }

        // Create a few more random transmissions for variety
        for ($i = 0; $i < 5; $i++) {
            $statuses = ['pending', 'success', 'error'];
            $counties = ['LEON', 'MIAMI-DADE', 'ORANGE', 'HILLSBOROUGH', 'BROWARD', 'PALM BEACH'];
            $status = $statuses[array_rand($statuses)];
            
            $birthDate = Carbon::now()->subYears(rand(18, 65));
            $user = User::create([
                'role_id' => 4, // Student role
                'first_name' => 'Test' . ($i + 4),
                'last_name' => 'User' . ($i + 4),
                'email' => 'test' . ($i + 4) . '@example.com',
                'password' => bcrypt('password'),
                'driver_license' => 'T' . str_pad($i + 4, 12, '0', STR_PAD_LEFT),
                'birth_month' => $birthDate->month,
                'birth_day' => $birthDate->day,
                'birth_year' => $birthDate->year,
                'phone' => '555-0' . str_pad($i + 4, 3, '0', STR_PAD_LEFT),
                'address' => '456 Random Street',
                'city' => 'Miami',
                'state' => 'FL',
                'zip' => '33101',
                'gender' => rand(0, 1) ? 'M' : 'F',
                'license_state' => 'FL',
                'status' => 'active',
            ]);

            $enrollment = UserCourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'citation_number' => str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'court_county' => $counties[array_rand($counties)],
                'enrolled_at' => Carbon::now()->subDays(rand(1, 60)),
                'completed_at' => $status !== 'pending' ? Carbon::now()->subDays(rand(1, 14)) : null,
                'status' => $status === 'pending' ? 'active' : 'completed',
                'progress_percentage' => $status === 'pending' ? rand(10, 80) : 100,
                'payment_status' => 'paid',
                'amount_paid' => 29.95,
                'payment_method' => rand(0, 1) ? 'credit_card' : 'paypal',
            ]);

            StateTransmission::create([
                'enrollment_id' => $enrollment->id,
                'state' => 'FL',
                'system' => 'FLHSMV',
                'status' => $status,
                'payload_json' => json_encode([
                    'mvUserid' => config('services.florida.username', 'test_user'),
                    'mvSchoolid' => config('services.florida.school_id', '30981'),
                    'mvFirstName' => $user->first_name,
                    'mvLastName' => $user->last_name,
                    'mvDriversLicense' => $user->driver_license,
                    'mvCitationNumber' => $enrollment->citation_number,
                    'mvCitationCounty' => $enrollment->court_county,
                ]),
                'response_code' => $this->getResponseCode($status),
                'response_message' => $this->getResponseMessage($status),
                'sent_at' => $status !== 'pending' ? Carbon::now()->subDays(rand(1, 14)) : null,
                'retry_count' => $status === 'error' ? rand(0, 5) : 0,
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now()->subDays(rand(0, 14)),
            ]);
        }

        $this->command->info('✅ Test Florida transmissions created successfully!');
        $this->command->info('📊 Total transmissions: ' . StateTransmission::count());
        $this->command->info('🔗 View at: http://127.0.0.1:8000/admin/state-transmissions');
    }

    /**
     * Get response code based on status.
     */
    protected function getResponseCode(string $status): ?string
    {
        return match ($status) {
            'success' => 'SUCCESS',
            'error' => $this->getRandomErrorCode(),
            'pending' => null,
            default => null,
        };
    }

    /**
     * Get response message based on status.
     */
    protected function getResponseMessage(string $status): ?string
    {
        return match ($status) {
            'success' => 'Certificate submitted successfully to Florida FLHSMV',
            'error' => $this->getRandomErrorMessage(),
            'pending' => null,
            default => null,
        };
    }

    /**
     * Get random Florida error code for testing.
     */
    protected function getRandomErrorCode(): string
    {
        $errorCodes = [
            'VL000', // Login failed
            'DV100', // Citation number incorrect length
            'CF032', // Invalid Florida driver license format
            'ST001', // Student last name missing
            'VC000', // Could not verify class
            'DB000', // Generic student insert error
        ];

        return $errorCodes[array_rand($errorCodes)];
    }

    /**
     * Get random error message for testing.
     */
    protected function getRandomErrorMessage(): string
    {
        $messages = [
            'Login failed - invalid credentials',
            'Citation number is required or incorrect length (must be seven characters)',
            'Submitted as Florida DL number, but not in Florida DL format A999999999999',
            'Student last name is missing',
            'Could not verify class. Check class dates and times for correct format',
            'Generic student insert error',
        ];

        return $messages[array_rand($messages)];
    }
}
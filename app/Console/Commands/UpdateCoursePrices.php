<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateCoursePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:update-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update course names and prices to match the official price list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating course names and prices...');

        $updates = [
            // Delaware Courses
            ['old_title' => '%Delaware%3%', 'new_title' => 'Defensive Driving - 3 Year Refresher/Renewal 3 Hour Course', 'price' => 17.95, 'state' => 'DE', 'duration' => 180],
            ['old_title' => '%Delaware%Aggressive%', 'new_title' => 'Driving/Ticket Dismissal – Aggressive Driving Course', 'price' => 100.00, 'state' => 'DE', 'duration' => 240],
            ['old_title' => '%Delaware%Insurance%', 'new_title' => 'Insurance Discount - 3 Year Refresher/Renewal 6 Hour Course', 'price' => 25.00, 'state' => 'DE', 'duration' => 360],

            // Florida Courses
            ['old_title' => '%Florida%Basic%Driver%Improvement%', 'new_title' => 'Driving/Ticket Dismissal - Florida 4-Hour Basic Driver Improvement Course (BDI)', 'price' => 19.95, 'state' => 'FL', 'duration' => 240],
            ['old_title' => '%Florida%Insurance%Discount%Defensive%', 'new_title' => 'Insurance Discount - Florida Defensive Driving Course / Work', 'price' => 16.95, 'state' => 'FL', 'duration' => 360],

            // Missouri Courses
            ['old_title' => '%Missouri%Insurance%Discount%', 'new_title' => 'Insurance Discount - Defensive Driving Course', 'price' => 24.95, 'state' => 'Missouri'],
            ['old_title' => '%Missouri%Driving%Ticket%Dismissal%', 'new_title' => 'Driving/Ticket Dismissal - Missouri 8 Hour Driver Improvement Course', 'price' => 24.94, 'state' => 'Missouri'],

            // Texas Courses
            ['old_title' => '%Texas%Driving%Ticket%Dismissal%', 'new_title' => 'Driving/Ticket Dismissal - Texas 6 Hour Defensive Driving Course', 'price' => 28.00, 'state' => 'TX'],
            ['old_title' => '%Texas%Insurance%Discount%', 'new_title' => 'Insurance Discount - Texas 6 Hour Defensive Driving Course', 'price' => 28.00, 'state' => 'TX'],
        ];

        $updated = 0;
        $notFound = 0;

        foreach ($updates as $update) {
            // Try florida_courses table first
            $query = \DB::table('florida_courses')
                ->where('title', 'LIKE', $update['old_title']);

            // Add state and duration filters if provided
            if (isset($update['state'])) {
                $query->where('state_code', $update['state']);
            }
            if (isset($update['duration'])) {
                $query->where('total_duration', $update['duration']);
            }

            $course = $query->first();

            if ($course) {
                \DB::table('florida_courses')
                    ->where('id', $course->id)
                    ->update([
                        'title' => $update['new_title'],
                        'price' => $update['price'],
                        'updated_at' => now(),
                    ]);
                $this->info("✓ Updated: {$update['new_title']} - \${$update['price']}");
                $updated++;
            } else {
                // Try courses table
                $query = \DB::table('courses')
                    ->where('title', 'LIKE', $update['old_title']);

                if (isset($update['state'])) {
                    $query->where('state', $update['state']);
                }
                if (isset($update['duration'])) {
                    $query->where('duration', $update['duration']);
                }

                $course = $query->first();

                if ($course) {
                    \DB::table('courses')
                        ->where('id', $course->id)
                        ->update([
                            'title' => $update['new_title'],
                            'price' => $update['price'],
                            'updated_at' => now(),
                        ]);
                    $this->info("✓ Updated: {$update['new_title']} - \${$update['price']}");
                    $updated++;
                } else {
                    $this->warn("✗ Not found: {$update['old_title']}");
                    $notFound++;
                }
            }
        }

        // Add Texas Seat Belt Course if it doesn't exist
        $texasSeatBelt = \DB::table('florida_courses')
            ->where('title', 'LIKE', '%Texas%Seat%Belt%')
            ->first();

        if (! $texasSeatBelt) {
            $newCourseId = \DB::table('florida_courses')->insertGetId([
                'title' => 'Driving/Ticket Dismissal - Texas Seat Belt Course',
                'description' => 'Texas Seat Belt Course approved by TDLR for ticket dismissal. License Number: CP007',
                'state_code' => 'TX',
                'course_type' => 'Ticket Dismissal',
                'total_duration' => 60,
                'price' => 28.00,
                'min_pass_score' => 70,
                'is_active' => true,
                'certificate_template' => 'CP007',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info('✓ Created: Driving/Ticket Dismissal - Texas Seat Belt Course - $28.00');
            $updated++;
        }

        $this->newLine();
        $this->info('Summary:');
        $this->info("  Updated/Created: {$updated} course(s)");
        if ($notFound > 0) {
            $this->warn("  Not Found: {$notFound} course(s)");
        }
        $this->info('Done!');

        return 0;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FloridaDataSeeder extends Seeder
{
    public function run(): void
    {
        // Insert sample Florida schools
        DB::table('florida_schools')->insert([
            [
                'school_id' => 'FL-001',
                'school_name' => 'Miami Traffic School',
                'address' => '123 Ocean Drive, Miami, FL 33139',
                'phone' => '305-555-0100',

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 'FL-002',
                'school_name' => 'Orlando Driving Academy',
                'address' => '456 Universal Blvd, Orlando, FL 32819',
                'phone' => '407-555-0200',

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => 'FL-003',
                'school_name' => 'Tampa Bay Traffic Institute',
                'address' => '789 Bay Street, Tampa, FL 33602',
                'phone' => '813-555-0300',

                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert sample Florida courses
        DB::table('florida_courses')->insert([
            [
                'course_type' => 'BDI',
                'delivery_type' => 'internet',
                'title' => 'Basic Driver Improvement (BDI)',
                'description' => '4-hour Basic Driver Improvement course',
                'state_code' => 'FL',
                'min_pass_score' => 80,
                'total_duration' => 240,
                'price' => 29.99,
                'dicds_course_id' => 'BDI-001',

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_type' => 'ADI',
                'delivery_type' => 'internet',
                'title' => 'Advanced Driver Improvement (ADI)',
                'description' => '12-hour Advanced Driver Improvement course',
                'state_code' => 'FL',
                'min_pass_score' => 80,
                'total_duration' => 720,
                'price' => 59.99,
                'dicds_course_id' => 'ADI-001',

                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_type' => 'TLSAE',
                'delivery_type' => 'internet',
                'title' => 'Traffic Law and Substance Abuse Education',
                'description' => '4-hour TLSAE course for first-time drivers',
                'state_code' => 'FL',
                'min_pass_score' => 80,
                'total_duration' => 240,
                'price' => 24.99,
                'dicds_course_id' => 'TLSAE-001',

                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

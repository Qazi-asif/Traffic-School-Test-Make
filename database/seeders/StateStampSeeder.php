<?php

namespace Database\Seeders;

use App\Models\StateStamp;
use Illuminate\Database\Seeder;

class StateStampSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['state_code' => 'FL', 'state_name' => 'Florida'],
            ['state_code' => 'TX', 'state_name' => 'Texas'],
            ['state_code' => 'CA', 'state_name' => 'California'],
            ['state_code' => 'NY', 'state_name' => 'New York'],
            ['state_code' => 'MO', 'state_name' => 'Missouri'],
            ['state_code' => 'IL', 'state_name' => 'Illinois'],
            ['state_code' => 'PA', 'state_name' => 'Pennsylvania'],
            ['state_code' => 'OH', 'state_name' => 'Ohio'],
            ['state_code' => 'GA', 'state_name' => 'Georgia'],
            ['state_code' => 'NC', 'state_name' => 'North Carolina'],
            ['state_code' => 'MI', 'state_name' => 'Michigan'],
            ['state_code' => 'NJ', 'state_name' => 'New Jersey'],
            ['state_code' => 'VA', 'state_name' => 'Virginia'],
            ['state_code' => 'WA', 'state_name' => 'Washington'],
            ['state_code' => 'AZ', 'state_name' => 'Arizona'],
            ['state_code' => 'MA', 'state_name' => 'Massachusetts'],
            ['state_code' => 'TN', 'state_name' => 'Tennessee'],
            ['state_code' => 'IN', 'state_name' => 'Indiana'],
            ['state_code' => 'MD', 'state_name' => 'Maryland'],
            ['state_code' => 'WI', 'state_name' => 'Wisconsin'],
        ];

        foreach ($states as $state) {
            StateStamp::updateOrCreate(
                ['state_code' => $state['state_code']],
                [
                    'state_name' => $state['state_name'],
                    'is_active' => true,
                    'description' => 'State stamp for '.$state['state_name'],
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run()
    {
        $states = [
            ['code' => 'AL', 'name' => 'Alabama', 'is_active' => true],
            ['code' => 'AK', 'name' => 'Alaska', 'is_active' => true],
            ['code' => 'AZ', 'name' => 'Arizona', 'is_active' => true],
            ['code' => 'AR', 'name' => 'Arkansas', 'is_active' => true],
            ['code' => 'CA', 'name' => 'California', 'is_active' => true],
            ['code' => 'CO', 'name' => 'Colorado', 'is_active' => true],
            ['code' => 'CT', 'name' => 'Connecticut', 'is_active' => true],
            ['code' => 'DE', 'name' => 'Delaware', 'is_active' => true],
            ['code' => 'FL', 'name' => 'Florida', 'is_active' => true],
            ['code' => 'GA', 'name' => 'Georgia', 'is_active' => true],
            ['code' => 'HI', 'name' => 'Hawaii', 'is_active' => true],
            ['code' => 'ID', 'name' => 'Idaho', 'is_active' => true],
            ['code' => 'IL', 'name' => 'Illinois', 'is_active' => true],
            ['code' => 'IN', 'name' => 'Indiana', 'is_active' => true],
            ['code' => 'IA', 'name' => 'Iowa', 'is_active' => true],
            ['code' => 'KS', 'name' => 'Kansas', 'is_active' => true],
            ['code' => 'KY', 'name' => 'Kentucky', 'is_active' => true],
            ['code' => 'LA', 'name' => 'Louisiana', 'is_active' => true],
            ['code' => 'ME', 'name' => 'Maine', 'is_active' => true],
            ['code' => 'MD', 'name' => 'Maryland', 'is_active' => true],
            ['code' => 'MA', 'name' => 'Massachusetts', 'is_active' => true],
            ['code' => 'MI', 'name' => 'Michigan', 'is_active' => true],
            ['code' => 'MN', 'name' => 'Minnesota', 'is_active' => true],
            ['code' => 'MS', 'name' => 'Mississippi', 'is_active' => true],
            ['code' => 'MO', 'name' => 'Missouri', 'is_active' => true],
            ['code' => 'MT', 'name' => 'Montana', 'is_active' => true],
            ['code' => 'NE', 'name' => 'Nebraska', 'is_active' => true],
            ['code' => 'NV', 'name' => 'Nevada', 'is_active' => true],
            ['code' => 'NH', 'name' => 'New Hampshire', 'is_active' => true],
            ['code' => 'NJ', 'name' => 'New Jersey', 'is_active' => true],
            ['code' => 'NM', 'name' => 'New Mexico', 'is_active' => true],
            ['code' => 'NY', 'name' => 'New York', 'is_active' => true],
            ['code' => 'NC', 'name' => 'North Carolina', 'is_active' => true],
            ['code' => 'ND', 'name' => 'North Dakota', 'is_active' => true],
            ['code' => 'OH', 'name' => 'Ohio', 'is_active' => true],
            ['code' => 'OK', 'name' => 'Oklahoma', 'is_active' => true],
            ['code' => 'OR', 'name' => 'Oregon', 'is_active' => true],
            ['code' => 'PA', 'name' => 'Pennsylvania', 'is_active' => true],
            ['code' => 'RI', 'name' => 'Rhode Island', 'is_active' => true],
            ['code' => 'SC', 'name' => 'South Carolina', 'is_active' => true],
            ['code' => 'SD', 'name' => 'South Dakota', 'is_active' => true],
            ['code' => 'TN', 'name' => 'Tennessee', 'is_active' => true],
            ['code' => 'TX', 'name' => 'Texas', 'is_active' => true],
            ['code' => 'UT', 'name' => 'Utah', 'is_active' => true],
            ['code' => 'VT', 'name' => 'Vermont', 'is_active' => true],
            ['code' => 'VA', 'name' => 'Virginia', 'is_active' => true],
            ['code' => 'WA', 'name' => 'Washington', 'is_active' => true],
            ['code' => 'WV', 'name' => 'West Virginia', 'is_active' => true],
            ['code' => 'WI', 'name' => 'Wisconsin', 'is_active' => true],
            ['code' => 'WY', 'name' => 'Wyoming', 'is_active' => true],
            ['code' => 'DC', 'name' => 'District of Columbia', 'is_active' => true],
            ['code' => 'PR', 'name' => 'Puerto Rico', 'is_active' => true],
            ['code' => 'VI', 'name' => 'Virgin Islands', 'is_active' => true],
            ['code' => 'GU', 'name' => 'Guam', 'is_active' => true],
            ['code' => 'AS', 'name' => 'American Samoa', 'is_active' => true],
            ['code' => 'MP', 'name' => 'Northern Mariana Islands', 'is_active' => true],
        ];

        foreach ($states as $state) {
            State::firstOrCreate(
                ['code' => $state['code']],
                $state
            );
        }
    }
}

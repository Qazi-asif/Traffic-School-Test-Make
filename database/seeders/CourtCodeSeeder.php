<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\CourtCode;
use Illuminate\Database\Seeder;

class CourtCodeSeeder extends Seeder
{
    public function run(): void
    {
        // Florida TVCC codes
        $floridaCourts = Court::where('state', 'FL')->get();

        foreach ($floridaCourts as $court) {
            $countyCode = strtoupper(substr($court->county, 0, 3));
            $courtNum = str_pad($court->id, 4, '0', STR_PAD_LEFT);

            CourtCode::create([
                'court_id' => $court->id,
                'code_type' => 'tvcc',
                'code_value' => "FL{$courtNum}",
                'code_name' => "{$court->court} TVCC",
                'is_active' => true,
                'effective_date' => now()->subYear(),
            ]);

            CourtCode::create([
                'court_id' => $court->id,
                'code_type' => 'location_code',
                'code_value' => substr($courtNum, -3),
                'code_name' => "{$court->court} Location",
                'is_active' => true,
            ]);
        }

        // Missouri court codes
        $missouriCourts = Court::where('state', 'MO')->get();

        foreach ($missouriCourts as $court) {
            $courtNum = str_pad($court->id, 4, '0', STR_PAD_LEFT);

            CourtCode::create([
                'court_id' => $court->id,
                'code_type' => 'court_id',
                'code_value' => "MO{$courtNum}",
                'code_name' => "{$court->court} ID",
                'is_active' => true,
            ]);
        }

        // Texas court codes
        $texasCourts = Court::where('state', 'TX')->get();

        foreach ($texasCourts as $court) {
            $courtNum = str_pad($court->id, 4, '0', STR_PAD_LEFT);

            CourtCode::create([
                'court_id' => $court->id,
                'code_type' => 'court_id',
                'code_value' => "TX{$courtNum}",
                'code_name' => "{$court->court} ID",
                'is_active' => true,
            ]);
        }

        // Delaware court codes
        $delawareCourts = Court::where('state', 'DE')->get();

        foreach ($delawareCourts as $court) {
            $courtNum = str_pad($court->id, 4, '0', STR_PAD_LEFT);

            CourtCode::create([
                'court_id' => $court->id,
                'code_type' => 'court_id',
                'code_value' => "DE{$courtNum}",
                'code_name' => "{$court->court} ID",
                'is_active' => true,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtsSeeder extends Seeder
{
    public function run(): void
    {
        $file = base_path('all.csv');

        if (! file_exists($file)) {
            $this->command->error("CSV file not found: {$file}");

            return;
        }

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);
        $batchSize = 500;
        $batch = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $batch[] = [
                    'state' => trim($row[0]),
                    'county' => trim($row[1]),
                    'court' => trim($row[2]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    Court::insert($batch);
                    $batch = [];
                }
            }
        }

        if (! empty($batch)) {
            Court::insert($batch);
        }

        fclose($handle);
        $this->command->info('Courts data imported successfully!');
    }
}

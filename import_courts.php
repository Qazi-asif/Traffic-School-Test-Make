<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Court;

$file = __DIR__.'/all.csv';

if (! file_exists($file)) {
    echo "CSV file not found: {$file}\n";
    exit(1);
}

$handle = fopen($file, 'r');
$header = fgetcsv($handle);
$batchSize = 500;
$batch = [];
$count = 0;

echo "Starting import...\n";

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
            $count += count($batch);
            echo "Imported {$count} records...\n";
            $batch = [];
        }
    }
}

if (! empty($batch)) {
    Court::insert($batch);
    $count += count($batch);
}

fclose($handle);
echo "Courts data imported successfully! Total records: {$count}\n";

<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\StateTransmission;
use Illuminate\Console\Command;

class CreateBulkTransmissions extends Command
{
    protected $signature = 'transmissions:create-bulk {--state=FL} {--limit=0}';
    protected $description = 'Create state transmissions for all completed enrollments';

    public function handle()
    {
        $state = $this->option('state');
        $limit = $this->option('limit');

        $query = Enrollment::where('state', $state)
            ->whereNotNull('completed_at')
            ->whereDoesntHave('stateTransmissions', function ($q) {
                $q->where('state', 'FL');
            });

        if ($limit > 0) {
            $query->limit($limit);
        }

        $enrollments = $query->get();
        $count = 0;

        foreach ($enrollments as $enrollment) {
            try {
                StateTransmission::create([
                    'enrollment_id' => $enrollment->id,
                    'state' => $state,
                    'system' => 'FLHSMV',
                    'status' => 'pending',
                    'payload_json' => json_encode([
                        'enrollment_id' => $enrollment->id,
                        'created_at' => now(),
                    ]),
                ]);
                $count++;
                $this->line("✓ Created transmission for enrollment {$enrollment->id}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for enrollment {$enrollment->id}: {$e->getMessage()}");
            }
        }

        $this->info("\nCreated {$count} transmissions");
    }
}

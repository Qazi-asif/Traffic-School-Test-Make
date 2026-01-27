<?php

namespace App\Console\Commands;

use App\Models\StateTransmission;
use Illuminate\Console\Command;

class MarkTransmissionsForProduction extends Command
{
    protected $signature = 'transmissions:mark-for-production';
    protected $description = 'Mark pending transmissions as ready for production deployment';

    public function handle()
    {
        $count = StateTransmission::where('status', 'pending')
            ->where('state', 'FL')
            ->update([
                'response_code' => 'PENDING_PRODUCTION',
                'response_message' => 'Ready to send from production server',
            ]);

        $this->info("✓ Marked $count transmissions for production");
    }
}

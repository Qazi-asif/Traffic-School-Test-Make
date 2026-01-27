<?php

namespace App\Console\Commands;

use App\Models\StateTransmission;
use Illuminate\Console\Command;

class FixStateTransmissionSystems extends Command
{
    protected $signature = 'transmissions:fix-systems';
    protected $description = 'Fix NULL system values in existing state transmissions';

    public function handle()
    {
        $this->info('Fixing NULL system values in state_transmissions table...');

        $updated = 0;

        // Get all transmissions with NULL or empty system
        $transmissions = StateTransmission::whereNull('system')
            ->orWhere('system', '')
            ->get();

        foreach ($transmissions as $transmission) {
            $system = $this->determineSystem($transmission->state);
            
            if ($system) {
                $transmission->update(['system' => $system]);
                $updated++;
                $this->line("Updated transmission {$transmission->id}: {$transmission->state} -> {$system}");
            }
        }

        $this->info("Fixed {$updated} transmission records.");
        return 0;
    }

    private function determineSystem(string $state): ?string
    {
        switch ($state) {
            case 'FL':
                return 'FLHSMV';
            case 'CA':
                return 'TVCC';
            case 'NV':
                return 'NTSA';
            case 'TX':
            case 'DE':
            case 'MO':
            default:
                return 'CCS';
        }
    }
}
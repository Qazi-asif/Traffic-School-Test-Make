<?php

namespace App\Console\Commands;

use App\Jobs\SendFloridaTransmissionJob;
use App\Models\StateTransmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingTransmissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'transmissions:send-pending 
                            {--state=FL : The state code to process}
                            {--limit=100 : Maximum number of transmissions to process}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send all pending state transmissions to the appropriate state API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $state = strtoupper($this->option('state'));
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info("Processing pending transmissions for state: {$state}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No transmissions will be sent');
        }

        // Get pending transmissions
        $transmissions = StateTransmission::with(['enrollment.user', 'enrollment.course'])
            ->forState($state)
            ->pending()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($transmissions->isEmpty()) {
            $this->info("No pending transmissions found for state {$state}");

            return self::SUCCESS;
        }

        $this->info("Found {$transmissions->count()} pending transmissions");

        $bar = $this->output->createProgressBar($transmissions->count());
        $bar->start();

        $sent = 0;
        $failed = 0;

        foreach ($transmissions as $transmission) {
            try {
                if (! $dryRun) {
                    // Dispatch the appropriate job based on state
                    match ($state) {
                        'FL' => SendFloridaTransmissionJob::dispatch($transmission->id),
                        // Add more states as needed
                        default => throw new \Exception("No job handler for state: {$state}")
                    };
                    $sent++;
                } else {
                    $this->newLine();
                    $this->line("Would send transmission #{$transmission->id} for enrollment #{$transmission->enrollment_id}");
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to dispatch transmission', [
                    'transmission_id' => $transmission->id,
                    'error' => $e->getMessage(),
                ]);

                if (! $dryRun) {
                    $this->newLine();
                    $this->error("Failed to dispatch transmission #{$transmission->id}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("DRY RUN COMPLETE - {$transmissions->count()} transmissions would be sent");
        } else {
            $this->info("Successfully dispatched: {$sent}");
            if ($failed > 0) {
                $this->error("Failed to dispatch: {$failed}");
            }

            Log::info('Scheduled transmission batch', [
                'state' => $state,
                'sent' => $sent,
                'failed' => $failed,
            ]);
        }

        return self::SUCCESS;
    }
}

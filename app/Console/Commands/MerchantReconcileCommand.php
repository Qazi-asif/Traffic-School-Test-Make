<?php

namespace App\Console\Commands;

use App\Models\MerchantAccount;
use App\Services\MerchantService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MerchantReconcileCommand extends Command
{
    protected $signature = 'merchant:reconcile {account : Merchant account ID} {--start= : Start date} {--end= : End date}';

    protected $description = 'Run reconciliation for merchant account';

    public function handle(MerchantService $merchantService)
    {
        $account = MerchantAccount::findOrFail($this->argument('account'));

        $start = $this->option('start')
            ? Carbon::parse($this->option('start'))
            : now()->startOfMonth();

        $end = $this->option('end')
            ? Carbon::parse($this->option('end'))
            : now()->endOfMonth();

        $this->info("Creating reconciliation for {$account->account_name}");
        $this->info("Period: {$start->format('Y-m-d')} to {$end->format('Y-m-d')}");

        try {
            $reconciliation = $merchantService->createReconciliation($account, $start, $end);

            $this->table(
                ['Metric', 'Amount'],
                [
                    ['Expected Revenue', '$'.number_format($reconciliation->expected_revenue, 2)],
                    ['Expected Fees', '$'.number_format($reconciliation->expected_fees, 2)],
                    ['Status', $reconciliation->status],
                ]
            );

            $this->info("✓ Reconciliation created (ID: {$reconciliation->id})");

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());

            return 1;
        }
    }
}

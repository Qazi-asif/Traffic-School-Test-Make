<?php

namespace App\Console\Commands;

use App\Models\MerchantAccount;
use App\Services\MerchantService;
use Illuminate\Console\Command;

class MerchantSyncCommand extends Command
{
    protected $signature = 'merchant:sync {account? : Merchant account ID} {--days=30 : Number of days to sync}';

    protected $description = 'Sync merchant transactions from payment gateway';

    public function handle(MerchantService $merchantService)
    {
        $accountId = $this->argument('account');
        $days = $this->option('days');

        $accounts = $accountId
            ? MerchantAccount::where('id', $accountId)->get()
            : MerchantAccount::active()->get();

        if ($accounts->isEmpty()) {
            $this->error('No merchant accounts found');

            return 1;
        }

        $start = now()->subDays($days);
        $end = now();

        foreach ($accounts as $account) {
            $this->info("Syncing {$account->account_name}...");

            try {
                $synced = $merchantService->syncTransactions($account, $start, $end);
                $this->info("✓ Synced {$synced} transactions");
            } catch (\Exception $e) {
                $this->error('✗ Error: '.$e->getMessage());
            }
        }

        $this->info('Sync completed');

        return 0;
    }
}

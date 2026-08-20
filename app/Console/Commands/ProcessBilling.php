<?php

namespace App\Console\Commands;

use App\Domains\Finance\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessBilling extends Command
{
    protected $signature = 'billing:process {--dry-run : Roll back all changes at the end and just report what would happen}';
    protected $description = 'Run the daily billing cycle: renewals, trial conversions, and grace/lockdown enforcement';

    public function handle(BillingService $billingService): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        DB::beginTransaction();
        $summary = $billingService->runDailyBillingCycle();

        if ($isDryRun) {
            DB::rollBack();
            $this->warn('DRY RUN — no changes were saved.');
        } else {
            DB::commit();
        }

        $this->table(
            ['Metric', 'Count'],
            collect($summary)->map(fn ($count, $key) => [ucwords(str_replace('_', ' ', $key)), $count])->values()->toArray()
        );

        return self::SUCCESS;
    }
}

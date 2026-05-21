<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooSyncService;

class BackfillIsInternal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:backfill-is-internal {--start-date=2025-12-08 00:00:00 : Start date for backfilling}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill is_internal flag for local transactions from Odoo repair orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->option('start-date');
        $this->info("Starting backfill for is_internal from Odoo since {$startDate}...");
        
        $service = new OdooSyncService();
        $result = $service->backfillIsInternal($startDate);

        if ($result['success']) {
            $this->info($result['message']);
            return 0;
        } else {
            $this->error($result['message']);
            return 1;
        }
    }
}

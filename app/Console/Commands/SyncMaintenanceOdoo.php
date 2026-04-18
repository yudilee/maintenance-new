<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooSyncService;

class SyncMaintenanceOdoo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:sync-odoo {--force : Force sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Maintenance Job Orders and Bills from Odoo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Maintenance Odoo Sync...');
        
        $service = new OdooSyncService();
        $result = $service->sync('Scheduled');

        if ($result['success']) {
            $this->info($result['message']);
            return 0;
        } else {
            $this->error($result['message']);
            return 1;
        }
    }
}

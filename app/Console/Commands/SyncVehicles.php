<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooSyncService;

class SyncVehicles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:sync-vehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ALL vehicle data (stock.lot) from Odoo to local mobil table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Odoo Vehicle Sync...');

        $service = new OdooSyncService();
        $totalSynced = 0;
        $offset = 0;
        $limit = 500;
        $hasMore = true;

        $bar = $this->output->createProgressBar(1);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Fetching vehicles...');
        $bar->start();

        do {
            $result = $service->syncVehicles('Scheduled', $offset, $limit);

            if (!$result['success']) {
                $bar->finish();
                $this->newLine();
                $this->error($result['message']);
                return 1;
            }

            $batchCount = $result['items'] ?? 0;
            $totalSynced += $batchCount;
            $hasMore = $result['hasMore'] ?? false;
            $offset = $result['nextOffset'] ?? ($offset + $limit);

            $bar->setMessage("Synced {$totalSynced} vehicles (batch at offset {$offset})...");
            $bar->advance();

            if (!$hasMore) {
                break;
            }
        } while ($hasMore);

        $bar->finish();
        $this->newLine();
        $this->info("Vehicle sync completed. Total: {$totalSynced} vehicles synced.");

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Htransaksi;
use App\Models\Dtransaksi;
use App\Services\OdooSyncService;

class BackfillProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:backfill-product {--limit= : Max number of job orders to backfill} {--chunk=100 : Batch size for sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill product and tanggal_part_keluar fields in dtransaksi from Odoo in batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        $this->info("Fetching job orders needing product field backfill...");

        $query = Htransaksi::where('tanggal_job', '>=', '2025-12-08')
            ->where(function($mainQuery) {
                $mainQuery->whereIn('nomor_job', function($q) {
                    $q->select('nomor_invoice')->from('dtransaksi')->whereNull('product');
                })->orWhere(function($q) {
                    $q->where('is_internal', 1)
                      ->whereIn('nomor_job', function($sub) {
                          $sub->select('nomor_invoice')->from('dtransaksi')->whereNull('tanggal_part_keluar');
                      });
                });
            });

        if ($limit) {
            $query->limit((int) $limit);
        }

        $jobNumbers = $query->pluck('nomor_job')->toArray();
        $total = count($jobNumbers);

        if ($total === 0) {
            $this->info("No job orders need backfilling.");
            return 0;
        }

        $this->info("Found {$total} job orders to backfill. Running in chunks of {$chunkSize}...");

        $chunks = array_chunk($jobNumbers, $chunkSize);
        $bar = $this->output->createProgressBar(count($chunks));
        $bar->start();

        $service = new OdooSyncService();
        $successCount = 0;
        $failedCount = 0;

        foreach ($chunks as $chunk) {
            try {
                $result = $service->sync('Backfill', $chunk);
                if (isset($result['success']) && $result['success']) {
                    $successCount += count($chunk);
                } else {
                    $failedCount += count($chunk);
                    $this->error("\nFailed to sync batch: " . ($result['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $failedCount += count($chunk);
                $this->error("\nException during batch sync: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nBackfill process completed.");
        $this->info("Successfully backfilled: {$successCount} job orders.");
        if ($failedCount > 0) {
            $this->warn("Failed to backfill: {$failedCount} job orders.");
        }

        return 0;
    }
}

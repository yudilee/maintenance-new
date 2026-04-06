<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Htransaksi;
use App\Models\Dtransaksi;
use Illuminate\Support\Facades\DB;

class CleanupOdooDuplicates extends Command
{
    protected $signature = 'maintenance:cleanup-odoo-duplicates 
                            {--start-date=2025-04-01 : Start date for cleanup} 
                            {--end-date=2025-12-08 : End date for cleanup} 
                            {--dry-run : Only show what would be deleted} 
                            {--force : Force deletion}';

    protected $description = 'Clean up duplicate Odoo records (kode_sup like O-%) within a specific date range.';

    public function handle(): int
    {
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');
        $dryRun = $this->option('dry-run') || !$this->option('force');

        if ($dryRun) {
            $this->info("--- DRY RUN MODE (No changes will be made) ---");
        } else {
            $this->warn("--- ACTUAL CLEANUP MODE ---");
            if (!$this->confirm('Are you sure you want to PERMANENTLY DELETE records? This cannot be undone!')) {
                return 1;
            }
        }

        $this->info("Cleaning up Odoo duplicates from {$startDate} to {$endDate}");

        // 1. Identify records
        $query = Htransaksi::whereBetween('tanggal_job', [$startDate, $endDate])
                           ->where('kode_sup', 'like', 'O-%');

        $headersCount = $query->count();
        $invoices = $query->pluck('nomor_invoice');
        $detailsCount = Dtransaksi::whereIn('nomor_invoice', $invoices)->count();

        $this->info("Found {$headersCount} headers (htransaksi) and {$detailsCount} details (dtransaksi).");

        if ($headersCount === 0) {
            $this->info("No Odoo records found for this period. Nothing to do.");
            return 0;
        }

        if ($dryRun) {
            $this->info("Dry run complete. Use --force to perform actual deletion.");
            return 0;
        }

        // 2. Perform Deletion
        try {
            DB::beginTransaction();

            $this->info("Deleting details...");
            Dtransaksi::whereIn('nomor_invoice', $invoices)->delete();

            $this->info("Deleting headers...");
            $query->delete();

            DB::commit();
            $this->info("✓ Cleanup successful. Transferred control back to FoxPro-sourced data for this period.");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Cleanup failed: " . $e->getMessage());
            return 1;
        }
    }
}

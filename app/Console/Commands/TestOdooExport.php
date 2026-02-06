<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooService;

class TestOdooExport extends Command
{
    protected $signature = 'test:odoo-export';
    protected $description = 'Test if we can use Odoo export_data method (Option A)';

    public function handle(OdooService $odoo)
    {
        $this->info("Testing Odoo export_data capability...\n");

        // 1. Test basic connection
        $this->info("Step 1: Testing connection...");
        $connResult = $odoo->testConnection();
        
        if (!$connResult['success']) {
            $this->error("✗ " . $connResult['message']);
            return 1;
        }
        $this->info("✓ " . $connResult['message'] . "\n");

        // 2. Test export_data method
        $this->info("Step 2: Testing export_data permission...");
        $exportResult = $odoo->testExportData();

        if ($exportResult['success']) {
            $this->info("✓ " . $exportResult['message'] . "\n");
            
            $this->info("Sample data (first row):");
            $fields = $exportResult['fields'] ?? [];
            $sample = $exportResult['sample'] ?? [];
            
            foreach ($fields as $i => $field) {
                $value = $sample[$i] ?? '(empty)';
                $this->line("  $field: $value");
            }
            
            $this->newLine();
            $this->info("🎉 Option A is available! We can use export_data for full Excel parity.");
            return 0;
        } else {
            $this->error("✗ " . $exportResult['message']);
            $this->newLine();
            $this->warn("Option A is not available. Recommend proceeding with Option B.");
            return 1;
        }
    }
}

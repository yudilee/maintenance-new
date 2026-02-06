<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooService;

class DebugOdoo extends Command
{
    protected $signature = 'debug:odoo';
    protected $description = 'Debug Odoo API response fields';

    public function handle()
    {
        $odoo = new OdooService();
        $this->info('Testing Odoo Connection...');
        
        $conn = $odoo->testConnection();
        if (!$conn['success']) {
            $this->error($conn['message']);
            return;
        }
        $this->info($conn['message']);

        $this->info('Fetching Stock Lots...');
        // We call fetchStockLots directly but it's protected/private? No it's public.
        // But wait, I need to check if I can modify it to just return raw for me, or I use the existing method and var_dump inside,
        // OR I just replicate the fetch logic here.
        // Replicating is safer to avoid modifying service during debug.
        
        // Reflection to access protected execute if needed, or just use fetchStockLots and inspect results
        // fetchStockLots returns transformed data? No, it returns 'data' which is the raw-ish results from search_read.
        
        $result = $odoo->fetchStockLots();
        
        if (!$result['success']) {
            $this->error($result['message']);
            return;
        }
        
        $data = $result['data'];
        $this->info('Fetched ' . count($data) . ' records.');
        
        // Take a sample of 5 records where rental_id is present
        $count = 0;
        foreach ($data as $row) {
            if (!empty($row['rental_id'])) {
                $this->info("--- Record ---");
                $this->info("Name: " . ($row['name'] ?? 'N/A'));
                $this->info("Rental ID: " . print_r($row['rental_id'], true));
                $this->info("Original Reserved: " . print_r($row['original_reserved'] ?? 'MISSING', true));
                $this->info("Is Vendor Rent: " . print_r($row['is_vendor_rent'] ?? 'MISSING', true));
                
                $count++;
                if ($count >= 10) break;
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;

class DebugInventory extends Command
{
    protected $signature = 'debug:inventory {--type=rented : Type of debug (rented, discrepancy)}';
    protected $description = 'Debug inventory data integrity issues';

    public function handle()
    {
        $type = $this->option('type');

        $this->info("Starting Inventory Debug for: " . $type);

        if ($type === 'rented') {
            $this->debugRentedLeak();
        } elseif ($type === 'discrepancy') {
            $this->debugDiscrepancy();
        } else {
            $this->error("Invalid type. Use --type=rented or --type=discrepancy");
        }
    }

    private function debugRentedLeak()
    {
        $items = Item::all();
        $inCustomer = 0;
        $caught = 0;
        $uncaught = [];
        $counts = [
            'Vendor Rent' => 0,
            'Original in Customer' => 0,
            'Replacement' => 0,
            'Check Rent position' => 0
        ];

        foreach ($items as $item) {
            if ($item->is_sold) continue;
            if ($item->location !== 'Partners/Customers/Rental') continue;

            $inCustomer++;
            
            $isVendor = $item->is_vendor_rent;
            $rentalId = $item->rental_id;
            $lotNo = $item->lot_number;
            $reservedLot = $item->reserved_lot;

            $matched = false;

            if ($isVendor) {
                $matched = true;
                $counts['Vendor Rent']++;
            } elseif ($lotNo == $reservedLot && !empty($reservedLot)) {
                $matched = true;
                $counts['Original in Customer']++;
            } elseif (!empty($rentalId) && $lotNo != $reservedLot && !$isVendor) {
                $matched = true;
                $counts['Replacement']++;
            } elseif (empty($rentalId)) {
                $matched = true;
                $counts['Check Rent position']++;
            }

            if ($matched) {
                $caught++;
            } else {
                $uncaught[] = $item->lot_number;
            }
        }

        $this->table(
            ['Category', 'Count'],
            array_map(function($k, $v) { return [$k, $v]; }, array_keys($counts), array_values($counts))
        );

        $this->info("Total In Customer: $inCustomer");
        $this->info("Caught: $caught");
        
        if (count($uncaught) > 0) {
            $this->error("Uncaught (Leak): " . count($uncaught));
            $this->info("Leaked Lots: " . implode(', ', array_slice($uncaught, 0, 10)) . (count($uncaught) > 10 ? '...' : ''));
        } else {
            $this->info("No leaks detected!");
        }
    }

    private function debugDiscrepancy()
    {
        // Ported from analyze_discrepancy.php
        $items = Item::all();

        $externalOrigWithReplace = $items->filter(function($item) {
            return stripos($item->location, 'Partners/Vendors/Service') === 0
                && !empty($item->rental_id)
                && $item->lot_number == $item->reserved_lot
                && $item->rental_id_count > 1;
        });

        $this->info("External 'Original Rented with Replace' (dashboard count): " . $externalOrigWithReplace->count());

        // Find items that are main lots but whose replacement is NOT with customer (if that's the discrepancy)
        // ... Log additional details
        $this->info("Discrepancy analysis complete (add more logic here if needed based on recent issues).");
    }
}

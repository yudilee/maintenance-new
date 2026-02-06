<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Constants\Location;
use Carbon\Carbon;

class DebugServiceRentals extends Command
{
    protected $signature = 'debug:service-rentals';
    protected $description = 'Analyze Service items to explain count discrepancies';

    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        $this->info("Today: $today");

        // 1. Total Original (No Replace) in External Service
        // This mirrors SummaryGenerator logic (Location + RentalID + Count=1)
        $query = Item::where('location', 'like', Location::SERVICE_EXTERNAL . '%')
                     ->whereNotNull('rental_id')
                     ->where('rental_id_count', 1);
        
        $total = $query->count();
        $this->info("Total 'Original No Replace' in Ext Service: $total (Target: 199)");

        // 2. Check Status Breakdown
        $activeStartOnly = (clone $query)->where(function($q) use ($today) {
            $q->whereNull('actual_start_rental')->orWhere('actual_start_rental', '<=', $today);
        })->count();
        
        $activeStartAndEnd = (clone $query)->where(function($q) use ($today) {
            $q->whereNull('actual_start_rental')->orWhere('actual_start_rental', '<=', $today);
        })->where(function($q) use ($today) {
             $q->whereNull('actual_end_rental')->orWhere('actual_end_rental', '>=', $today);
        })->count();

        $future = (clone $query)->where('actual_start_rental', '>', $today)->count();
        $expired = (clone $query)->where('actual_end_rental', '<', $today)->count();
        
        $this->info("Breakdown:");
        $this->info("- Active (Start <= Today): $activeStartOnly");
        $this->info("- Active (Start <= Today AND End >= Today): $activeStartAndEnd");
        $this->info("- Future (Start > Today): $future");
        $this->info("- Expired (End < Today): $expired");
        $this->info("- Missing Dates: " . ($total - $future - $expired - $activeStartAndEnd));

        // 3. Reserved Lot Check
        $matchingReserved = (clone $query)->whereColumn('lot_number', 'reserved_lot')->count();
        $this->info("- Lot == ReservedLot: $matchingReserved");

        // 4. Sample Items (Active but failing logic?)
        $this->info("\nSample Items:");
        $samples = (clone $query)->limit(5)->get();
        foreach($samples as $s) {
            $this->info("Lot: {$s->lot_number} | RID: {$s->rental_id} | Start: {$s->actual_start_rental} | End: {$s->actual_end_rental} | Reserved: {$s->reserved_lot}");
        }
    }
}

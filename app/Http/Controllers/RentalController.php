<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\History;
use App\Constants\Location;

class RentalController extends Controller
{
    public function index()
    {
        // Get all items not sold
        $items = Item::where('is_sold', false)->whereNotNull('rental_id')->where('rental_id', '!=', '')->get();
        // Group by rental_id
        $grouped = $items->groupBy('rental_id');
        
        $rentalPairs = [];
        foreach ($grouped as $rid => $group) {
            if ($group->count() > 1) {
                // Sort Main first
                $sorted = $group->sortBy(function($item) {
                     return $item->vehicle_role === 'Main' ? 0 : 1;
                });
                
                $main = $sorted->firstWhere('vehicle_role', 'Main');
                
                // Determine Category based on Main Unit Location
                $category = 'other';
                if ($main) {
                    $loc = $main->location;
                    if (stripos($loc, 'Service') !== false || stripos($loc, 'Insurance') !== false) {
                        $category = 'service';
                    } elseif ($main->in_stock) {
                        $category = 'stock';
                    } elseif ($loc === Location::RENTAL_CUSTOMER) {
                        $category = 'customer';
                    }
                }

                $rentalPairs[$rid] = [
                    'rental_id' => $rid,
                    'vehicles' => $sorted->values(),
                    'main_vehicle' => $main,
                    'replacement_vehicles' => $sorted->where('vehicle_role', 'Replacement')->values(),
                    'category' => $category,
                ];
            }
        }
        
        $pairsCount = count($rentalPairs);
        
        // Count Stats
        $stats = [
            'total' => $pairsCount,
            'service' => collect($rentalPairs)->where('category', 'service')->count(),
            'stock' => collect($rentalPairs)->where('category', 'stock')->count(),
            'customer' => collect($rentalPairs)->where('category', 'customer')->count(),
            'other' => collect($rentalPairs)->where('category', 'other')->count(),
        ];
        
        // Metadata
        $latest = History::orderBy('snapshot_date', 'desc')->first();
        $metadata = ['imported_at' => $latest ? $latest->updated_at->format('Y-m-d H:i:s') : null];

        return view('rental_pairs', compact('rentalPairs', 'pairsCount', 'metadata', 'stats'));
    }
}

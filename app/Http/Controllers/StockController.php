<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Services\InventoryService;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    public function index()
    {
        $locations = Item::select('location')->distinct()->orderBy('location')->pluck('location');
        $products = Item::select('product')->distinct()->orderBy('product')->pluck('product');
        $types = Item::whereNotNull('rental_type')->where('rental_type', '!=', '')->select('rental_type')->distinct()->pluck('rental_type');
        
        return view('total_stock', compact('locations', 'products', 'types'));
    }

    public function filter(Request $request, InventoryService $inventory)
    {
        $query = Item::query()->where('is_sold', false)->where('on_hand_quantity', '>', 0);
        $filters = $request->input('filters', []);

        if (!empty($filters)) {
            $inventory->applyAdvancedFilters($query, $filters);
        }

        // Apply sorting
        $sortCol = $request->input('sortCol', 'id');
        $sortAsc = $request->input('sortAsc', true);
        $direction = $sortAsc ? 'asc' : 'desc';

        if ($sortCol === 'status') {
             $query->orderBy('rental_id', $direction);
        } else {
             $query->orderBy($sortCol, $direction);
        }

        // Pagination
        $perPage = $request->input('perPage', 50);
        $items = $query->paginate($perPage);

        return response()->json($items);
    }

    public function export(Request $request)
    {
        $filters = $request->input('filters', []);
        
        // When sent via form.submit(), JSON might be a string
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        }
        
        // Handle sorting from request
        $sortCol = $request->input('sortCol', 'lot_number');
        $sortAsc = filter_var($request->input('sortAsc', true), FILTER_VALIDATE_BOOLEAN);
        
        $filename = 'total_stock_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(
            new \App\Exports\TotalStockExport($filters, $sortCol, $sortAsc), 
            $filename
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Htransaksi;
use App\Models\Mobil;
use App\Models\Customer;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query || strlen($query) < 2) {
            return view('maintenance.search-results', [
                'query' => $query,
                'jobs' => collect(),
                'vehicles' => collect(),
                'customers' => collect(),
            ]);
        }

        // Clean the query by removing spaces for fuzzy matching
        $cleanQuery = str_replace(' ', '', $query);

        // Search Repair Jobs
        $jobs = Htransaksi::with(['customer', 'mobil'])
            ->whereRaw("REPLACE(nomor_job, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->orWhereRaw("REPLACE(nomor_chassis, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->orWhereRaw("REPLACE(nomor_invoice, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->limit(20)
            ->get();

        // Search Vehicles
        $vehicles = Mobil::whereRaw("REPLACE(nomor_polisi, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->orWhereRaw("REPLACE(nomor_chassis, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->orWhereRaw("REPLACE(model, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->limit(20)
            ->get();

        // Search Customers
        $customers = Customer::whereRaw("REPLACE(nama_customer, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->orWhereRaw("REPLACE(kode_customer, ' ', '') LIKE ?", ['%' . $cleanQuery . '%'])
            ->limit(20)
            ->get();

        return view('maintenance.search-results', compact('query', 'jobs', 'vehicles', 'customers'));
    }
}

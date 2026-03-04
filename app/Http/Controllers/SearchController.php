<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Models\Dtransaksi;

class SearchController extends Controller
{
    public function nomorPolisi(Request $request)
    {
        $search = $request->q;
        $results = Mobil::select('nomor_polisi')
            ->where('nomor_polisi', 'like', '%' . $search . '%')
            ->groupBy('nomor_polisi')
            ->limit(20)
            ->get();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = ['id' => $row->nomor_polisi, 'text' => $row->nomor_polisi];
        }
        return response()->json($formatted);
    }

    public function deskripsi(Request $request)
    {
        $search = $request->q;
        $results = Dtransaksi::select('deskripsi')
            ->where('deskripsi', 'like', '%' . $search . '%')
            ->groupBy('deskripsi')
            ->limit(20)
            ->get();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = ['id' => $row->deskripsi, 'text' => $row->deskripsi];
        }
        return response()->json($formatted);
    }

    public function customer(Request $request)
    {
        $search = $request->q;
        $results = \App\Models\Customer::select('kode_customer', 'nama_customer')
            ->whereRaw("CONCAT(kode_customer, ' - ', nama_customer) LIKE ?", ["%{$search}%"])
            ->groupBy('kode_customer', 'nama_customer')
            ->limit(20)
            ->get();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'id' => $row->kode_customer,
                'text' => $row->kode_customer . ' - ' . $row->nama_customer
            ];
        }
        return response()->json($formatted);
    }
}

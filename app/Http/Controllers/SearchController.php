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
            ->orderBy('nama_customer', 'asc')
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
    public function supplier(Request $request)
    {
        $search = $request->q;
        $results = \App\Models\Supplier::select('kode_supplier', 'nama_supplier')
            ->whereRaw("CONCAT(kode_supplier, ' - ', nama_supplier) LIKE ?", ["%{$search}%"])
            ->where('kode_supplier', '!=', '0')
            ->where('kode_supplier', '!=', '')
            ->orderBy('nama_supplier', 'asc')
            ->limit(25)
            ->get();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'id'   => $row->kode_supplier,
                'text' => $row->kode_supplier . ' - ' . $row->nama_supplier
            ];
        }
        return response()->json($formatted);
    }
}

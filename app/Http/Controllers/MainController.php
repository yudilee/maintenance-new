<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Htransaksi;
use App\Models\Mobil;

class MainController extends Controller
{
    public function index(Request $request)
    {
        $vehicleResults = collect();
        $mobilDetail = null;

        // If a specific vehicle is selected, skip the directory and go straight to its transactions
        if ($request->filled('nomor_polisi')) {
            return redirect()->route('maintenance.vehicle.transactions', $request->all());
        }

        // If only nama_customer is selected, show the directory of vehicles on the main page
        if ($request->filled('nama_customer') && !$request->filled('nomor_polisi')) {
            $customer = Customer::where('kode_customer', $request->nama_customer)->first();
            if ($customer) {
                // Get all htransaksi for this customer and date range
                $htransaksiQuery = Htransaksi::with(['mobil', 'supplier', 'dtransaksi'])->where('id_customer', $customer->id);

                if ($request->start_date_transaksi && $request->end_date_transaksi) {
                    $htransaksiQuery->whereBetween('tanggal_job', [$request->start_date_transaksi, $request->end_date_transaksi]);
                } elseif ($request->start_date_transaksi) {
                    $htransaksiQuery->where('tanggal_job', '>=', $request->start_date_transaksi);
                } elseif ($request->end_date_transaksi) {
                    $htransaksiQuery->where('tanggal_job', '<=', $request->end_date_transaksi);
                }

                // Extract distinct vehicle IDs first using SQL instead of loading all htransaksi rows into RAM
                $vehicleIds = $htransaksiQuery->select('nomor_chassis')->distinct()->pluck('nomor_chassis');
                
                // Fetch the vehicles matching those IDs
                $vehicleResults = Mobil::whereIn('nomor_chassis', $vehicleIds)->get()->unique('nomor_chassis')->values();
            }
        }

        return view('main', compact('vehicleResults', 'mobilDetail'));
    }

    public function search(Request $request)
    {
        $results = collect();
        $mobilDetail = null;
        $vehicleResults = collect();

        // If only nama_customer is selected
        if ($request->filled('nama_customer') && !$request->filled('nomor_polisi')) {
            $customer = Customer::where('kode_customer', $request->nama_customer)->first();
            if ($customer) {
                $htransaksiQuery = Htransaksi::with(['mobil', 'supplier', 'dtransaksi'])->where('id_customer', $customer->id);

                if ($request->start_date && $request->end_date) {
                    $htransaksiQuery->whereBetween('tanggal_job', [$request->start_date, $request->end_date]);
                } elseif ($request->start_date) {
                    $htransaksiQuery->where('tanggal_job', '>=', $request->start_date);
                } elseif ($request->end_date) {
                    $htransaksiQuery->where('tanggal_job', '<=', $request->end_date);
                }

                $vehicleIds = $htransaksiQuery->select('nomor_chassis')->distinct()->pluck('nomor_chassis');
                $vehicleResults = Mobil::whereIn('nomor_chassis', $vehicleIds)->get()->unique('nomor_chassis')->values();
            }
        }
        // If both nomor_polisi and nama_customer are selected
        elseif ($request->filled('nama_customer') && $request->filled('nomor_polisi')) {
            $customer = Customer::where('kode_customer', $request->nama_customer)->first();
            $query = Htransaksi::with(['mobil', 'supplier', 'dtransaksi']);
            if ($customer) {
                $query->where('id_customer', $customer->id);
            }

            $query->whereHas('mobil', function ($q) use ($request) {
                $q->where('nomor_polisi', $request->nomor_polisi);
            });

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('tanggal_job', [$request->start_date, $request->end_date]);
            } elseif ($request->start_date) {
                $query->where('tanggal_job', '>=', $request->start_date);
            } elseif ($request->end_date) {
                $query->where('tanggal_job', '<=', $request->end_date);
            }

            $results = $query->get();
            $mobilDetail = Mobil::where('nomor_polisi', $request->nomor_polisi)->first();
        }
        // If only nomor_polisi or other cases
        else {
            $query = Htransaksi::with(['mobil', 'supplier', 'dtransaksi']);
            if ($request->nomor_polisi) {
                $query->whereHas('mobil', function ($q) use ($request) {
                    $q->where('nomor_polisi', $request->nomor_polisi);
                });
                $mobilDetail = Mobil::where('nomor_polisi', $request->nomor_polisi)->first();
            }

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('tanggal_job', [$request->start_date, $request->end_date]);
            } elseif ($request->start_date) {
                $query->where('tanggal_job', '>=', $request->start_date);
            } elseif ($request->end_date) {
                $query->where('tanggal_job', '<=', $request->end_date);
            }

            $results = $query->get();
        }

        return view('main', compact('results', 'mobilDetail', 'vehicleResults'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\customer;
use Illuminate\Http\Request;
use App\Models\htransaksi;
use App\Models\mobil;
use App\Models\dtransaksi;

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
            $customer = customer::where('kode_customer', $request->nama_customer)->first();
            if ($customer) {
                // Get all htransaksi for this customer and date range
                $htransaksiQuery = htransaksi::with(['mobil', 'supplier', 'dtransaksi'])->where('id_customer', $customer->id);

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
                $vehicleResults = mobil::whereIn('nomor_chassis', $vehicleIds)->get()->unique('nomor_chassis')->values();
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
            // $request->nama_customer now contains kode_customer
            $customer = customer::where('kode_customer', $request->nama_customer)->first();
            if ($customer) {
                // Get all htransaksi for this customer and date range
                $htransaksiQuery = htransaksi::with(['mobil', 'supplier', 'dtransaksi'])->where('id_customer', $customer->id);

                if ($request->start_date && $request->end_date) {
                    $htransaksiQuery->whereBetween('tanggal_job', [$request->start_date, $request->end_date]);
                } elseif ($request->start_date) {
                    $htransaksiQuery->where('tanggal_job', '>=', $request->start_date);
                } elseif ($request->end_date) {
                    $htransaksiQuery->where('tanggal_job', '<=', $request->end_date);
                }

                // Extract distinct vehicle IDs first using SQL instead of loading all htransaksi rows into RAM
                $vehicleIds = $htransaksiQuery->select('nomor_chassis')->distinct()->pluck('nomor_chassis');
                
                // Fetch the vehicles matching those IDs
                $vehicleResults = mobil::whereIn('nomor_chassis', $vehicleIds)->get()->unique('nomor_chassis')->values();
            }
        }
        // If both nomor_polisi and nama_customer are selected
        elseif ($request->filled('nama_customer') && $request->filled('nomor_polisi')) {
            $customer = customer::where('kode_customer', $request->nama_customer)->first();
            $query = htransaksi::with(['mobil', 'supplier', 'dtransaksi']);
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
            $mobilDetail = mobil::where('nomor_polisi', $request->nomor_polisi)->first();
        }
        // If only nomor_polisi or other cases, keep your existing logic...
        else {
            $query = htransaksi::with(['mobil', 'supplier', 'dtransaksi']);
            if ($request->nomor_polisi) {
                $query->whereHas('mobil', function ($q) use ($request) {
                    $q->where('nomor_polisi', $request->nomor_polisi);
                });
                $mobilDetail = mobil::where('nomor_polisi', $request->nomor_polisi)->first();
            }

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('tanggal_job', [$request->start_date, $request->end_date]);
            } elseif ($request->start_date) {
                $query->where('tanggal_job', '>=', $request->start_date);
            } elseif ($request->end_date) {
                $query->where('tanggal_job', '<=', $request->end_date);
            }

            // if ($request->deskripsi) {
            //     $query->whereHas('dtransaksi', function ($q) use ($request) {
            //         $q->where('deskripsi', 'like', '%' . $request->deskripsi . '%');
            //     });
            // }
            $results = $query->get();
        }

        return view('main', compact('results', 'mobilDetail', 'vehicleResults'));
    }

    public function searchNomorPolisi(Request $request)
    {
        $search = $request->q;
        $results = mobil::select('nomor_polisi')
            ->where('nomor_polisi', 'like', '%' . $search . '%')
            ->groupBy('nomor_polisi')
            ->limit(20)
            ->get();

        // Format for Select2
        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = ['id' => $row->nomor_polisi, 'text' => $row->nomor_polisi];
        }
        return response()->json($formatted);
    }

    public function searchDeskripsi(Request $request)
    {
        $search = $request->q;
        $results = dtransaksi::select('deskripsi')
            ->where('deskripsi', 'like', '%' . $search . '%')
            ->groupBy('deskripsi')
            ->limit(20)
            ->get();

        // Format for Select2
        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = ['id' => $row->deskripsi, 'text' => $row->deskripsi];
        }
        return response()->json($formatted);
    }

    public function searchCustomer(Request $request)
    {
        $search = $request->q;
        $results = \App\Models\customer::select('kode_customer', 'nama_customer')
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

    // COMMENTED OUT - Simple Pagination (old method)
    /*
    public function vehicleTransactions_OLD(Request $request)
    {
        $results = collect();
        $mobilDetail = null;

        $nama_customer = $request->nama_customer;
        $nomor_polisi = $request->nomor_polisi;

        $start = $request->start_date_transaksi;
        $end = $request->end_date_transaksi;

        // Calculate grand totals for ALL transactions first
        $grandTotals = $this->calculateGrandTotals($nama_customer, $nomor_polisi, $start, $end);

        if ($nama_customer) {
            $customer = customer::where('kode_customer', $nama_customer)->first();
            if ($customer) {
                $query = htransaksi::with(['mobil', 'supplier', 'dtransaksi'])->where('id_customer', $customer->id);

                // Add date filtering here
                if ($start && $end) {
                    $query->whereBetween('tanggal_job', [$start, $end]);
                } elseif ($start) {
                    $query->where('tanggal_job', '>=', $start);
                } elseif ($end) {
                    $query->where('tanggal_job', '<=', $end);
                }

                if ($nomor_polisi) {
                    $query->whereHas('mobil', function ($q) use ($nomor_polisi) {
                        $q->where('nomor_polisi', $nomor_polisi);
                    });
                    $mobilDetail = mobil::where('nomor_polisi', $nomor_polisi)->first();
                }

                // Use pagination - 100 items per page
                $results = $query->orderBy('tanggal_job', 'desc')->paginate(100);
            }
        }

        return view('transaksi', compact('results', 'mobilDetail', 'nama_customer', 'nomor_polisi', 'grandTotals'));
    }
    */

    public function vehicleTransactions(Request $request)
    {
        $mobilDetail = null;
        $nama_customer = $request->nama_customer;
        $nomor_polisi = $request->nomor_polisi;
        $start = $request->start_date_transaksi;
        $end = $request->end_date_transaksi;

        if ($nomor_polisi) {
            $mobilDetail = mobil::where('nomor_polisi', $nomor_polisi)->first();
        }

        // Get grand totals for all transactions (not just current page)
        $grandTotals = $this->calculateGrandTotals($nama_customer, $nomor_polisi, $start, $end);

        return view('transaksi', compact('mobilDetail', 'nama_customer', 'nomor_polisi', 'grandTotals'));
    }

    public function vehicleTransactionsData(Request $request)
    {
        $nama_customer = $request->nama_customer;
        $nomor_polisi = $request->nomor_polisi;
        $start = $request->start_date_transaksi;
        $end = $request->end_date_transaksi;

        $query = $this->buildTransactionQuery($nama_customer, $nomor_polisi, $start, $end);

        // Get paginated results for DataTables
        $recordsTotal = $query->count();

        // Apply DataTables parameters
        $start_dt = $request->input('start', 0);
        $length = $request->input('length', 50);
        $search = $request->input('search.value', '');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_job', 'like', "%{$search}%")
                    ->orWhere('posisi_km', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();
        $results = $query->skip($start_dt)->take($length)->get();

        // Format data for DataTables
        $data = [];
        foreach ($results as $transaction) {
            $details = $transaction->dtransaksi;

            if ($details->count() > 0) {
                // First row with first detail
                $kodeSup = $transaction->kode_sup ?? '';
                $workshopHarent = (strpos($kodeSup, '*INTERNAL') !== false) ? 'Workshop Harent' : '';
                $maintenanceService = (trim($transaction->nomor_sv ?? '') === 'M') ? 'Maintenance' : ($transaction->nomor_sv ?? '');
                
                $data[] = [
                    'nomor_job' => $transaction->nomor_job . ' - ' . ($transaction->supplier->kode_supplier ?? '-') . ' - ' . ($transaction->supplier->nama_supplier ?? '-'),
                    'tanggal_job' => \Carbon\Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    'posisi_km' => $transaction->posisi_km,
                    'nomor_chassis' => $transaction->mobil->nomor_chassis ?? '-',
                    'nomor_polisi' => $transaction->mobil->nomor_polisi ?? '-',
                    'maintenance_service' => $maintenanceService,
                    'workshop_harent' => $workshopHarent,
                    'deskripsi' => $details->first()->deskripsi ?? '-',
                    'jumlah' => $details->first()->jumlah ?? '-',
                    'harga' => number_format($details->first()->harga ?? 0, 0, ',', '.'),
                    'harga_total' => ($transaction->harga_total ?? 0) != 0 ? number_format($transaction->harga_total, 0, ',', '.') : '-',
                    'harga_pajak' => ($transaction->harga_pajak ?? 0) != 0 ? number_format($transaction->harga_pajak, 0, ',', '.') : '-',
                    'keterangan' => $details->first()->keterangan ?? ($transaction->keterangan ?? '-'),
                    'tanggal_close' => \Carbon\Carbon::parse($transaction->tanggal_close)->format('d-m-Y'),
                    'is_detail' => false
                ];

                // Additional detail rows
                foreach ($details->skip(1) as $detail) {
                    $isTaxLine = ($detail->note === 'tax');
                    $data[] = [
                        'nomor_job' => '',
                        'tanggal_job' => '',
                        'posisi_km' => '',
                        'nomor_chassis' => '',
                        'nomor_polisi' => '',
                        'maintenance_service' => '',
                        'workshop_harent' => '',
                        'deskripsi' => $detail->deskripsi ?? '-',
                        'jumlah' => $isTaxLine ? '' : ($detail->jumlah ?? '-'),
                        'harga' => $isTaxLine ? '' : number_format($detail->harga ?? 0, 0, ',', '.'),
                        'harga_total' => '',
                        'harga_pajak' => $isTaxLine ? number_format($detail->value ?? 0, 0, ',', '.') : '',
                        'keterangan' => '',
                        'tanggal_close' => \Carbon\Carbon::parse($transaction->tanggal_close)->format('d-m-Y'),
                        'is_detail' => true
                    ];
                }
            } else {
                $kodeSup = $transaction->kode_sup ?? '';
                $workshopHarent = (strpos($kodeSup, '*INTERNAL') !== false) ? 'Workshop Harent' : '';
                $maintenanceService = (trim($transaction->nomor_sv ?? '') === 'M') ? 'Maintenance' : ($transaction->nomor_sv ?? '');
                
                $data[] = [
                    'nomor_job' => $transaction->nomor_job . ' - ' . ($transaction->supplier->kode_supplier ?? '-') . ' - ' . ($transaction->supplier->nama_supplier ?? '-'),
                    'tanggal_job' => \Carbon\Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    'posisi_km' => $transaction->posisi_km,
                    'nomor_chassis' => $transaction->mobil->nomor_chassis ?? '-',
                    'nomor_polisi' => $transaction->mobil->nomor_polisi ?? '-',
                    'maintenance_service' => $maintenanceService,
                    'workshop_harent' => $workshopHarent,
                    'deskripsi' => '-',
                    'jumlah' => '-',
                    'harga' => '-',
                    'harga_total' => number_format($transaction->harga_total ?? 0, 0, ',', '.'),
                    'harga_pajak' => number_format($transaction->harga_pajak ?? 0, 0, ',', '.'),
                    'keterangan' => $transaction->keterangan ?? '-',
                    'tanggal_close' => \Carbon\Carbon::parse($transaction->tanggal_close)->format('d-m-Y'),
                    'is_detail' => false
                ];
            }
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'grandTotals' => $this->calculateGrandTotals($request->nama_customer, $request->nomor_polisi, $request->start_date_transaksi, $request->end_date_transaksi, $search)
        ]);
    }

    public function vehicleTransactionsExport(Request $request)
    {
        $nama_customer = $request->nama_customer;
        $nomor_polisi = $request->nomor_polisi;
        $start = $request->start_date_transaksi;
        $end = $request->end_date_transaksi;

        $query = $this->buildTransactionQuery($nama_customer, $nomor_polisi, $start, $end);
        $results = $query->get();

        // Calculate totals for export
        $grandTotal = 0;
        $hargaTotal = 0;
        $hargaPajak = 0;

        $exportData = [];
        foreach ($results as $transaction) {
            $grandTotal += ($transaction->harga_total ?? 0) + ($transaction->harga_pajak ?? 0);
            $hargaTotal += $transaction->harga_total ?? 0;
            $hargaPajak += $transaction->harga_pajak ?? 0;

            $details = $transaction->dtransaksi;

            // Build nomor_job with supplier info (matching view)
            $nomorJobFull = $transaction->nomor_job . ' - ' .
                ($transaction->supplier->kode_supplier ?? '-') . ' - ' .
                ($transaction->supplier->nama_supplier ?? '-');

            // Determine workshop harent
            $kodeSup = $transaction->kode_sup ?? '';
            $workshopHarent = (strpos($kodeSup, '*INTERNAL') !== false) ? 'Workshop Harent' : '';
            
            // Determine maintenance/service
            $nomorSv = $transaction->nomor_sv ?? '';
            $maintenanceService = (trim($nomorSv) === 'M') ? 'Maintenance' : $nomorSv;

            if ($details->count() > 0) {
                // First row with first detail
                $exportData[] = [
                    'nomor_job' => $nomorJobFull,
                    'tanggal_job' => \Carbon\Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    'posisi_km' => $transaction->posisi_km,
                    'nomor_chassis' => $transaction->mobil->nomor_chassis ?? '-',
                    'nomor_polisi' => $transaction->mobil->nomor_polisi ?? '-',
                    'workshop_harent' => $workshopHarent,
                    'maintenance_service' => $maintenanceService,
                    'deskripsi' => $details->first()->deskripsi ?? '-',
                    'jumlah' => $details->first()->jumlah ?? '-',
                    'harga' => $details->first()->harga ?? 0,
                    'harga_total' => $transaction->harga_total ?? 0,
                    'harga_pajak' => $transaction->harga_pajak ?? 0,
                    'keterangan' => $details->first()->keterangan ?? ($transaction->keterangan ?? '-'),
                    'tanggal_close' => \Carbon\Carbon::parse($transaction->tanggal_close)->format('d-m-Y'),
                    'is_detail' => false
                ];

                // Additional detail rows (skip first)
                foreach ($details->skip(1) as $detail) {
                    $exportData[] = [
                        'nomor_job' => '',
                        'tanggal_job' => '',
                        'posisi_km' => '',
                        'nomor_chassis' => '',
                        'workshop_harent' => '',
                        'maintenance_service' => '',
                        'deskripsi' => $detail->deskripsi ?? '-',
                        'jumlah' => $detail->jumlah ?? '-',
                        'harga' => $detail->harga ?? 0,
                        'harga_total' => '',
                        'harga_pajak' => '',
                        'keterangan' => '',
                        'tanggal_close' => \Carbon\Carbon::parse($transaction->tanggal_close)->format('d-m-Y'),
                        'is_detail' => true
                    ];
                }
            } else {
                $exportData[] = [
                    'nomor_job' => $nomorJobFull,
                    'tanggal_job' => \Carbon\Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    'posisi_km' => $transaction->posisi_km,
                    'nomor_chassis' => $transaction->mobil->nomor_chassis ?? '-',
                    'nomor_polisi' => $transaction->mobil->nomor_polisi ?? '-',
                    'workshop_harent' => $workshopHarent,
                    'maintenance_service' => $maintenanceService,
                    'deskripsi' => '-',
                    'jumlah' => '-',
                    'harga' => '-',
                    'harga_total' => $transaction->harga_total ?? 0,
                    'harga_pajak' => $transaction->harga_pajak ?? 0,
                    'keterangan' => $transaction->keterangan ?? '-',
                    'tanggal_close' => \Carbon\Carbon::parse($transaction->tanggal_close)->format('d-m-Y'),
                    'is_detail' => false
                ];
            }
        }

        return response()->json([
            'data' => $exportData,
            'grandTotal' => $grandTotal,
            'hargaTotal' => $hargaTotal,
            'hargaPajak' => $hargaPajak,
            'customer' => $nama_customer,
            'nomor_polisi' => $nomor_polisi
        ]);
    }

    private function buildTransactionQuery($nama_customer, $nomor_polisi, $start, $end)
{
    $query = htransaksi::with(['mobil', 'supplier', 'dtransaksi']);

    if ($nama_customer) {
        $customer = customer::where('kode_customer', $nama_customer)->first();
        if ($customer) {
            $query->where('id_customer', $customer->id);
        } else {
            // If a customer was provided but not found, return an empty query
            return htransaksi::whereRaw('1 = 0');
        }
    }

    if ($nomor_polisi) {
        $query->whereHas('mobil', function ($q) use ($nomor_polisi) {
            $q->where('nomor_polisi', $nomor_polisi);
        });
    }

    if ($start && $end) {
        $query->whereBetween('tanggal_job', [$start, $end]);
    } elseif ($start) {
        $query->where('tanggal_job', '>=', $start);
    } elseif ($end) {
        $query->where('tanggal_job', '<=', $end);
    }

    $query->orderBy('tanggal_job', 'desc');

    return $query;
}
    private function calculateGrandTotals($nama_customer, $nomor_polisi, $start, $end, $search = null)
    {
        $query = htransaksi::query();

        if ($nama_customer) {
            $customer = customer::where('kode_customer', $nama_customer)->first();
            if ($customer) {
                $query->where('id_customer', $customer->id);
            } else {
                return [
                    'hargaPajak' => 0,
                    'grandTotal' => 0,
                ];
            }
        }

        if ($nomor_polisi) {
            $query->whereHas('mobil', function ($q) use ($nomor_polisi) {
                $q->where('nomor_polisi', $nomor_polisi);
            });
        }

        if ($start && $end) {
            $query->whereBetween('tanggal_job', [$start, $end]);
        } elseif ($start) {
            $query->where('tanggal_job', '>=', $start);
        } elseif ($end) {
            $query->where('tanggal_job', '<=', $end);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_job', 'like', "%{$search}%")
                  ->orWhere('nomor_chassis', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('mobil', function ($mq) use ($search) {
                      $mq->where('nomor_polisi', 'like', "%{$search}%");
                  });
            });
        }

        $totals = $query->selectRaw('
            SUM(harga_total) as total_harga,
            SUM(harga_pajak) as total_pajak,
            SUM(COALESCE(harga_total, 0) + COALESCE(harga_pajak, 0)) as grand_total
        ')->first();

        return [
            'hargaTotal' => (float) ($totals->total_harga ?? 0),
            'hargaPajak' => (float) ($totals->total_pajak ?? 0),
            'grandTotal' => (float) ($totals->grand_total ?? 0),
        ];
    }

    public function repairJobs(Request $request)
    {
        $openStates = ['confirmed', 'under_repair', 'ready'];
        $closedStates = ['done', '2binvoiced'];

        $totalJobs = htransaksi::count();
        $openJobs = htransaksi::whereIn('state', $openStates)->count();
        $closedJobs = htransaksi::whereIn('state', $closedStates)->count();

        return view('repair-jobs', compact('totalJobs', 'openJobs', 'closedJobs'));
    }

    public function repairJobsData(Request $request)
    {
        $openStates = ['confirmed', 'under_repair', 'ready'];
        $closedStates = ['done', '2binvoiced'];

        $query = htransaksi::with(['mobil', 'supplier']);

        // Status filter
        $statusFilter = $request->input('status', 'all');
        if ($statusFilter === 'open') {
            $query->whereIn('state', $openStates);
        } elseif ($statusFilter === 'closed') {
            $query->whereIn('state', $closedStates);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('tanggal_job', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal_job', '<=', $request->end_date);
        }

        // Customer filter
        if ($request->filled('customer')) {
            $customerName = \App\Models\customer::where('kode_customer', $request->customer)->value('nama_customer');
            if ($customerName) {
                // Older Foxpro data or current data
                $query->where(function($q) use ($request, $customerName) {
                     $q->whereHas('mobil', function($mq) use ($request) {
                         $mq->where('customer', $request->customer);
                     })
                     ->orWhere('keterangan', 'like', "%{$customerName}%");
                });
            } else {
                 $query->whereHas('mobil', function($mq) use ($request) {
                     $mq->where('customer', $request->customer);
                 });
            }
        }

        // Nomor Polisi filter
        if ($request->filled('nomor_polisi')) {
            $nomorPolisi = $request->nomor_polisi;
            $chassis = \App\Models\mobil::where('nomor_polisi', $nomorPolisi)->value('nomor_chassis');
            
            $query->where(function($q) use ($nomorPolisi, $chassis) {
                $q->whereHas('mobil', function($mq) use ($nomorPolisi) {
                    $mq->where('nomor_polisi', $nomorPolisi);
                });
                
                if ($chassis) {
                    $q->orWhere('nomor_chassis', $chassis);
                }
            });
        }

        $recordsTotal = $query->count();

        // DataTables search
        $search = $request->input('search.value', '');
        if ($search) {
            // Pre-fetch matching chassis for vehicles to massively speed up search
            $mobilChassisList = \App\Models\mobil::where('nomor_polisi', 'like', "%{$search}%")
                ->pluck('nomor_chassis')
                ->filter()
                ->toArray();

            $query->where(function ($q) use ($search, $mobilChassisList) {
                $q->where('nomor_job', 'like', "%{$search}%")
                  ->orWhere('nomor_chassis', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
                  
                if (!empty($mobilChassisList)) {
                    $q->orWhereIn('nomor_chassis', $mobilChassisList);
                }
            });
        }

        $recordsFiltered = $query->count();

        // Ordering
        $orderCol = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['nomor_job', 'tanggal_job', 'state', 'nomor_job', 'nomor_job', 'harga_total', 'tanggal_close'];
        $sortBy = $columns[$orderCol] ?? 'tanggal_job';
        $query->orderBy($sortBy, $orderDir);

        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $results = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($results as $job) {
            $state = $job->state ?? 'done';
            $isOpen = in_array($state, $openStates);

            // Calculate days open for open jobs
            $daysOpen = null;
            if ($isOpen && $job->tanggal_job) {
                $daysOpen = intval(\Carbon\Carbon::parse($job->tanggal_job)->diffInDays(now()));
            }

            $stateLabel = match($state) {
                'confirmed' => 'Confirmed',
                'under_repair' => 'Under Repair',
                'ready' => 'Ready',
                '2binvoiced' => 'To Invoice',
                'done' => 'Done',
                default => ucfirst($state),
            };

            $data[] = [
                'nomor_job' => $job->nomor_job,
                'tanggal_job' => $job->tanggal_job ? \Carbon\Carbon::parse($job->tanggal_job)->format('d-m-Y') : '-',
                'nomor_polisi' => $job->mobil->nomor_polisi ?? '-',
                'nomor_chassis' => $job->mobil->nomor_chassis ?? '-',
                'model' => $job->mobil->model ?? '-',
                'supplier' => $job->supplier->nama_supplier ?? '-',
                'service_type' => $job->nomor_sv ?? '-',
                'state' => $state,
                'state_label' => $stateLabel,
                'is_open' => $isOpen,
                'days_open' => $daysOpen,
                'harga_total' => number_format($job->harga_total ?? 0, 0, ',', '.'),
                'harga_total_raw' => $job->harga_total ?? 0,
                'harga_pajak' => number_format($job->harga_pajak ?? 0, 0, ',', '.'),
                'keterangan' => $job->keterangan ?? '-',
                'tanggal_close' => $job->tanggal_close ? \Carbon\Carbon::parse($job->tanggal_close)->format('d-m-Y') : '-',
                'posisi_km' => $job->posisi_km ?? 0,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    public function repairJobDetails($nomor_job)
    {
        $job = htransaksi::with(['dtransaksi', 'mobil', 'supplier'])->where('nomor_job', $nomor_job)->first();
        
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        return view('maintenance.job-details', compact('job'));
    }
}

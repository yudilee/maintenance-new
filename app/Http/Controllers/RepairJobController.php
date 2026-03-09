<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Htransaksi;

class RepairJobController extends Controller
{
    public function index(Request $request)
    {
        $openStates = ['confirmed', 'under_repair', 'ready'];
        $closedStates = ['done', '2binvoiced'];

        $totalJobs = Htransaksi::count();
        $openJobs = Htransaksi::whereIn('state', $openStates)->count();
        $closedJobs = Htransaksi::whereIn('state', $closedStates)->count();

        // Individual state counts
        $confirmedJobs = Htransaksi::where('state', 'confirmed')->count();
        $underRepairJobs = Htransaksi::where('state', 'under_repair')->count();
        $readyJobs = Htransaksi::where('state', 'ready')->count();
        $doneJobs = Htransaksi::where('state', 'done')->count();
        $toInvoiceJobs = Htransaksi::where('state', '2binvoiced')->count();

        return view('repair-jobs', compact(
            'totalJobs', 'openJobs', 'closedJobs',
            'confirmedJobs', 'underRepairJobs', 'readyJobs',
            'doneJobs', 'toInvoiceJobs'
        ));
    }

    public function data(Request $request)
    {
        $openStates = ['confirmed', 'under_repair', 'ready'];
        $closedStates = ['done', '2binvoiced'];

        $query = Htransaksi::with(['mobil', 'supplier']);

        // Status filter
        $statusFilter = $request->input('status', 'all');
        if ($statusFilter === 'open') {
            $query->whereIn('state', $openStates);
        } elseif ($statusFilter === 'closed') {
            $query->whereIn('state', $closedStates);
        } elseif ($statusFilter !== 'all' && $statusFilter !== '') {
            // Individual state filter (e.g., 'confirmed', 'under_repair', etc.)
            $query->where('state', $statusFilter);
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
            $customer = \App\Models\Customer::where('kode_customer', $request->customer)->first();
            if ($customer) {
                $query->where('id_customer', $customer->id);
            }
        }

        // Nomor Polisi filter
        if ($request->filled('nomor_polisi')) {
            $nomorPolisi = $request->nomor_polisi;
            $chassis = \App\Models\Mobil::where('nomor_polisi', $nomorPolisi)->value('nomor_chassis');
            
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
        if (!empty($search)) {
            $mobilChassisList = \App\Models\Mobil::where('nomor_polisi', 'like', "%{$search}%")
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

    public function details($nomor_job)
    {
        $job = Htransaksi::with(['dtransaksi', 'mobil', 'supplier'])->where('nomor_job', $nomor_job)->first();
        
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        return view('maintenance.job-details', compact('job'));
    }
}

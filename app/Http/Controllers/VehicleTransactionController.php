<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Htransaksi;
use App\Models\Mobil;

class VehicleTransactionController extends Controller
{
    public function index(Request $request)
    {
        $mobilDetail = null;
        $nama_customer = $request->nama_customer;
        $nomor_polisi = $request->nomor_polisi;
        $start = $request->start_date_transaksi;
        $end = $request->end_date_transaksi;

        if ($nomor_polisi) {
            $mobilDetail = Mobil::where('nomor_polisi', $nomor_polisi)->first();
        }

        // Get grand totals for all transactions (not just current page)
        $grandTotals = $this->calculateGrandTotals($nama_customer, $nomor_polisi, $start, $end);

        return view('transaksi', compact('mobilDetail', 'nama_customer', 'nomor_polisi', 'grandTotals'));
    }

    public function data(Request $request)
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

    public function export(Request $request)
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
        $closedStates = ['done', '2binvoiced'];
        $query = Htransaksi::with(['mobil', 'supplier', 'dtransaksi'])
            ->where(function($q) use ($closedStates) {
                $q->whereIn('state', $closedStates)
                  ->orWhereNull('state')
                  ->orWhere('state', '');
            });

        if ($nama_customer) {
            $customer = Customer::where('kode_customer', $nama_customer)->first();
            if ($customer) {
                $query->where('id_customer', $customer->id);
            } else {
                return Htransaksi::whereRaw('1 = 0');
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
        $closedStates = ['done', '2binvoiced'];
        $query = Htransaksi::where(function($q) use ($closedStates) {
            $q->whereIn('state', $closedStates)
              ->orWhereNull('state')
              ->orWhere('state', '');
        });

        if ($nama_customer) {
            $customer = Customer::where('kode_customer', $nama_customer)->first();
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
}

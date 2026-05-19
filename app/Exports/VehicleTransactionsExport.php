<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class VehicleTransactionsExport implements FromCollection, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function headings(): array
    {
        return [
            'Nomor Job',
            'Tanggal Job',
            'Posisi KM',
            'Nomor Chassis',
            'Nomor Polisi',
            'Workshop Harent',
            'Maintenance/Service',
            'Deskripsi',
            'Jumlah',
            'Harga',
            'Harga Total',
            'Harga Pajak',
            'Keterangan',
            'Tanggal Close'
        ];
    }

    public function collection()
    {
        $results = $this->query->get();

        $grandTotal = 0;
        $hargaTotal = 0;
        $hargaPajak = 0;

        $exportData = [];
        foreach ($results as $transaction) {
            $grandTotal += ($transaction->harga_total ?? 0) + ($transaction->harga_pajak ?? 0);
            $hargaTotal += $transaction->harga_total ?? 0;
            $hargaPajak += $transaction->harga_pajak ?? 0;

            $details = $transaction->dtransaksi;

            $nomorJobFull = $transaction->nomor_job . ' - ' .
                ($transaction->supplier->kode_supplier ?? '-') . ' - ' .
                ($transaction->supplier->nama_supplier ?? '-');

            $kodeSup = $transaction->kode_sup ?? '';
            $workshopHarent = (strpos($kodeSup, '*INTERNAL') !== false) ? 'Workshop Harent' : '';
            
            $nomorSv = $transaction->nomor_sv ?? '';
            $maintenanceService = (trim($nomorSv) === 'M') ? 'Maintenance' : $nomorSv;

            if ($details->count() > 0) {
                // First line
                $firstDetail = $details->first();
                $exportData[] = [
                    $nomorJobFull,
                    Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    $transaction->posisi_km,
                    $transaction->mobil->nomor_chassis ?? '-',
                    $transaction->mobil->nomor_polisi ?? '-',
                    $workshopHarent,
                    $maintenanceService,
                    $firstDetail->deskripsi ?? '-',
                    $firstDetail->jumlah ?? '-',
                    $firstDetail->harga ?? 0,
                    $transaction->harga_total ?? 0,
                    $transaction->harga_pajak ?? 0,
                    $firstDetail->keterangan ?? ($transaction->keterangan ?? '-'),
                    Carbon::parse($transaction->tanggal_close)->format('d-m-Y')
                ];

                // Subsequent lines
                foreach ($details->skip(1) as $detail) {
                    $isTaxLine = ($detail->note === 'tax');
                    $exportData[] = [
                        '', // nomor_job
                        '', // tanggal_job
                        '', // posisi_km
                        '', // nomor_chassis
                        '', // nomor_polisi
                        '', // workshop_harent
                        '', // maintenance_service
                        $detail->deskripsi ?? '-',
                        $isTaxLine ? '' : ($detail->jumlah ?? '-'),
                        $isTaxLine ? '' : ($detail->harga ?? 0),
                        '', // harga_total
                        $isTaxLine ? ($detail->value ?? 0) : '', // harga_pajak (tax line goes here)
                        '', // keterangan
                        Carbon::parse($transaction->tanggal_close)->format('d-m-Y')
                    ];
                }
            } else {
                $exportData[] = [
                    $nomorJobFull,
                    Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    $transaction->posisi_km,
                    $transaction->mobil->nomor_chassis ?? '-',
                    $transaction->mobil->nomor_polisi ?? '-',
                    $workshopHarent,
                    $maintenanceService,
                    '-', // deskripsi
                    '-', // jumlah
                    '-', // harga
                    $transaction->harga_total ?? 0,
                    $transaction->harga_pajak ?? 0,
                    $transaction->keterangan ?? '-',
                    Carbon::parse($transaction->tanggal_close)->format('d-m-Y')
                ];
            }
        }

        // Add Grand Total Row
        $exportData[] = [
            '', '', '', '', '', '', '', '', '', '', 
            (float) $hargaTotal, 
            (float) $hargaPajak, 
            'GRAND TOTAL: ' . (float) $grandTotal,
            ''
        ];

        return collect($exportData);
    }
}

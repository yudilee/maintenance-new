<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class VehicleTransactionsExport implements FromCollection, WithHeadings
{
    protected $query;
    protected $includeTanggalUpdate;

    public function __construct($query, $includeTanggalUpdate = false)
    {
        $this->query = $query;
        $this->includeTanggalUpdate = $includeTanggalUpdate;
    }

    public function headings(): array
    {
        $headings = [
            'Nomor Job',
            'Tanggal Job',
            'Posisi KM',
            'Nomor Chassis',
            'Nomor Polisi',
            'Tipe',
            'Workshop Harent',
            'Maintenance/Service',
            'Product',
            'Deskripsi',
        ];

        if ($this->includeTanggalUpdate) {
            $headings[] = 'Tanggal Update';
        }

        return array_merge($headings, [
            'Jumlah',
            'Harga',
            'Harga Total',
            'Harga Pajak',
            'Keterangan',
            'Tanggal Close'
        ]);
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

            $tipe = $transaction->is_internal ? 'Internal' : 'External';

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
                $row = [
                    $nomorJobFull,
                    Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    $transaction->posisi_km,
                    $transaction->mobil->nomor_chassis ?? '-',
                    $transaction->mobil->nomor_polisi ?? '-',
                    $tipe,
                    $workshopHarent,
                    $maintenanceService,
                    $firstDetail->product ?? '',
                    $firstDetail->deskripsi ?? '-',
                ];

                if ($this->includeTanggalUpdate) {
                    $row[] = $firstDetail->tanggal_part_keluar ? Carbon::parse($firstDetail->tanggal_part_keluar)->format('d-m-Y H:i:s') : '';
                }

                $row = array_merge($row, [
                    $firstDetail->jumlah ?? '-',
                    $firstDetail->harga ?? 0,
                    $transaction->harga_total ?? 0,
                    $transaction->harga_pajak ?? 0,
                    $firstDetail->keterangan ?? ($transaction->keterangan ?? '-'),
                    Carbon::parse($transaction->tanggal_close)->format('d-m-Y')
                ]);

                $exportData[] = $row;

                // Subsequent lines
                foreach ($details->skip(1) as $detail) {
                    $isTaxLine = ($detail->note === 'tax');
                    $row = [
                        '', // nomor_job
                        '', // tanggal_job
                        '', // posisi_km
                        '', // nomor_chassis
                        '', // nomor_polisi
                        '', // tipe
                        '', // workshop_harent
                        '', // maintenance_service
                        $detail->product ?? '', // product
                        $detail->deskripsi ?? '-',
                    ];

                    if ($this->includeTanggalUpdate) {
                        $row[] = $detail->tanggal_part_keluar ? Carbon::parse($detail->tanggal_part_keluar)->format('d-m-Y H:i:s') : '';
                    }

                    $row = array_merge($row, [
                        $isTaxLine ? '' : ($detail->jumlah ?? '-'),
                        $isTaxLine ? '' : ($detail->harga ?? 0),
                        '', // harga_total
                        $isTaxLine ? ($detail->value ?? 0) : '', // harga_pajak (tax line goes here)
                        '', // keterangan
                        Carbon::parse($transaction->tanggal_close)->format('d-m-Y')
                    ]);

                    $exportData[] = $row;
                }
            } else {
                $row = [
                    $nomorJobFull,
                    Carbon::parse($transaction->tanggal_job)->format('d-m-Y'),
                    $transaction->posisi_km,
                    $transaction->mobil->nomor_chassis ?? '-',
                    $transaction->mobil->nomor_polisi ?? '-',
                    $tipe,
                    $workshopHarent,
                    $maintenanceService,
                    '', // product
                    '-', // deskripsi
                ];

                if ($this->includeTanggalUpdate) {
                    $row[] = '';
                }

                $row = array_merge($row, [
                    '-', // jumlah
                    '-', // harga
                    $transaction->harga_total ?? 0,
                    $transaction->harga_pajak ?? 0,
                    $transaction->keterangan ?? '-',
                    Carbon::parse($transaction->tanggal_close)->format('d-m-Y')
                ]);

                $exportData[] = $row;
            }
        }

        // Add Grand Total Row
        $numPadding = $this->includeTanggalUpdate ? 13 : 12;
        $grandTotalRow = array_fill(0, $numPadding, '');
        $grandTotalRow = array_merge($grandTotalRow, [
            (float) $hargaTotal, 
            (float) $hargaPajak, 
            'GRAND TOTAL: ' . (float) $grandTotal,
            ''
        ]);
        $exportData[] = $grandTotalRow;

        return collect($exportData);
    }
}

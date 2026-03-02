<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class htransaksi extends Model
{
    use HasFactory;

    //Nama Table
    protected $table = 'htransaksi';
    public $timestamps = false; // <--- Add this line

    protected $fillable = [
        'id',
        'id_customer',
        'nomor_job',
        'tanggal_job',
        'nomor_chassis',
        'nomor_invoice',
        'sup_invoice',
        'tanggal_invoice',
        'pajak',
        'kode_sup',
        'harga_part',
        'harga_oli',
        'harga_lbr',
        'harga_oth',
        'harga_total',
        'harga_pajak',
        'harga_jual',
        'harga_pajak_jual',
        'mtrs',
        'keterangan',
        'kode_servis',
        'nomor_req',
        'posisi_km',
        'nomor_sv',
        'tanggal_close',
        'state',
    ];

    public function dtransaksi()
    {
        return $this->hasMany(dtransaksi::class, 'nomor_invoice', 'nomor_invoice');
    }

    public function mobil()
    {
        return $this->belongsTo(mobil::class, 'nomor_chassis', 'nomor_chassis');
    }

    public function supplier()
    {
        return $this->belongsTo(supplier::class, 'kode_sup', 'kode_supplier');
    }
}

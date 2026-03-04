<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    use HasFactory;

    protected $table = 'mobil';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nomor_kk',
        'nomor_chassis',
        'nomor_polisi',
        'nopol',
        'model',
        'tahun_pembuatan',
        'warna',
        'nomor_mesin',
        'tanggal_pembelian',
        'kode_sup',
    ];

    public function getNomorChassisAttribute($value)
    {
        return trim($value);
    }
}

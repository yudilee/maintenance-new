<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dtransaksi extends Model
{
    use HasFactory;

    protected $table = 'dtransaksi';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nomor_invoice',
        'mnt_grp',
        'deskripsi',
        'note',
        'jumlah',
        'harga',
        'discount',
        'value',
        'lbr_grp',
    ];

    public function htransaksi()
    {
        return $this->belongsTo(Htransaksi::class, 'nomor_invoice', 'nomor_invoice');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dtransaksi extends Model
{
    use HasFactory;

    //Nama Table
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
}

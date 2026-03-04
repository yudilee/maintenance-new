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
        'nomor_chassis',
        'nomor_polisi',
        'model',
        'merk',
        'tahun',
        'warna',
        'id_customer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id');
    }
}

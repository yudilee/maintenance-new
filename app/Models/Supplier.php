<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'kode_supplier',
        'nama_supplier',
    ];

    public function htransaksis()
    {
        return $this->hasMany(Htransaksi::class, 'kode_sup', 'kode_supplier');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class supplier extends Model
{
    //Nama Table
    protected $table = 'supplier';
    public $timestamps = false; // <--- Add this line

    protected $fillable = [
        'id',
        'kode_supplier',
        'nama_supplier',
    ];
}

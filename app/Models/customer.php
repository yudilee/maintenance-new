<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    //Nama Table
    protected $table = 'customer';
    public $timestamps = false; // <--- Add this line

    protected $fillable = [
        'id',
        'kode_customer',
        'nama_customer',
    ];
}

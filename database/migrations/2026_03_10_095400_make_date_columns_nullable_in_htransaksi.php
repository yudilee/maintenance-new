<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE htransaksi MODIFY tanggal_job DATE NULL, MODIFY tanggal_invoice DATE NULL, MODIFY tanggal_close DATE NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE htransaksi MODIFY tanggal_job DATE NOT NULL, MODIFY tanggal_invoice DATE NOT NULL, MODIFY tanggal_close DATE NOT NULL');
    }
};

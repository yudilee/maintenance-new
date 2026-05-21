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
        Schema::table('dtransaksi', function (Blueprint $table) {
            $table->dateTime('tanggal_part_keluar')->nullable()->after('deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtransaksi', function (Blueprint $table) {
            $table->dropColumn('tanggal_part_keluar');
        });
    }
};

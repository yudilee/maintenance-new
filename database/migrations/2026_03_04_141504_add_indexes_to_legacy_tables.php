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
        Schema::table('htransaksi', function (Blueprint $table) {
            $table->index('nomor_chassis');
            $table->index('tanggal_job');
            $table->index('nomor_job');
            $table->index('id_customer');
        });

        Schema::table('mobil', function (Blueprint $table) {
            $table->index('nomor_chassis');
            $table->index('nomor_polisi');
        });

        Schema::table('dtransaksi', function (Blueprint $table) {
            $table->index('nomor_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('htransaksi', function (Blueprint $table) {
            $table->dropIndex(['nomor_chassis']);
            $table->dropIndex(['tanggal_job']);
            $table->dropIndex(['nomor_job']);
            $table->dropIndex(['id_customer']);
        });

        Schema::table('mobil', function (Blueprint $table) {
            $table->dropIndex(['nomor_chassis']);
            $table->dropIndex(['nomor_polisi']);
        });

        Schema::table('dtransaksi', function (Blueprint $table) {
            $table->dropIndex(['nomor_invoice']);
        });
    }
};

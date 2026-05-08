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
        if (!Schema::hasTable('htransaksi')) {
            Schema::create('htransaksi', function (Blueprint $table) {
                $table->id();
                $table->integer('id_customer')->nullable();
                $table->string('nomor_job')->nullable();
                $table->date('tanggal_job')->nullable();
                $table->string('nomor_chassis')->nullable();
                $table->string('nomor_invoice')->nullable();
                $table->string('sup_invoice')->nullable();
                $table->date('tanggal_invoice')->nullable();
                $table->string('pajak')->nullable();
                $table->string('kode_sup')->nullable();
                $table->decimal('harga_part', 20, 2)->default(0);
                $table->decimal('harga_oli', 20, 2)->default(0);
                $table->decimal('harga_lbr', 20, 2)->default(0);
                $table->decimal('harga_oth', 20, 2)->default(0);
                $table->decimal('harga_total', 20, 2)->default(0);
                $table->decimal('harga_pajak', 20, 2)->default(0);
                $table->decimal('harga_jual', 20, 2)->default(0);
                $table->decimal('harga_pajak_jual', 20, 2)->default(0);
                $table->string('mtrs')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('kode_servis')->nullable();
                $table->string('nomor_req')->nullable();
                $table->string('posisi_km')->nullable();
                $table->string('nomor_sv')->nullable();
                $table->date('tanggal_close')->nullable();
                $table->string('state')->nullable();
            });
        }

        if (!Schema::hasTable('dtransaksi')) {
            Schema::create('dtransaksi', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_invoice')->nullable();
                $table->string('mnt_grp')->nullable();
                $table->text('deskripsi')->nullable();
                $table->text('note')->nullable();
                $table->decimal('jumlah', 20, 2)->default(0);
                $table->decimal('harga', 20, 2)->default(0);
                $table->decimal('discount', 20, 2)->default(0);
                $table->decimal('value', 20, 2)->default(0);
                $table->string('lbr_grp')->nullable();
            });
        }

        if (!Schema::hasTable('mobil')) {
            Schema::create('mobil', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_kk')->nullable();
                $table->string('nomor_chassis')->nullable();
                $table->string('nomor_polisi')->nullable();
                $table->string('nopol')->nullable();
                $table->string('model')->nullable();
                $table->string('tahun_pembuatan')->nullable();
                $table->string('warna')->nullable();
                $table->string('nomor_mesin')->nullable();
                $table->date('tanggal_pembelian')->nullable();
                $table->string('kode_sup')->nullable();
            });
        }

        if (!Schema::hasTable('customer')) {
            Schema::create('customer', function (Blueprint $table) {
                $table->id();
                $table->string('kode_customer')->nullable();
                $table->string('nama_customer')->nullable();
            });
        }

        if (!Schema::hasTable('supplier')) {
            Schema::create('supplier', function (Blueprint $table) {
                $table->id();
                $table->string('kode_supplier')->nullable();
                $table->string('nama_supplier')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('htransaksi');
        Schema::dropIfExists('dtransaksi');
        Schema::dropIfExists('mobil');
        Schema::dropIfExists('customer');
        Schema::dropIfExists('supplier');
    }
};

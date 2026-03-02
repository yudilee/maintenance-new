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
            $table->bigInteger('harga_jual')->default(0)->after('harga_pajak');
            $table->bigInteger('harga_pajak_jual')->default(0)->after('harga_jual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('htransaksi', function (Blueprint $table) {
            //
        });
    }
};

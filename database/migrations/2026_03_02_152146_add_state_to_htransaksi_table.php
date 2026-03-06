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
        if (Schema::hasTable('htransaksi')) {
            Schema::table('htransaksi', function (Blueprint $table) {
                if (!Schema::hasColumn('htransaksi', 'state')) {
                    $table->string('state')->default('draft')->after('harga_pajak_jual');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('htransaksi', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};

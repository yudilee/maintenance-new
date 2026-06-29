<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Increase model column from varchar(50) to varchar(100)
     * to accommodate Odoo product names (e.g. "[MTH-XPANE15-AT-B] MITSUBISHI NEW XPANDER EXCEED 1.5 CVT BENSIN")
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE mobil MODIFY COLUMN model VARCHAR(100)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mobil MODIFY COLUMN model VARCHAR(50)");
    }
};

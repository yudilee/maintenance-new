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
        Schema::table('items', function (Blueprint $table) {
            $table->string('last_customer')->nullable()->after('rental_type'); // Partner/Cust.
            $table->string('current_customer')->nullable()->after('last_customer'); // Rental ID/Customer
            $table->string('warehouse')->nullable()->after('current_customer'); // Rental ID/Warehouse
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['last_customer', 'current_customer', 'warehouse']);
        });
    }
};

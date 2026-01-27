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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            $table->float('sdp_stock')->default(0);
            $table->float('in_stock')->default(0);
            $table->float('rented')->default(0);
            $table->float('in_service')->default(0);
            $table->json('summary_json')->nullable(); // Store detailed breakdown if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};

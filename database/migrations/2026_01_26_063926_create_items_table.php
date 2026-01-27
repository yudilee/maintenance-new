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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('product')->nullable();
            $table->string('lot_number')->nullable()->index(); // Index for faster searching
            $table->string('internal_reference')->nullable();
            $table->string('year')->nullable();
            $table->string('location')->nullable()->index();
            $table->float('on_hand_quantity')->default(0);
            
            // Boolean flags
            $table->boolean('is_vendor_rent')->default(false);
            $table->boolean('is_on_hand')->default(false);
            $table->boolean('is_stock')->default(false); // Renamed from in_stock to avoid confusion? No, keep in_stock to match code.
            $table->boolean('in_stock')->default(false);
            $table->boolean('is_sold')->default(false);
            $table->boolean('is_active_rental')->default(false);

            // Rental Info
            $table->string('rental_id')->nullable()->index();
            $table->string('reserved_lot')->nullable();
            $table->string('rental_type')->nullable();
            
            // Dates
            $table->date('actual_start_rental')->nullable();
            $table->date('actual_end_rental')->nullable();
            
            // Calculated/Helpers
            $table->float('km_last')->nullable();
            $table->string('vehicle_role')->nullable(); // Main vs Replacement
            $table->integer('rental_id_count')->default(0);
            $table->text('linked_vehicle')->nullable(); // CSV of other lots
            $table->json('category_flags')->nullable(); // Tags like 'vendor_rent', 'stock_pure'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

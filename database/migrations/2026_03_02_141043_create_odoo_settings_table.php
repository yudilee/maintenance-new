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
        Schema::create('odoo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('odoo_url')->nullable();
            $table->string('database')->nullable();
            $table->string('user_email')->nullable();
            $table->string('api_key')->nullable();
            $table->boolean('enable_auto_sync')->default(false);
            $table->string('sync_interval')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odoo_settings');
    }
};

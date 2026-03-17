<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Odoo Auto-Sync Schedule
// Wrapped in try-catch to prevent errors during Docker build (when DB doesn't exist)
try {
    $odooSetting = \App\Models\OdooSetting::first();
    
    if ($odooSetting && $odooSetting->enable_auto_sync) {
        $interval = $odooSetting->sync_interval ?: 'daily';
        
        $command = Schedule::command('odoo:sync');
        
        match ($interval) {
            'hourly' => $command->hourly(),
            'every_2_hours' => $command->everyTwoHours(),
            'every_4_hours' => $command->everyFourHours(),
            'every_6_hours' => $command->everySixHours(),
            'every_12_hours' => $command->twiceDaily(0, 12),
            'daily' => $command->daily(),
            default => $command->daily(),
        };
    }
} catch (\Exception $e) {
    // Database not available (e.g., during Docker build) - skip scheduling
}

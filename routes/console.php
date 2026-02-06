<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Odoo Auto-Sync Schedule
// Read schedule configuration from settings
$scheduleEnabled = Setting::getValue('odoo_schedule_enabled', 'false') === 'true';
$scheduleInterval = Setting::getValue('odoo_schedule_interval', 'daily');

if ($scheduleEnabled) {
    $command = Schedule::command('odoo:sync');
    
    match ($scheduleInterval) {
        'hourly' => $command->hourly(),
        'every_2_hours' => $command->everyTwoHours(),
        'every_4_hours' => $command->everyFourHours(),
        'every_6_hours' => $command->everySixHours(),
        'every_12_hours' => $command->twiceDaily(0, 12),
        'daily' => $command->daily(),
        default => $command->daily(),
    };
}

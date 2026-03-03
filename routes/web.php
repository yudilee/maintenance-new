<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\OdooSettingController as MaintenanceOdooSettingController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Import Data Routes
Route::get('/import', [ImportController::class, 'index'])->name('import');
Route::post('/import/excel', [ImportController::class, 'uploadExcel'])->name('import.excel');
Route::post('/import/odoo/config', [ImportController::class, 'saveOdooConfig'])->name('import.odoo.config');
Route::post('/import/odoo/test', [ImportController::class, 'testOdooConnection'])->name('import.odoo.test');
Route::post('/import/odoo/sync', [ImportController::class, 'syncOdoo'])->name('import.odoo.sync');
Route::get('/import/odoo/schedule', [ImportController::class, 'getSchedule'])->name('import.odoo.schedule.get');
Route::post('/import/odoo/schedule', [ImportController::class, 'saveSchedule'])->name('import.odoo.schedule.save');
Route::get('/import/history', [ImportController::class, 'history'])->name('import.history');
Route::get('/details', [DashboardController::class, 'details'])->name('details');
Route::get('/export', [DashboardController::class, 'export'])->name('export');
Route::get('/print', [DashboardController::class, 'print'])->name('print');
Route::get('/rental-pairs', [DashboardController::class, 'rentalPairs'])->name('rental.pairs');
Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');
Route::get('/help', function () { return view('help'); })->name('help');
Route::post('/generate', [DashboardController::class, 'upload'])->name('summary.generate');

Route::get('/total-stock', [DashboardController::class, 'totalStock'])->name('total.stock');
Route::post('/total-stock/filter', [DashboardController::class, 'filterTotalStock'])->name('total.stock.filter');
Route::post('/total-stock/export', [DashboardController::class, 'exportTotalStock'])->name('total.stock.export');

// Settings Routes
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/targets', [SettingsController::class, 'updateTargets'])->name('settings.targets');
Route::post('/settings/odoo', [SettingsController::class, 'updateOdoo'])->name('settings.odoo');
Route::get('/api/settings/targets', [SettingsController::class, 'getTargets'])->name('api.settings.targets');

// Repair History API
Route::get('/api/repair-history/{lotNumber}', [DashboardController::class, 'repairHistory'])->name('api.repair.history');

// Maintenance (Operational Costs) Module Routes
Route::prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/', [MainController::class, 'index'])->name('dashboard');
    Route::get('/nomor-polisi-search', [MainController::class, 'searchNomorPolisi'])->name('nomor_polisi.search');
    Route::get('/nama-customer-search', [MainController::class, 'searchCustomer'])->name('nama_customer.search');
    Route::get('/vehicle-transactions', [MainController::class, 'vehicleTransactions'])->name('vehicle.transactions');
    Route::get('/vehicle-transactions-data', [MainController::class, 'vehicleTransactionsData'])->name('vehicle.transactions.data');
    Route::get('/vehicle-transactions-export', [MainController::class, 'vehicleTransactionsExport'])->name('vehicle.transactions.export');
    Route::get('/repair-jobs', [MainController::class, 'repairJobs'])->name('repair.jobs');
    Route::get('/repair-jobs-data', [MainController::class, 'repairJobsData'])->name('repair.jobs.data');
    Route::get('/repair-job-details/{nomor_job}', [MainController::class, 'repairJobDetails'])->name('repair.job.details');
    
    // Odoo settings for Maintenance
    Route::get('/odoo/settings', [MaintenanceOdooSettingController::class, 'index'])->name('odoo.settings');
    Route::post('/odoo/settings', [MaintenanceOdooSettingController::class, 'store'])->name('odoo.settings.store');
    Route::post('/odoo/test-connection', [MaintenanceOdooSettingController::class, 'testConnection'])->name('odoo.test_connection');
    Route::post('/odoo/sync-now', [MaintenanceOdooSettingController::class, 'syncNow'])->name('odoo.sync_now');
    Route::get('/odoo/sync-status', [MaintenanceOdooSettingController::class, 'syncStatus'])->name('odoo.sync_status');
});

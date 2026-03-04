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
    Route::get('/repair-job-details/{nomor_job}', [MainController::class, 'repairJobDetails'])
        ->where('nomor_job', '.*')
        ->name('repair.job.details');
    
    // Odoo settings for Maintenance
    Route::get('/odoo/settings', [MaintenanceOdooSettingController::class, 'index'])->name('odoo.settings');
    Route::post('/odoo/settings', [MaintenanceOdooSettingController::class, 'store'])->name('odoo.settings.store');
    Route::post('/odoo/test-connection', [MaintenanceOdooSettingController::class, 'testConnection'])->name('odoo.test_connection');
    Route::post('/odoo/sync-now', [MaintenanceOdooSettingController::class, 'syncNow'])->name('odoo.sync_now');
    Route::get('/odoo/sync-status', [MaintenanceOdooSettingController::class, 'syncStatus'])->name('odoo.sync_status');

    // Two-Factor Authentication
    Route::get('2fa', [\App\Http\Controllers\TwoFactorController::class, 'index'])->name('2fa.index');
    Route::post('2fa/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('2fa/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::post('2fa/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('2fa/regenerate', [\App\Http\Controllers\TwoFactorController::class, 'regenerateCodes'])->name('2fa.regenerate');
    Route::delete('sessions/{session}', [\App\Http\Controllers\TwoFactorController::class, 'terminateSession'])->name('sessions.terminate');
    Route::post('sessions/other', [\App\Http\Controllers\TwoFactorController::class, 'terminateOtherSessions'])->name('sessions.terminate-others');

    // Admin routes (requires admin role)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        
        // User Management
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Database Backups
        Route::get('backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backups.create');
        Route::get('backups/{filename}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/{filename}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
        Route::post('backups/restore-file', [\App\Http\Controllers\Admin\BackupController::class, 'restoreFromFile'])->name('backups.restore-file');
        Route::delete('backups/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backups.destroy');
        Route::post('backups/schedule', [\App\Http\Controllers\Admin\BackupController::class, 'updateSchedule'])->name('backups.schedule');
        Route::post('backups/delete-batch', [\App\Http\Controllers\Admin\BackupController::class, 'deleteBatch'])->name('backups.delete-batch');
        Route::post('backups/prune', [\App\Http\Controllers\Admin\BackupController::class, 'prune'])->name('backups.prune');

        // Session Manager
        Route::get('sessions', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('sessions.index');
        Route::post('sessions/settings', [\App\Http\Controllers\Admin\SessionController::class, 'updateSettings'])->name('sessions.settings');
        Route::post('sessions/cleanup', [\App\Http\Controllers\Admin\SessionController::class, 'cleanup'])->name('sessions.cleanup');
        Route::delete('sessions/{session}', [\App\Http\Controllers\Admin\SessionController::class, 'terminate'])->name('sessions.terminate');

        // Role Management
        Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.update-permissions');
        Route::get('roles/{role}/fields/{doctype}', [\App\Http\Controllers\Admin\RoleController::class, 'fieldPermissions'])->name('roles.field-permissions');
        Route::post('roles/{role}/fields/{doctype}', [\App\Http\Controllers\Admin\RoleController::class, 'updateFieldPermissions'])->name('roles.update-field-permissions');
    });
});

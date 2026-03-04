<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\OdooSettingController as MaintenanceOdooSettingController;

// ──────────────────────────────────────────────
// Public / Guest Routes
// ──────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::get('register', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
});

// ──────────────────────────────────────────────
// Authenticated Routes
// ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/details', [DashboardController::class, 'details'])->name('details');
    Route::get('/export', [DashboardController::class, 'export'])->name('export');
    Route::get('/print', [DashboardController::class, 'print'])->name('print');
    Route::get('/rental-pairs', [\App\Http\Controllers\RentalController::class, 'index'])->name('rental.pairs');
    Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');
    Route::post('/generate', [DashboardController::class, 'upload'])->name('summary.generate');

    // Total Stock
    Route::get('/total-stock', [\App\Http\Controllers\StockController::class, 'index'])->name('total.stock');
    Route::post('/total-stock/filter', [\App\Http\Controllers\StockController::class, 'filter'])->name('total.stock.filter');
    Route::post('/total-stock/export', [\App\Http\Controllers\StockController::class, 'export'])->name('total.stock.export');

    // Help
    Route::get('/help', function () { return view('help'); })->name('help');

    // Import Data
    Route::prefix('import')->name('import')->group(function () {
        Route::get('/', [ImportController::class, 'index']);
        Route::post('/excel', [ImportController::class, 'uploadExcel'])->name('.excel');
        Route::post('/odoo/config', [ImportController::class, 'saveOdooConfig'])->name('.odoo.config');
        Route::post('/odoo/test', [ImportController::class, 'testOdooConnection'])->name('.odoo.test');
        Route::post('/odoo/sync', [ImportController::class, 'syncOdoo'])->name('.odoo.sync');
        Route::get('/odoo/schedule', [ImportController::class, 'getSchedule'])->name('.odoo.schedule.get');
        Route::post('/odoo/schedule', [ImportController::class, 'saveSchedule'])->name('.odoo.schedule.save');
        Route::get('/history', [ImportController::class, 'history'])->name('.history');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/targets', [SettingsController::class, 'updateTargets'])->name('settings.targets');
    Route::post('/settings/odoo', [SettingsController::class, 'updateOdoo'])->name('settings.odoo');

    // JSON API endpoints (session-authenticated, called from frontend JS)
    Route::get('/api/settings/targets', [SettingsController::class, 'getTargets'])->name('api.settings.targets');
    Route::get('/api/repair-history/{lotNumber}', [\App\Http\Controllers\RepairHistoryController::class, 'show'])->name('api.repair.history');

    // ──────────────────────────────────────────
    // Maintenance (Operational Costs) Module
    // ──────────────────────────────────────────
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MainController::class, 'index'])->name('dashboard');
        Route::get('/nomor-polisi-search', [\App\Http\Controllers\SearchController::class, 'nomorPolisi'])->name('nomor_polisi.search');
        Route::get('/nama-customer-search', [\App\Http\Controllers\SearchController::class, 'customer'])->name('nama_customer.search');
        Route::get('/vehicle-transactions', [\App\Http\Controllers\VehicleTransactionController::class, 'index'])->name('vehicle.transactions');
        Route::get('/vehicle-transactions-data', [\App\Http\Controllers\VehicleTransactionController::class, 'data'])->name('vehicle.transactions.data');
        Route::get('/vehicle-transactions-export', [\App\Http\Controllers\VehicleTransactionController::class, 'export'])->name('vehicle.transactions.export');
        Route::get('/repair-jobs', [\App\Http\Controllers\RepairJobController::class, 'index'])->name('repair.jobs');
        Route::get('/repair-jobs-data', [\App\Http\Controllers\RepairJobController::class, 'data'])->name('repair.jobs.data');
        Route::get('/repair-job-details/{nomor_job}', [\App\Http\Controllers\RepairJobController::class, 'details'])
            ->where('nomor_job', '.*')
            ->name('repair.job.details');
    });

    // ──────────────────────────────────────────
    // Two-Factor Authentication
    // ──────────────────────────────────────────
    Route::get('2fa', [\App\Http\Controllers\TwoFactorController::class, 'index'])->name('2fa.index');
    Route::post('2fa/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('2fa/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::post('2fa/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('2fa/regenerate', [\App\Http\Controllers\TwoFactorController::class, 'regenerateCodes'])->name('2fa.regenerate-codes');
    Route::delete('sessions/{session}', [\App\Http\Controllers\TwoFactorController::class, 'terminateSession'])->name('2fa.terminate-session');
    Route::post('sessions/other', [\App\Http\Controllers\TwoFactorController::class, 'terminateOtherSessions'])->name('2fa.terminate-other-sessions');

    // ──────────────────────────────────────────
    // Admin Routes (requires admin role)
    // ──────────────────────────────────────────
    
    // Odoo settings for Maintenance (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('maintenance/odoo/settings', [\App\Http\Controllers\MaintenanceOdooSettingController::class, 'index'])->name('maintenance.odoo.settings');
        Route::post('maintenance/odoo/settings', [\App\Http\Controllers\MaintenanceOdooSettingController::class, 'store'])->name('maintenance.odoo.settings.store');
        Route::post('maintenance/odoo/test-connection', [\App\Http\Controllers\MaintenanceOdooSettingController::class, 'testConnection'])->name('maintenance.odoo.test_connection');
        Route::post('maintenance/odoo/sync-now', [\App\Http\Controllers\MaintenanceOdooSettingController::class, 'syncNow'])->name('maintenance.odoo.sync_now');
        Route::get('maintenance/odoo/sync-status', [\App\Http\Controllers\MaintenanceOdooSettingController::class, 'syncStatus'])->name('maintenance.odoo.sync_status');
    });

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
    });
});

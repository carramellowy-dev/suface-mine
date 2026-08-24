<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminUnitController;
use App\Http\Controllers\AdminMaterialController;
use App\Http\Controllers\AdminAreaController;
use App\Http\Controllers\AdminTargetController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminSpvController;
use App\Http\Controllers\AdminPegawaiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PegawaiRitasiController;
use App\Http\Controllers\PegawaiNonRitasiController;
use App\Http\Controllers\PegawaiGeneralController;
use App\Http\Controllers\UtilizationController;
use Illuminate\Http\Request;
use App\Http\Controllers\RekapanController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Legacy redirects
Route::get('/absensi', fn () => redirect()->route('pegawai.ritasi.create'));
Route::get('/rekapan', function () {
    $role = auth()->user()?->role;
    if ($role === 'admin') {
        return redirect()->route('admin.rekapan.index');
    }
    if ($role === 'spv') {
        return redirect()->route('spv.rekapan.index');
    }
    return redirect()->route('pegawai.ritasi.create');
})->name('rekapan');

Route::middleware('auth')->group(function () {
    // CSRF token refresh for offline sync (see resources/js/offline-sync.js)
    Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('csrf-token');

    // Pegawai (Operator)
    Route::middleware('role:pegawai')->prefix('pegawai')->name('pegawai.')->group(function () {
        Route::get('/', [PegawaiController::class, 'dashboard'])->name('dashboard');
        
        // Ritasi
        Route::get('ritasi/create', [PegawaiRitasiController::class, 'create'])->name('ritasi.create');
        Route::post('ritasi', [PegawaiRitasiController::class, 'store'])->name('ritasi.store');
        Route::get('ritasi/riwayat', [PegawaiRitasiController::class, 'riwayat'])->name('ritasi.riwayat');
        
        // Non-Ritasi
        Route::get('non-ritasi/create', [PegawaiNonRitasiController::class, 'create'])->name('non-ritasi.create');
        Route::post('non-ritasi', [PegawaiNonRitasiController::class, 'store'])->name('non-ritasi.store');
        Route::get('non-ritasi/riwayat', [PegawaiNonRitasiController::class, 'riwayat'])->name('non-ritasi.riwayat');
        
        // General
        Route::get('general/create', [PegawaiGeneralController::class, 'create'])->name('general.create');
        Route::post('general', [PegawaiGeneralController::class, 'store'])->name('general.store');

        // Utilization
        Route::get('utilization/create', [UtilizationController::class, 'create'])->name('utilization.create');
        Route::post('utilization', [UtilizationController::class, 'store'])->name('utilization.store');
    });

    // SPV
    Route::middleware('role:spv')->prefix('spv')->name('spv.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

        // Utilization
        Route::get('utilization', [UtilizationController::class, 'index'])->name('utilization.index');

        // Rekapan
        Route::get('rekapan', [RekapanController::class, 'index'])->name('rekapan.index');
        Route::post('rekapan/export', [RekapanController::class, 'export'])->name('rekapan.export');
        Route::get('rekapan/{pegawai}', [RekapanController::class, 'show'])->name('rekapan.show');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

        // Master Data
        Route::get('master-data', function (Request $request) {
            $tab = $request->query('tab', 'unit');
            return match ($tab) {
                'user' => app(\App\Http\Controllers\AdminUserController::class)->index($request),
                'target' => app(\App\Http\Controllers\AdminTargetController::class)->index($request),
                default => app(\App\Http\Controllers\AdminUnitController::class)->index($request),
            };
        })->name('master-data.index');
        Route::post('unit', [AdminUnitController::class, 'store'])->name('unit.store');
        Route::put('unit/{unit}', [AdminUnitController::class, 'update'])->name('unit.update');
        Route::delete('unit/{unit}', [AdminUnitController::class, 'destroy'])->name('unit.destroy');
        Route::get('unit/{unit}/edit', [AdminUnitController::class, 'edit'])->name('unit.edit');
        
        Route::post('material', [AdminMaterialController::class, 'store'])->name('material.store');
        Route::put('material/{material}', [AdminMaterialController::class, 'update'])->name('material.update');
        Route::delete('material/{material}', [AdminMaterialController::class, 'destroy'])->name('material.destroy');
        Route::get('material/{material}/edit', [AdminMaterialController::class, 'edit'])->name('material.edit');
        
        Route::post('area', [AdminAreaController::class, 'store'])->name('area.store');
        Route::put('area/{area}', [AdminAreaController::class, 'update'])->name('area.update');
        Route::delete('area/{area}', [AdminAreaController::class, 'destroy'])->name('area.destroy');
        Route::get('area/{area}/edit', [AdminAreaController::class, 'edit'])->name('area.edit');
        
        // Target
        Route::get('target', [AdminTargetController::class, 'index'])->name('target.index');
        Route::post('target', [AdminTargetController::class, 'store'])->name('target.store');
        Route::get('target/{target}/edit', [AdminTargetController::class, 'edit'])->name('target.edit');
        Route::put('target/{target}', [AdminTargetController::class, 'update'])->name('target.update');
        Route::delete('target/{target}', [AdminTargetController::class, 'destroy'])->name('target.destroy');

        // User Management
        Route::get('user', [AdminUserController::class, 'index'])->name('user.index');
        Route::post('user', [AdminUserController::class, 'store'])->name('user.store');
        Route::get('user/{user}/edit', [AdminUserController::class, 'edit'])->name('user.edit');
        Route::put('user/{user}', [AdminUserController::class, 'update'])->name('user.update');
        Route::delete('user/{user}', [AdminUserController::class, 'destroy'])->name('user.destroy');

        // Utilization
        Route::get('utilization', [UtilizationController::class, 'index'])->name('utilization.index');

        // Rekapan
        Route::get('rekapan', [RekapanController::class, 'index'])->name('rekapan.index');
        Route::post('rekapan/export', [RekapanController::class, 'export'])->name('rekapan.export');
        Route::get('rekapan/{pegawai}', [RekapanController::class, 'show'])->name('rekapan.show');
        
        // SPV Management
        Route::get('spv', [AdminSpvController::class, 'index'])->name('spv.index');
        Route::get('spv/create', [AdminSpvController::class, 'create'])->name('spv.create');
        Route::post('spv', [AdminSpvController::class, 'store'])->name('spv.store');
        Route::get('spv/{spv}/edit', [AdminSpvController::class, 'edit'])->name('spv.edit');
        Route::put('spv/{spv}', [AdminSpvController::class, 'update'])->name('spv.update');
        Route::delete('spv/{spv}', [AdminSpvController::class, 'destroy'])->name('spv.destroy');
        
        // Pegawai Management
        Route::get('pegawai', [AdminPegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('pegawai/create', [AdminPegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('pegawai', [AdminPegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('pegawai/{pegawai}/edit', [AdminPegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('pegawai/{pegawai}', [AdminPegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('pegawai/{pegawai}', [AdminPegawaiController::class, 'destroy'])->name('pegawai.destroy');
    });
});

require __DIR__.'/auth.php';

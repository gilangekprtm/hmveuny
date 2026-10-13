<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\HmveController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;

// Rute Pengguna (Frontend)
Route::get('/', [HmveController::class, 'index'])->name('home');
Route::get('/bergabung', [HmveController::class, 'join'])->name('join');
Route::get('/unit/{slug}', [HmveController::class, 'detailUnit'])->name('unit.detail');

Route::get('/control-panel/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/control-panel/login', [AdminAuthController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('admin.login.store');
Route::get('/control-panel/register', [AdminAuthController::class, 'registration'])->name('admin.register');
Route::post('/control-panel/register', [AdminAuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('admin.register.store');
Route::post('/control-panel/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

// Rute Control Panel Admin
Route::prefix('control-panel')->middleware(EnsureAdminAuthenticated::class)->group(function () {
    Route::get('/', [HmveController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/blob-upload-authorize', fn () => response()->noContent());
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // Konten publik website
    Route::put('/konten', [HmveController::class, 'updateSiteContent'])->name('admin.content.update');
    Route::post('/publikasi', [HmveController::class, 'storePublication'])->name('admin.publication.store');
    Route::put('/publikasi/{publication}', [HmveController::class, 'updatePublication'])->name('admin.publication.update');
    Route::delete('/publikasi/{publication}', [HmveController::class, 'destroyPublication'])->name('admin.publication.destroy');

    // Biro & Divisi
    Route::post('/unit', [HmveController::class, 'storeUnit'])->name('admin.unit.store');
    Route::put('/unit/{unit}', [HmveController::class, 'updateUnit'])->name('admin.unit.update');
    Route::delete('/unit/{id}', [HmveController::class, 'destroyUnit'])->name('admin.unit.destroy');

    // Pembina
    Route::post('/pembina', [HmveController::class, 'storePembina'])->name('admin.pembina.store');
    Route::delete('/pembina/{id}', [HmveController::class, 'destroyPembina'])->name('admin.pembina.destroy');

    // Pimpinan (Ketua & Wakil)
    Route::post('/pimpinan', [HmveController::class, 'storePimpinan'])->name('admin.pimpinan.store');
    Route::put('/pimpinan/{id}', [HmveController::class, 'updatePimpinan'])->name('admin.pimpinan.update');
    Route::delete('/pimpinan/{id}', [HmveController::class, 'destroyPimpinan'])->name('admin.pimpinan.destroy');

    // Anggota (Biro & Divisi)
    Route::post('/anggota', [HmveController::class, 'storeAnggota'])->name('admin.anggota.store');
    Route::put('/anggota/{id}', [HmveController::class, 'updateAnggota'])->name('admin.anggota.update');
    Route::delete('/anggota/{id}', [HmveController::class, 'destroyAnggota'])->name('admin.anggota.destroy');
});

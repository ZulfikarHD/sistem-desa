<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Warga (role: warga) — US-1.2 + US-1.3
    Route::view('dashboard', 'dashboard')
        ->middleware('role:warga')
        ->name('dashboard');

    // Dashboard Admin (role: admin) — US-1.2 + US-1.3
    // Route admin Phase 02/04/06 nanti ditambah di grup `role:admin` yang sama.
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::view('dashboard', 'admin.dashboard')->name('dashboard.admin');
    });
});

require __DIR__.'/settings.php';

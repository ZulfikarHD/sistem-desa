<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Warga (role: warga) — US-1.2
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Dashboard Admin (role: admin) — US-1.2
    Route::view('admin/dashboard', 'admin.dashboard')->name('dashboard.admin');
});

require __DIR__.'/settings.php';

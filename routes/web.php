<?php

use App\Livewire\JenisSurat\DataJenisSurat;
use App\Livewire\JenisSurat\PersyaratanDokumen;
use App\Livewire\Pengajuan\DetailPengajuanWarga;
use App\Livewire\Pengajuan\FormPengajuanSurat;
use App\Livewire\Pengajuan\RiwayatPengajuan;
use App\Livewire\Verifikasi\DaftarPengajuanVerifikasi;
use App\Livewire\Verifikasi\DetailPengajuanVerifikasi;
use App\Models\DokumenPersyaratan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');

// US-2.2 + US-2.3 — Persyaratan dokumen (publik, tanpa auth; warga/admin tetap bisa membuka)
Route::livewire('persyaratan-dokumen', PersyaratanDokumen::class)
    ->name('persyaratan-dokumen.index');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Warga (role: warga) — US-1.2 + US-1.3
    Route::middleware('role:warga')->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');

        // US-3.1 — Form Pengajuan Surat Keterangan
        Route::livewire('pengajuan-surat', FormPengajuanSurat::class)
            ->name('pengajuan-surat.create');

        // US-3.4 + US-5.3 — Riwayat pengajuan & ajukan ulang
        Route::livewire('riwayat-pengajuan', RiwayatPengajuan::class)
            ->name('pengajuan-surat.riwayat');

        // US-5.3 — Detail pengajuan warga
        Route::livewire('pengajuan-surat/detail/{pengajuan}', DetailPengajuanWarga::class)
            ->name('pengajuan-surat.show')
            ->whereNumber('pengajuan');

        Route::livewire('pengajuan-surat/ajukan-ulang/{pengajuan}', FormPengajuanSurat::class)
            ->name('pengajuan-surat.resubmit')
            ->whereNumber('pengajuan');
    });

    // Dashboard Admin (role: admin) — US-1.2 + US-1.3
    // Route admin Phase 02/04/06 ditambah di grup `role:admin` yang sama.
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::view('dashboard', 'admin.dashboard')->name('dashboard.admin');

        // US-2.1 — Kelola Data Jenis Surat
        Route::livewire('jenis-surat', DataJenisSurat::class)->name('jenis-surat.index');

        // US-4.1 + US-4.2 — Verifikasi pengajuan (daftar & detail)
        Route::livewire('verifikasi', DaftarPengajuanVerifikasi::class)->name('verifikasi.index');

        // Pratinjau/unduh dokumen persyaratan (hanya admin, disk privat) — harus sebelum {pengajuan}
        Route::get('verifikasi/dokumen/{dokumen}', function (DokumenPersyaratan $dokumen) {
            abort_unless(Storage::disk('local')->exists($dokumen->file_path), 404);

            return Storage::disk('local')->response($dokumen->file_path);
        })->name('verifikasi.dokumen.show')->whereNumber('dokumen');

        Route::get('verifikasi/dokumen/{dokumen}/unduh', function (DokumenPersyaratan $dokumen) {
            abort_unless(Storage::disk('local')->exists($dokumen->file_path), 404);

            $filename = basename($dokumen->file_path);

            return Storage::disk('local')->download($dokumen->file_path, $filename);
        })->name('verifikasi.dokumen.download')->whereNumber('dokumen');

        Route::livewire('verifikasi/{pengajuan}', DetailPengajuanVerifikasi::class)
            ->name('verifikasi.show')
            ->whereNumber('pengajuan');
    });
});

require __DIR__.'/settings.php';

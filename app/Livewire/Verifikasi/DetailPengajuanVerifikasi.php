<?php

namespace App\Livewire\Verifikasi;

use App\Models\DokumenPersyaratan;
use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Pengajuan')]
class DetailPengajuanVerifikasi extends Component
{
    public PengajuanSurat $pengajuan;

    /**
     * Muat relasi pengajuan untuk tampilan detail verifikasi.
     */
    public function mount(PengajuanSurat $pengajuan): void
    {
        $pengajuan->load([
            'user:id,name,nik,no_telepon,alamat',
            'jenisSurat:id,nama_surat,deskripsi,persyaratan_dokumen',
            'dokumenPersyaratan',
        ]);

        $this->pengajuan = $pengajuan;
    }

    /**
     * Tentukan apakah file dapat dipratinjau sebagai gambar.
     */
    public function isPreviewableImage(DokumenPersyaratan $dokumen): bool
    {
        if (! $this->fileExists($dokumen)) {
            return false;
        }

        $extension = strtolower(pathinfo($dokumen->file_path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    /**
     * Tentukan apakah file dapat dipratinjau sebagai PDF.
     */
    public function isPreviewablePdf(DokumenPersyaratan $dokumen): bool
    {
        if (! $this->fileExists($dokumen)) {
            return false;
        }

        return strtolower(pathinfo($dokumen->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Cek keberadaan file di disk lokal privat.
     */
    public function fileExists(DokumenPersyaratan $dokumen): bool
    {
        return Storage::disk('local')->exists($dokumen->file_path);
    }

    public function render(): View
    {
        return view('livewire.verifikasi.detail-pengajuan-verifikasi')
            ->layout('layouts::app');
    }
}

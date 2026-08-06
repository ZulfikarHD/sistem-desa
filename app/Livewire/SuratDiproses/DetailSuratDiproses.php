<?php

namespace App\Livewire\SuratDiproses;

use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Surat Diproses')]
class DetailSuratDiproses extends Component
{
    public PengajuanSurat $pengajuan;

    /** Tanggal pengambilan (Y-m-d) untuk tandai siap diambil (US-8.6). */
    public string $tanggalPengambilan = '';

    /**
     * Muat relasi pengajuan untuk halaman detail surat diproses.
     */
    public function mount(PengajuanSurat $pengajuan): void
    {
        $pengajuan->load([
            'user:id,name,nik,no_telepon,alamat',
            'jenisSurat:id,nama_surat,deskripsi',
            'suratTerbit',
        ]);

        $this->pengajuan = $pengajuan;
    }

    /**
     * Tanggal minimal date picker (hari ini WIB) untuk atribut min HTML.
     */
    public function tanggalMinHariIni(): string
    {
        return now('Asia/Jakarta')->toDateString();
    }

    /**
     * Apakah admin dapat menandai dokumen siap diambil.
     * Syarat: status diproses dan PDF surat_terbit sudah ada.
     */
    public function canMarkSiapDiambil(): bool
    {
        return $this->pengajuan->status === PengajuanSurat::STATUS_DIPROSES
            && $this->pengajuan->suratTerbit !== null;
    }

    /**
     * Apakah form tanggal & tombol Siap Diambil disembunyikan (sudah lanjut status).
     */
    public function sudahLewatDiproses(): bool
    {
        return in_array($this->pengajuan->status, [
            PengajuanSurat::STATUS_SIAP_DIAMBIL,
            PengajuanSurat::STATUS_SELESAI,
        ], true);
    }

    /**
     * Preview jam kerja berdasarkan tanggal yang dipilih (kosong jika tutup/invalid).
     */
    public function jamKerjaPreview(): ?string
    {
        if ($this->tanggalPengambilan === '') {
            return null;
        }

        try {
            $tanggal = Carbon::parse($this->tanggalPengambilan, 'Asia/Jakarta');
        } catch (\Throwable) {
            return null;
        }

        $validasi = SuratTerbit::validasiTanggalPengambilan($tanggal);

        return $validasi['ok'] ? $validasi['jam_kerja_label'] : null;
    }

    /**
     * Tombol "Siap Diambil" aktif hanya jika tanggal valid (US-8.6).
     */
    public function isTanggalPengambilanSiap(): bool
    {
        if ($this->tanggalPengambilan === '') {
            return false;
        }

        try {
            $tanggal = Carbon::parse($this->tanggalPengambilan, 'Asia/Jakarta');
        } catch (\Throwable) {
            return false;
        }

        return SuratTerbit::validasiTanggalPengambilan($tanggal)['ok'];
    }

    /**
     * Apakah file PDF surat terbit ada di disk.
     */
    public function suratPdfExists(): bool
    {
        $surat = $this->pengajuan->suratTerbit;

        if ($surat === null) {
            return false;
        }

        return Storage::disk('local')->exists($surat->file_path);
    }

    /**
     * Tandai dokumen siap diambil: diproses → siap_diambil + notifikasi warga (US-8.6).
     */
    public function tandaiSiapDiambil(): void
    {
        if (! $this->canMarkSiapDiambil()) {
            Flux::toast(variant: 'danger', text: 'Pengajuan tidak dapat ditandai siap diambil pada status saat ini.');

            return;
        }

        // Validasi server: required + after_or_equal hari ini WIB (AC US-8.6) + jam kerja di model.
        // Pakai tanggal WIB eksplisit agar tidak bergantung APP_TIMEZONE=UTC.
        $hariIniWib = now('Asia/Jakarta')->toDateString();

        $this->validate([
            'tanggalPengambilan' => ['required', 'date', 'after_or_equal:'.$hariIniWib],
        ], [
            'tanggalPengambilan.required' => 'Tanggal pengambilan wajib dipilih.',
            'tanggalPengambilan.date' => 'Format tanggal pengambilan tidak valid.',
            'tanggalPengambilan.after_or_equal' => 'Tanggal pengambilan tidak boleh di masa lalu.',
        ]);

        $tanggal = Carbon::parse($this->tanggalPengambilan, 'Asia/Jakarta');
        $hasil = SuratTerbit::tandaiSiapDiambil($this->pengajuan, $tanggal);

        if (! $hasil['ok']) {
            $this->addError('tanggalPengambilan', $hasil['message']);
            Flux::toast(variant: 'danger', text: $hasil['message']);

            return;
        }

        Flux::toast(variant: 'success', text: $hasil['message']);

        $this->redirect(route('surat-diproses.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.surat-diproses.detail-surat-diproses')
            ->layout('layouts::app');
    }
}

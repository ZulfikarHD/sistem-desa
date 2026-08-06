<?php

namespace App\Livewire\Verifikasi;

use App\Models\DokumenPersyaratan;
use App\Models\LogVerifikasi;
use App\Models\PengajuanSurat;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Pengajuan')]
class DetailPengajuanVerifikasi extends Component
{
    public PengajuanSurat $pengajuan;

    /** Modal konfirmasi penolakan pengajuan. */
    public bool $showTolakModal = false;

    /** Alasan penolakan yang wajib diisi admin. */
    public string $catatanAdmin = '';

    /**
     * Muat relasi pengajuan dan transisi status diajukan → diproses (US-4.4).
     */
    public function mount(PengajuanSurat $pengajuan): void
    {
        $pengajuan->load([
            'user:id,name,nik,no_telepon,alamat',
            'jenisSurat:id,nama_surat,deskripsi,persyaratan_dokumen',
            'dokumenPersyaratan',
        ]);

        if ($pengajuan->status === PengajuanSurat::STATUS_DIAJUKAN) {
            $pengajuan->update(['status' => PengajuanSurat::STATUS_DIPROSES]);
            $pengajuan->refresh();
        }

        $this->pengajuan = $pengajuan;
    }

    /**
     * Apakah pengajuan masih dapat diverifikasi (setujui/tolak).
     */
    public function canVerify(): bool
    {
        return $this->pengajuan->status === PengajuanSurat::STATUS_DIPROSES;
    }

    /**
     * Buka modal penolakan pengajuan.
     */
    public function openTolakModal(): void
    {
        if (! $this->canVerify()) {
            return;
        }

        $this->resetValidation();
        $this->catatanAdmin = '';
        $this->showTolakModal = true;
    }

    /**
     * Tutup modal penolakan tanpa menyimpan.
     */
    public function closeTolakModal(): void
    {
        $this->showTolakModal = false;
        $this->catatanAdmin = '';
        $this->resetValidation();
    }

    /**
     * Setujui pengajuan yang sedang diproses (US-4.3).
     */
    public function setujui(): void
    {
        if (! $this->canVerify()) {
            Flux::toast(variant: 'danger', text: 'Pengajuan tidak dapat disetujui pada status saat ini.');

            return;
        }

        $adminId = Auth::id();

        DB::transaction(function () use ($adminId): void {
            $pengajuan = PengajuanSurat::query()
                ->whereKey($this->pengajuan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pengajuan->status !== PengajuanSurat::STATUS_DIPROSES) {
                throw new \RuntimeException('Status pengajuan telah berubah.');
            }

            $pengajuan->update([
                'status' => PengajuanSurat::STATUS_DISETUJUI,
                'catatan_admin' => null,
                'diverifikasi_oleh' => $adminId,
            ]);

            LogVerifikasi::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'admin_id' => $adminId,
                'aksi' => LogVerifikasi::AKSI_SETUJUI,
                'keterangan' => null,
                'created_at' => now(),
            ]);
        });

        Flux::toast(variant: 'success', text: 'Pengajuan berhasil disetujui.');

        $this->redirect(route('verifikasi.index'), navigate: true);
    }

    /**
     * Tolak pengajuan dengan catatan admin wajib (US-4.3).
     */
    public function tolak(): void
    {
        if (! $this->canVerify()) {
            Flux::toast(variant: 'danger', text: 'Pengajuan tidak dapat ditolak pada status saat ini.');

            return;
        }

        $validated = $this->validate([
            'catatanAdmin' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatanAdmin.required' => 'Alasan penolakan wajib diisi.',
            'catatanAdmin.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $adminId = Auth::id();
        $catatan = $validated['catatanAdmin'];

        DB::transaction(function () use ($adminId, $catatan): void {
            $pengajuan = PengajuanSurat::query()
                ->whereKey($this->pengajuan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pengajuan->status !== PengajuanSurat::STATUS_DIPROSES) {
                throw new \RuntimeException('Status pengajuan telah berubah.');
            }

            $pengajuan->update([
                'status' => PengajuanSurat::STATUS_DITOLAK,
                'catatan_admin' => $catatan,
                'diverifikasi_oleh' => $adminId,
            ]);

            LogVerifikasi::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'admin_id' => $adminId,
                'aksi' => LogVerifikasi::AKSI_TOLAK,
                'keterangan' => $catatan,
                'created_at' => now(),
            ]);
        });

        $this->closeTolakModal();

        Flux::toast(variant: 'success', text: 'Pengajuan berhasil ditolak.');

        $this->redirect(route('verifikasi.index'), navigate: true);
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

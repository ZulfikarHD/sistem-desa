<?php

namespace App\Livewire\Verifikasi;

use App\Models\DokumenPersyaratan;
use App\Models\LogVerifikasi;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
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

    /** Tanggal pengambilan (Y-m-d) untuk tandai siap diambil (US-7.5). */
    public string $tanggalPengambilan = '';

    /**
     * Muat relasi pengajuan tanpa mengubah status (US-7.1 — hapus auto diajukan→diproses).
     */
    public function mount(PengajuanSurat $pengajuan): void
    {
        $pengajuan->load([
            'user:id,name,nik,no_telepon,alamat',
            'jenisSurat:id,nama_surat,deskripsi,persyaratan_dokumen',
            'dokumenPersyaratan',
            'suratTerbit',
        ]);

        $this->pengajuan = $pengajuan;
    }

    /**
     * Apakah pengajuan masih dapat diverifikasi (setujui/tolak).
     * Menunggu verifikasi = status diajukan (US-7.1).
     */
    public function canVerify(): bool
    {
        return $this->pengajuan->status === PengajuanSurat::STATUS_DIAJUKAN;
    }

    /**
     * Apakah admin dapat menandai dokumen siap diambil (US-7.5).
     * Syarat: status diproses dan PDF surat_terbit sudah ada.
     */
    public function canMarkSiapDiambil(): bool
    {
        return $this->pengajuan->status === PengajuanSurat::STATUS_DIPROSES
            && $this->pengajuan->suratTerbit !== null;
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
     * Tombol "Dokumen Siap Diambil" aktif hanya jika tanggal valid (US-7.5).
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
     * Tandai dokumen siap diambil: diproses → siap_diambil + notifikasi warga (US-7.5).
     */
    public function tandaiDokumenSiapDiambil(): void
    {
        if (! $this->canMarkSiapDiambil()) {
            Flux::toast(variant: 'danger', text: 'Pengajuan tidak dapat ditandai siap diambil pada status saat ini.');

            return;
        }

        $this->validate([
            'tanggalPengambilan' => ['required', 'date'],
        ], [
            'tanggalPengambilan.required' => 'Tanggal pengambilan wajib dipilih.',
            'tanggalPengambilan.date' => 'Format tanggal pengambilan tidak valid.',
        ]);

        $tanggal = Carbon::parse($this->tanggalPengambilan, 'Asia/Jakarta');
        $hasil = SuratTerbit::tandaiSiapDiambil($this->pengajuan, $tanggal);

        if (! $hasil['ok']) {
            $this->addError('tanggalPengambilan', $hasil['message']);
            Flux::toast(variant: 'danger', text: $hasil['message']);

            return;
        }

        Flux::toast(variant: 'success', text: $hasil['message']);

        $this->redirect(route('verifikasi.index'), navigate: true);
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
     * Setujui pengajuan dari diajukan → disetujui → diproses (US-7.1).
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

            if ($pengajuan->status !== PengajuanSurat::STATUS_DIAJUKAN) {
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

            $pengajuanFresh = $pengajuan->fresh(['jenisSurat']);
            $this->buatNotifikasiStatus($pengajuanFresh, PengajuanSurat::STATUS_DISETUJUI);

            // Lanjut otomatis ke diproses + hook generate surat (US-7.2)
            $pengajuanFresh->update(['status' => PengajuanSurat::STATUS_DIPROSES]);
            $this->buatNotifikasiStatus($pengajuanFresh->fresh(['jenisSurat']), PengajuanSurat::STATUS_DIPROSES);
            $this->triggerGenerateSurat($pengajuanFresh->fresh(['jenisSurat']));
        });

        Flux::toast(variant: 'success', text: 'Pengajuan berhasil disetujui.');

        $this->redirect(route('verifikasi.index'), navigate: true);
    }

    /**
     * Tolak pengajuan dengan catatan admin wajib (US-7.1 — terminal, tidak masuk diproses).
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

            if ($pengajuan->status !== PengajuanSurat::STATUS_DIAJUKAN) {
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

            $this->buatNotifikasiStatus($pengajuan->fresh(['jenisSurat']), PengajuanSurat::STATUS_DITOLAK);
        });

        $this->closeTolakModal();

        Flux::toast(variant: 'success', text: 'Pengajuan berhasil ditolak.');

        $this->redirect(route('verifikasi.index'), navigate: true);
    }

    /**
     * Generate PDF surat, nomor resmi, dan QR sekali pakai setelah masuk diproses (US-7.2).
     */
    private function triggerGenerateSurat(PengajuanSurat $pengajuan): void
    {
        $adminId = Auth::id();

        if ($adminId === null) {
            throw new \RuntimeException('Admin penerbit tidak ditemukan.');
        }

        SuratTerbit::terbitkanUntuk(
            $pengajuan->fresh(['user', 'jenisSurat']),
            $adminId,
        );
    }

    /**
     * Buat notifikasi in-app untuk warga pemohon (US-5.1 / US-7.1).
     */
    private function buatNotifikasiStatus(PengajuanSurat $pengajuan, string $statusBaru): void
    {
        $namaSurat = $pengajuan->jenisSurat?->nama_surat ?? 'Surat';
        $labelStatus = match ($statusBaru) {
            PengajuanSurat::STATUS_DIPROSES => 'sedang diproses',
            PengajuanSurat::STATUS_DISETUJUI => 'disetujui',
            PengajuanSurat::STATUS_DITOLAK => 'ditolak',
            PengajuanSurat::STATUS_SIAP_DIAMBIL => 'siap diambil',
            PengajuanSurat::STATUS_SELESAI => 'selesai',
            default => $statusBaru,
        };

        Notifikasi::query()->create([
            'user_id' => $pengajuan->user_id,
            'pengajuan_id' => $pengajuan->id,
            'pesan' => "Pengajuan {$namaSurat} ({$pengajuan->nomor_pengajuan}) {$labelStatus}.",
            'status_baca' => Notifikasi::STATUS_BELUM,
            'created_at' => now(),
        ]);
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

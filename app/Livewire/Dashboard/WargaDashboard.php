<?php

namespace App\Livewire\Dashboard;

use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard Warga')]
class WargaDashboard extends Component
{
    /** Elapsed time diajukan > N hari → warna amber (sinyal ke warga). */
    public const DIAJUKAN_ELAPSED_AMBER_HARI = 7;

    /**
     * Langkah alur yang ditampilkan di hero (bahasa warga).
     *
     * @return list<array{key: string, label: string}>
     */
    public function langkahAlur(): array
    {
        return [
            ['key' => PengajuanSurat::STATUS_DIAJUKAN, 'label' => 'Diajukan'],
            ['key' => PengajuanSurat::STATUS_DIPROSES, 'label' => 'Diproses'],
            ['key' => PengajuanSurat::STATUS_SIAP_DIAMBIL, 'label' => 'Siap diambil'],
        ];
    }

    /**
     * Indeks langkah aktif pada alur (0-based). Null jika status di luar alur hero.
     */
    public function indeksLangkahAktif(string $status): ?int
    {
        $normalized = match ($status) {
            PengajuanSurat::STATUS_DISETUJUI => PengajuanSurat::STATUS_DIPROSES,
            default => $status,
        };

        foreach ($this->langkahAlur() as $index => $langkah) {
            if ($langkah['key'] === $normalized) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Kalimat penjelasan status untuk warga awam.
     */
    public function penjelasanStatus(string $status): string
    {
        return match ($status) {
            PengajuanSurat::STATUS_DIAJUKAN => 'Pengajuan Anda sedang menunggu ditinjau oleh petugas desa.',
            PengajuanSurat::STATUS_DISETUJUI,
            PengajuanSurat::STATUS_DIPROSES => 'Surat Anda sedang disiapkan oleh petugas. Anda dapat mengunduh surat sementara di bawah.',
            PengajuanSurat::STATUS_SIAP_DIAMBIL => 'Surat Anda sudah siap diambil! Datanglah ke kantor desa pada jadwal berikut:',
            default => PengajuanSurat::statusLabel($status),
        };
    }

    public function render(): View
    {
        $user = Auth::user();
        $userId = $user?->id;
        $hariIni = now('Asia/Jakarta')->startOfDay();

        $pengajuanAktif = PengajuanSurat::query()
            ->with([
                'jenisSurat:id,nama_surat',
                'suratTerbit:id,pengajuan_id,tanggal_terbit,tanggal_pengambilan,jam_kerja_label,siap_diambil_at,file_path',
            ])
            ->where('user_id', $userId)
            ->whereIn('status', PengajuanSurat::statusAktif())
            ->get()
            ->map(function (PengajuanSurat $pengajuan) use ($hariIni): array {
                $hari = $pengajuan->hariDiStatusSaatIni($hariIni);

                return [
                    'model' => $pengajuan,
                    'hari' => $hari,
                    'masuk_at' => $pengajuan->waktuMasukStatusSaatIni()->timestamp,
                    'penjelasan' => $this->penjelasanStatus($pengajuan->status),
                    'elapsed_amber' => $pengajuan->status === PengajuanSurat::STATUS_DIAJUKAN
                        && $hari > self::DIAJUKAN_ELAPSED_AMBER_HARI,
                    'boleh_unduh' => $pengajuan->dapatUnduhSurat(),
                    'langkah_aktif' => $this->indeksLangkahAktif($pengajuan->status),
                ];
            })
            // Terlama di status aktif dahulu
            ->sortBy('masuk_at')
            ->values();

        $riwayatTerbaru = PengajuanSurat::query()
            ->with(['jenisSurat:id,nama_surat'])
            ->where('user_id', $userId)
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->limit(3)
            ->get();

        $notifikasiTerbaru = Notifikasi::query()
            ->where('user_id', $userId)
            ->orderByRaw('CASE WHEN status_baca = ? THEN 0 ELSE 1 END', [Notifikasi::STATUS_BELUM])
            ->latest('created_at')
            ->latest('id')
            ->limit(3)
            ->get();

        $unreadCount = Notifikasi::query()
            ->where('user_id', $userId)
            ->where('status_baca', Notifikasi::STATUS_BELUM)
            ->count();

        return view('livewire.dashboard.warga-dashboard', [
            'namaWarga' => $user?->name ?? 'Warga',
            'pengajuanAktif' => $pengajuanAktif,
            'langkahAlur' => $this->langkahAlur(),
            'riwayatTerbaru' => $riwayatTerbaru,
            'notifikasiTerbaru' => $notifikasiTerbaru,
            'unreadCount' => $unreadCount,
        ])->layout('layouts::app');
    }
}

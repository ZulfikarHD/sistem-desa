<?php

namespace App\Livewire\Rekap;

use App\Models\LogVerifikasi;
use App\Models\PengajuanSurat;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Rekap Pengajuan')]
class DetailRekapPengajuan extends Component
{
    public PengajuanSurat $pengajuan;

    /**
     * Muat pengajuan beserta relasi untuk ringkasan + timeline (US-8.7).
     */
    public function mount(PengajuanSurat $pengajuan): void
    {
        $pengajuan->load([
            'user:id,name,nik',
            'jenisSurat:id,nama_surat',
            'diverifikasiOleh:id,name',
            'logVerifikasi.admin:id,name',
            'suratTerbit.diterbitkanOleh:id,name',
            'suratTerbit.qrDigunakanOleh:id,name',
        ]);

        $this->pengajuan = $pengajuan;
    }

    /**
     * Apakah tombol unduh PDF boleh ditampilkan (ada surat_terbit; hybrid pastikan file).
     */
    public function dapatUnduhPdf(): bool
    {
        $surat = $this->pengajuan->suratTerbit;

        if ($surat === null) {
            return false;
        }

        return $surat->pastikanFilePdf() !== null;
    }

    /**
     * Bangun poin timeline kronologis — hanya tahap yang sudah terjadi (US-8.7).
     *
     * @return list<array{
     *     key: string,
     *     icon: string,
     *     color: string,
     *     label: string,
     *     waktu: CarbonInterface,
     *     aktor: string,
     *     estimasi: bool
     * }>
     */
    public function timelineItems(): array
    {
        $items = [];

        // 1. Pengajuan dibuat
        $items[] = [
            'key' => 'dibuat',
            'icon' => 'document-text',
            'color' => 'zinc',
            'label' => 'Pengajuan diterima oleh sistem',
            'waktu' => Carbon::parse($this->pengajuan->created_at ?? now())->timezone('Asia/Jakarta'),
            'aktor' => 'Sistem',
            'estimasi' => false,
        ];

        $logTolak = $this->pengajuan->logVerifikasi
            ->where('aksi', LogVerifikasi::AKSI_TOLAK)
            ->sortByDesc('created_at')
            ->first();

        $logSetujui = $this->pengajuan->logVerifikasi
            ->where('aksi', LogVerifikasi::AKSI_SETUJUI)
            ->sortByDesc('created_at')
            ->first();

        // Jika ditolak: tampilkan poin tolak lalu berhenti (AC US-8.7).
        if ($this->pengajuan->status === PengajuanSurat::STATUS_DITOLAK || $logTolak !== null) {
            if ($logTolak !== null) {
                $alasan = trim((string) ($logTolak->keterangan ?: $this->pengajuan->catatan_admin ?: '—'));
                $namaAdmin = $logTolak->admin?->name ?? 'Petugas desa';

                $items[] = [
                    'key' => 'ditolak',
                    'icon' => 'x-circle',
                    'color' => 'red',
                    'label' => "Ditolak oleh {$namaAdmin} — Alasan: {$alasan}",
                    'waktu' => Carbon::parse($logTolak->created_at)->timezone('Asia/Jakarta'),
                    'aktor' => $namaAdmin,
                    'estimasi' => false,
                ];
            }

            return $items;
        }

        // 2. Disetujui & surat diproses
        if ($logSetujui !== null) {
            $namaAdmin = $logSetujui->admin?->name ?? 'Petugas desa';
            $nomorSurat = $this->pengajuan->suratTerbit?->nomor_surat;
            $labelNomor = $nomorSurat !== null && $nomorSurat !== ''
                ? "surat #{$nomorSurat} digenerate"
                : 'surat sedang digenerate';

            $items[] = [
                'key' => 'disetujui_diproses',
                'icon' => 'check-circle',
                'color' => 'blue',
                'label' => "Disetujui oleh {$namaAdmin} — {$labelNomor}",
                'waktu' => Carbon::parse($logSetujui->created_at)->timezone('Asia/Jakarta'),
                'aktor' => $namaAdmin,
                'estimasi' => false,
            ];
        }

        $surat = $this->pengajuan->suratTerbit;

        // 3. Siap diambil (hanya jika sudah terjadi)
        $statusSudahSiapAtauSelesai = in_array($this->pengajuan->status, [
            PengajuanSurat::STATUS_SIAP_DIAMBIL,
            PengajuanSurat::STATUS_SELESAI,
        ], true);

        if ($surat !== null && ($surat->siap_diambil_at !== null || $statusSudahSiapAtauSelesai)) {
            $estimasi = $surat->siap_diambil_at === null;
            $waktuSiap = $surat->siap_diambil_at
                ?? $surat->updated_at
                ?? now();

            // Aktor: diterbitkan_oleh (sumber di data model Phase 08), fallback diverifikasi_oleh.
            $namaAdminSiap = $surat->diterbitkanOleh?->name
                ?? $this->pengajuan->diverifikasiOleh?->name
                ?? 'Petugas desa';

            $tanggalLabel = $surat->tanggal_pengambilan
                ? Carbon::parse($surat->tanggal_pengambilan)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d MMMM Y')
                : '—';
            $jamLabel = $surat->jam_kerja_label ?: '—';

            $label = "Dokumen siap diambil oleh {$namaAdminSiap} — Tanggal: {$tanggalLabel} ({$jamLabel})";
            if ($estimasi) {
                $label .= ' (waktu estimasi — data lama tanpa siap_diambil_at)';
            }

            $items[] = [
                'key' => 'siap_diambil',
                'icon' => 'calendar-days',
                'color' => 'green',
                'label' => $label,
                'waktu' => Carbon::parse($waktuSiap)->timezone('Asia/Jakarta'),
                'aktor' => $namaAdminSiap,
                'estimasi' => $estimasi,
            ];
        }

        // 4. Selesai (QR scan)
        if ($surat !== null && $surat->qr_digunakan_at !== null) {
            $namaAdminQr = $surat->qrDigunakanOleh?->name ?? 'Petugas desa';

            $items[] = [
                'key' => 'selesai',
                'icon' => 'qr-code',
                'color' => 'zinc',
                'label' => "Dokumen telah diambil — QR dipindai, dicatat oleh {$namaAdminQr}",
                'waktu' => Carbon::parse($surat->qr_digunakan_at)->timezone('Asia/Jakarta'),
                'aktor' => $namaAdminQr,
                'estimasi' => false,
            ];
        }

        return $items;
    }

    /**
     * Format waktu timeline: "DD MMMM YYYY, HH:mm WIB".
     */
    public function formatWaktuWib(CarbonInterface $waktu): string
    {
        // Format AC US-8.7: "DD MMMM YYYY, HH:mm WIB" (nama bulan Indonesia).
        return Carbon::parse($waktu)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d MMMM Y, H:i').' WIB';
    }

    public function render(): View
    {
        return view('livewire.rekap.detail-rekap-pengajuan', [
            'timelineItems' => $this->timelineItems(),
        ])->layout('layouts::app');
    }
}

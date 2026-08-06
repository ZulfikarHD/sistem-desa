<?php

namespace App\Livewire\Dashboard;

use App\Models\PengajuanSurat;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard Admin')]
class AdminDashboard extends Component
{
    /** Threshold aging diajukan (hari kalender). */
    public const DIAJUKAN_WARNING_HARI = 3;

    public const DIAJUKAN_URGENT_HARI = 7;

    /** Threshold aging diproses (hari kalender). */
    public const DIPROSES_WARNING_HARI = 5;

    public const DIPROSES_URGENT_HARI = 10;

    /**
     * Buka detail penanganan sesuai status pengajuan.
     */
    public function tangani(int $pengajuanId): void
    {
        $pengajuan = PengajuanSurat::query()->findOrFail($pengajuanId);

        $url = match ($pengajuan->status) {
            PengajuanSurat::STATUS_DIAJUKAN => route('verifikasi.show', $pengajuan),
            PengajuanSurat::STATUS_DIPROSES,
            PengajuanSurat::STATUS_DISETUJUI => route('surat-diproses.show', $pengajuan),
            PengajuanSurat::STATUS_SIAP_DIAMBIL => route('scan-qr-pengambilan.index'),
            default => route('rekap-pengajuan.index', ['status' => $pengajuan->status]),
        };

        $this->redirect($url, navigate: true);
    }

    /**
     * Buka detail / rekap untuk baris tabel aktif.
     */
    public function lihatDetail(int $pengajuanId): void
    {
        $pengajuan = PengajuanSurat::query()->findOrFail($pengajuanId);

        $url = match ($pengajuan->status) {
            PengajuanSurat::STATUS_DIAJUKAN => route('verifikasi.show', $pengajuan),
            PengajuanSurat::STATUS_DIPROSES,
            PengajuanSurat::STATUS_DISETUJUI => route('surat-diproses.show', $pengajuan),
            PengajuanSurat::STATUS_SIAP_DIAMBIL => route('rekap-pengajuan.index', [
                'status' => PengajuanSurat::STATUS_SIAP_DIAMBIL,
            ]),
            default => route('rekap-pengajuan.index'),
        };

        $this->redirect($url, navigate: true);
    }

    public function render(): View
    {
        $hariIni = now('Asia/Jakarta')->startOfDay();
        $besok = $hariIni->copy()->addDay();

        $kartuDiajukan = $this->buatKartuDiajukan($hariIni);
        $kartuDiproses = $this->buatKartuDiproses($hariIni);
        $kartuSiapDiambil = $this->buatKartuSiapDiambil($hariIni, $besok);
        $kartuSelesaiBulanIni = $this->buatKartuSelesaiBulanIni($hariIni);

        $perluDitindaklanjuti = $this->ambilPerluDitindaklanjuti($hariIni);
        $pengajuanAktifTerbaru = $this->ambilPengajuanAktifTerbaru($hariIni);

        $semuaKartuAktifKosong = $kartuDiajukan['total'] === 0
            && $kartuDiproses['total'] === 0
            && $kartuSiapDiambil['total'] === 0;

        return view('livewire.dashboard.admin-dashboard', [
            'kartuDiajukan' => $kartuDiajukan,
            'kartuDiproses' => $kartuDiproses,
            'kartuSiapDiambil' => $kartuSiapDiambil,
            'kartuSelesaiBulanIni' => $kartuSelesaiBulanIni,
            'perluDitindaklanjuti' => $perluDitindaklanjuti,
            'pengajuanAktifTerbaru' => $pengajuanAktifTerbaru,
            'semuaKartuAktifKosong' => $semuaKartuAktifKosong,
        ])->layout('layouts::app');
    }

    /**
     * @return array{total: int, sub_label: string|null, severity: string, href: string}
     */
    private function buatKartuDiajukan(CarbonInterface $hariIni): array
    {
        $items = PengajuanSurat::query()
            ->where('status', PengajuanSurat::STATUS_DIAJUKAN)
            ->get(['id', 'status', 'created_at', 'updated_at']);

        $warning = 0;
        $urgent = 0;
        $tertundaWarning = 0;

        foreach ($items as $item) {
            $hari = $item->hariDiStatusSaatIni($hariIni);
            if ($hari > self::DIAJUKAN_URGENT_HARI) {
                $urgent++;
            }
            if ($hari > self::DIAJUKAN_WARNING_HARI) {
                $warning++;
                $tertundaWarning++;
            }
        }

        return [
            'total' => $items->count(),
            'sub_label' => $tertundaWarning > 0
                ? $tertundaWarning.' tertunda > '.self::DIAJUKAN_WARNING_HARI.' hari'
                : null,
            'severity' => $this->severityDariHitungan($urgent, $warning),
            'href' => route('verifikasi.index'),
        ];
    }

    /**
     * @return array{total: int, sub_label: string|null, severity: string, href: string}
     */
    private function buatKartuDiproses(CarbonInterface $hariIni): array
    {
        $items = PengajuanSurat::query()
            ->with(['suratTerbit:id,pengajuan_id,tanggal_terbit'])
            ->whereIn('status', PengajuanSurat::statusDiprosesDashboard())
            ->get();

        $warning = 0;
        $urgent = 0;
        $tertundaWarning = 0;

        foreach ($items as $item) {
            $hari = $item->hariDiStatusSaatIni($hariIni);
            if ($hari > self::DIPROSES_URGENT_HARI) {
                $urgent++;
            }
            if ($hari > self::DIPROSES_WARNING_HARI) {
                $warning++;
                $tertundaWarning++;
            }
        }

        return [
            'total' => $items->count(),
            'sub_label' => $tertundaWarning > 0
                ? $tertundaWarning.' tertunda > '.self::DIPROSES_WARNING_HARI.' hari'
                : null,
            'severity' => $this->severityDariHitungan($urgent, $warning),
            'href' => route('surat-diproses.index'),
        ];
    }

    /**
     * @return array{total: int, sub_label: string|null, severity: string, href: string}
     */
    private function buatKartuSiapDiambil(CarbonInterface $hariIni, CarbonInterface $besok): array
    {
        $items = PengajuanSurat::query()
            ->with(['suratTerbit:id,pengajuan_id,tanggal_pengambilan,siap_diambil_at,updated_at'])
            ->where('status', PengajuanSurat::STATUS_SIAP_DIAMBIL)
            ->get();

        $warning = 0;
        $urgent = 0;
        $jadwalTerlewat = 0;

        foreach ($items as $item) {
            $tanggal = $item->suratTerbit?->tanggal_pengambilan
                ?->timezone('Asia/Jakarta')
                ->startOfDay();

            if ($tanggal === null) {
                continue;
            }

            if ($tanggal->lt($hariIni)) {
                $urgent++;
                $jadwalTerlewat++;
            } elseif ($tanggal->lte($besok)) {
                $warning++;
            }
        }

        return [
            'total' => $items->count(),
            'sub_label' => $jadwalTerlewat > 0
                ? $jadwalTerlewat.' jadwal terlewat'
                : null,
            'severity' => $this->severityDariHitungan($urgent, $warning),
            'href' => route('rekap-pengajuan.index', ['status' => PengajuanSurat::STATUS_SIAP_DIAMBIL]),
        ];
    }

    /**
     * @return array{total: int, sub_label: null, severity: string, href: string}
     */
    private function buatKartuSelesaiBulanIni(CarbonInterface $hariIni): array
    {
        $awalBulan = $hariIni->copy()->startOfMonth();
        $akhirBulan = $hariIni->copy()->endOfMonth()->endOfDay();

        $total = PengajuanSurat::query()
            ->where('status', PengajuanSurat::STATUS_SELESAI)
            ->whereHas('suratTerbit', function ($query) use ($awalBulan, $akhirBulan): void {
                $query->whereBetween('qr_digunakan_at', [$awalBulan, $akhirBulan]);
            })
            ->count();

        return [
            'total' => $total,
            'sub_label' => null,
            'severity' => 'normal',
            'href' => route('rekap-pengajuan.index', [
                'status' => PengajuanSurat::STATUS_SELESAI,
                'dari' => $awalBulan->toDateString(),
                'sampai' => $hariIni->toDateString(),
            ]),
        ];
    }

    /**
     * Maksimal 5 item mendesak dari semua status aktif.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function ambilPerluDitindaklanjuti(CarbonInterface $hariIni): Collection
    {
        $besok = $hariIni->copy()->addDay();

        $aktif = PengajuanSurat::query()
            ->with([
                'user:id,name',
                'jenisSurat:id,nama_surat',
                'suratTerbit:id,pengajuan_id,tanggal_terbit,tanggal_pengambilan,siap_diambil_at,updated_at',
            ])
            ->whereIn('status', PengajuanSurat::statusAktif())
            ->get();

        $ranked = $aktif
            ->map(function (PengajuanSurat $pengajuan) use ($hariIni, $besok): ?array {
                $priority = $this->prioritasMendesak($pengajuan, $hariIni);
                if ($priority === null) {
                    return null;
                }

                $hari = $pengajuan->hariDiStatusSaatIni($hariIni);

                return [
                    'id' => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'nama_warga' => $pengajuan->user?->name ?? '—',
                    'jenis_surat' => $pengajuan->jenisSurat?->nama_surat ?? '—',
                    'status' => $pengajuan->status,
                    'hari' => $hari,
                    'priority' => $priority,
                    'severity' => $this->severityBaris($pengajuan, $hariIni, $besok),
                ];
            })
            ->filter()
            ->sortBy([
                ['priority', 'asc'],
                ['hari', 'desc'],
            ])
            ->take(5)
            ->values();

        return $ranked;
    }

    /**
     * 7 pengajuan aktif, diurutkan paling lama di status saat ini.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function ambilPengajuanAktifTerbaru(CarbonInterface $hariIni): Collection
    {
        $besok = $hariIni->copy()->addDay();

        return PengajuanSurat::query()
            ->with([
                'user:id,name',
                'jenisSurat:id,nama_surat',
                'suratTerbit:id,pengajuan_id,tanggal_terbit,tanggal_pengambilan,siap_diambil_at,updated_at',
            ])
            ->whereIn('status', PengajuanSurat::statusAktif())
            ->get()
            ->map(function (PengajuanSurat $pengajuan) use ($hariIni, $besok): array {
                $hari = $pengajuan->hariDiStatusSaatIni($hariIni);

                return [
                    'id' => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'nama_warga' => $pengajuan->user?->name ?? '—',
                    'jenis_surat' => $pengajuan->jenisSurat?->nama_surat ?? '—',
                    'status' => $pengajuan->status,
                    'hari' => $hari,
                    'severity' => $this->severityBaris($pengajuan, $hariIni, $besok),
                    'masuk_at' => $pengajuan->waktuMasukStatusSaatIni()->timestamp,
                ];
            })
            // Paling lama di status saat ini di atas (masuk lebih dulu = timestamp lebih kecil)
            ->sortBy('masuk_at')
            ->take(7)
            ->values();
    }

    /**
     * Prioritas mendesak (1 = paling tinggi). Null = tidak masuk seksi.
     */
    private function prioritasMendesak(PengajuanSurat $pengajuan, CarbonInterface $hariIni): ?int
    {
        if ($pengajuan->status === PengajuanSurat::STATUS_SIAP_DIAMBIL) {
            $tanggal = $pengajuan->suratTerbit?->tanggal_pengambilan
                ?->timezone('Asia/Jakarta')
                ->startOfDay();

            if ($tanggal !== null && $tanggal->lt($hariIni)) {
                return 1;
            }

            return null;
        }

        $hari = $pengajuan->hariDiStatusSaatIni($hariIni);

        if (in_array($pengajuan->status, [PengajuanSurat::STATUS_DIAJUKAN], true)) {
            if ($hari > self::DIAJUKAN_URGENT_HARI) {
                return 2;
            }
            if ($hari > self::DIAJUKAN_WARNING_HARI) {
                return 4;
            }

            return null;
        }

        if (in_array($pengajuan->status, PengajuanSurat::statusDiprosesDashboard(), true)) {
            if ($hari > self::DIPROSES_URGENT_HARI) {
                return 3;
            }
            if ($hari > self::DIPROSES_WARNING_HARI) {
                return 5;
            }

            return null;
        }

        return null;
    }

    private function severityBaris(PengajuanSurat $pengajuan, CarbonInterface $hariIni, CarbonInterface $besok): string
    {
        if ($pengajuan->status === PengajuanSurat::STATUS_SIAP_DIAMBIL) {
            $tanggal = $pengajuan->suratTerbit?->tanggal_pengambilan
                ?->timezone('Asia/Jakarta')
                ->startOfDay();

            if ($tanggal === null) {
                return 'normal';
            }
            if ($tanggal->lt($hariIni)) {
                return 'urgent';
            }
            if ($tanggal->lte($besok)) {
                return 'warning';
            }

            return 'normal';
        }

        $hari = $pengajuan->hariDiStatusSaatIni($hariIni);

        if ($pengajuan->status === PengajuanSurat::STATUS_DIAJUKAN) {
            if ($hari > self::DIAJUKAN_URGENT_HARI) {
                return 'urgent';
            }
            if ($hari > self::DIAJUKAN_WARNING_HARI) {
                return 'warning';
            }

            return 'normal';
        }

        if (in_array($pengajuan->status, PengajuanSurat::statusDiprosesDashboard(), true)) {
            if ($hari > self::DIPROSES_URGENT_HARI) {
                return 'urgent';
            }
            if ($hari > self::DIPROSES_WARNING_HARI) {
                return 'warning';
            }
        }

        return 'normal';
    }

    private function severityDariHitungan(int $urgent, int $warning): string
    {
        if ($urgent > 0) {
            return 'urgent';
        }
        if ($warning > 0) {
            return 'warning';
        }

        return 'normal';
    }
}

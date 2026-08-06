<?php

namespace App\Livewire\Rekap;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Title('Rekap Pengajuan')]
class RekapPengajuan extends Component
{
    use WithPagination;

    /** Filter jenis surat; kosong = semua. */
    #[Url(as: 'jenis')]
    public string $jenisSuratFilter = '';

    /** Filter status; kosong = semua. */
    #[Url(as: 'status')]
    public string $statusFilter = '';

    /** Tanggal pengajuan dari (Y-m-d). */
    #[Url(as: 'dari')]
    public string $tanggalDari = '';

    /** Tanggal pengajuan sampai (Y-m-d). */
    #[Url(as: 'sampai')]
    public string $tanggalSampai = '';

    /**
     * Opsi filter status.
     *
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return ['' => 'Semua status'] + PengajuanSurat::statusOptions();
    }

    /**
     * Label status untuk tampilan tabel/export.
     */
    public function statusLabel(string $status): string
    {
        return PengajuanSurat::statusLabel($status);
    }

    public function updatedJenisSuratFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTanggalDari(): void
    {
        $this->resetPage();
    }

    public function updatedTanggalSampai(): void
    {
        $this->resetPage();
    }

    /**
     * Reset semua filter ke default (semua data).
     */
    public function resetFilters(): void
    {
        $this->reset('jenisSuratFilter', 'statusFilter', 'tanggalDari', 'tanggalSampai');
        $this->resetPage();
    }

    /**
     * Export CSV mengikuti filter tabel yang aktif (UTF-8 BOM).
     */
    public function exportCsv(): StreamedResponse
    {
        $this->validate([
            'tanggalDari' => ['nullable', 'date'],
            'tanggalSampai' => ['nullable', 'date', 'after_or_equal:tanggalDari'],
            'jenisSuratFilter' => ['nullable', 'string'],
            'statusFilter' => ['nullable', 'string'],
        ], [
            'tanggalSampai.after_or_equal' => 'Tanggal sampai harus sama atau setelah tanggal dari.',
        ]);

        $filename = 'rekap-pengajuan-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Excel membuka encoding dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nomor Pengajuan',
                'Nama Warga',
                'Jenis Surat',
                'Tanggal Pengajuan',
                'Status',
                'Admin Verifikator',
            ]);

            $this->filteredQuery()
                ->with([
                    'user:id,name',
                    'jenisSurat:id,nama_surat',
                    'diverifikasiOleh:id,name',
                ])
                ->orderBy('id')
                ->chunkById(200, function (Collection $rows) use ($handle): void {
                    foreach ($rows as $item) {
                        /** @var PengajuanSurat $item */
                        fputcsv($handle, [
                            $item->nomor_pengajuan,
                            $item->user?->name ?? '',
                            $item->jenisSurat?->nama_surat ?? '',
                            $item->tanggal_pengajuan?->format('Y-m-d') ?? '',
                            $this->statusLabel($item->status),
                            $item->diverifikasiOleh?->name ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Query dasar dengan filter tabel (jenis, status, rentang tanggal).
     *
     * @return Builder<PengajuanSurat>
     */
    protected function filteredQuery(): Builder
    {
        $query = PengajuanSurat::query();

        $this->applyJenisDanTanggalFilters($query);

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    /**
     * Query ringkasan: jenis + tanggal saja (tanpa filter status).
     *
     * @return Builder<PengajuanSurat>
     */
    protected function summaryQuery(): Builder
    {
        $query = PengajuanSurat::query();

        $this->applyJenisDanTanggalFilters($query);

        return $query;
    }

    /**
     * Terapkan filter jenis surat dan rentang tanggal.
     *
     * @param  Builder<PengajuanSurat>  $query
     */
    protected function applyJenisDanTanggalFilters(Builder $query): void
    {
        if ($this->jenisSuratFilter !== '') {
            $query->where('jenis_surat_id', (int) $this->jenisSuratFilter);
        }

        if ($this->tanggalDari !== '') {
            $query->whereDate('tanggal_pengajuan', '>=', $this->tanggalDari);
        }

        if ($this->tanggalSampai !== '') {
            $query->whereDate('tanggal_pengajuan', '<=', $this->tanggalSampai);
        }
    }

    /**
     * Hitung ringkasan per status (mengabaikan filter status tabel).
     *
     * @return array{total: int, diajukan: int, disetujui: int, diproses: int, siap_diambil: int, selesai: int, ditolak: int}
     */
    protected function ringkasanCounts(): array
    {
        $counts = $this->summaryQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $diajukan = (int) ($counts[PengajuanSurat::STATUS_DIAJUKAN] ?? 0);
        $disetujui = (int) ($counts[PengajuanSurat::STATUS_DISETUJUI] ?? 0);
        $diproses = (int) ($counts[PengajuanSurat::STATUS_DIPROSES] ?? 0);
        $siapDiambil = (int) ($counts[PengajuanSurat::STATUS_SIAP_DIAMBIL] ?? 0);
        $selesai = (int) ($counts[PengajuanSurat::STATUS_SELESAI] ?? 0);
        $ditolak = (int) ($counts[PengajuanSurat::STATUS_DITOLAK] ?? 0);

        return [
            'total' => $diajukan + $disetujui + $diproses + $siapDiambil + $selesai + $ditolak,
            'diajukan' => $diajukan,
            'disetujui' => $disetujui,
            'diproses' => $diproses,
            'siap_diambil' => $siapDiambil,
            'selesai' => $selesai,
            'ditolak' => $ditolak,
        ];
    }

    public function render(): View
    {
        $dateRangeInvalid = $this->tanggalDari !== ''
            && $this->tanggalSampai !== ''
            && $this->tanggalSampai < $this->tanggalDari;

        if ($dateRangeInvalid) {
            $this->addError('tanggalSampai', 'Tanggal sampai harus sama atau setelah tanggal dari.');
        }

        $pengajuanList = $dateRangeInvalid
            ? PengajuanSurat::query()->whereRaw('0 = 1')->paginate(10)
            : $this->filteredQuery()
                ->with([
                    'user:id,name',
                    'jenisSurat:id,nama_surat',
                    'diverifikasiOleh:id,name',
                ])
                ->latest('tanggal_pengajuan')
                ->latest('id')
                ->paginate(10);

        $ringkasan = $dateRangeInvalid
            ? [
                'total' => 0,
                'diajukan' => 0,
                'disetujui' => 0,
                'diproses' => 0,
                'siap_diambil' => 0,
                'selesai' => 0,
                'ditolak' => 0,
            ]
            : $this->ringkasanCounts();

        return view('livewire.rekap.rekap-pengajuan', [
            'pengajuanList' => $pengajuanList,
            'statusOptions' => $this->statusOptions(),
            'jenisSuratOptions' => JenisSurat::query()
                ->orderBy('nama_surat')
                ->get(['id', 'nama_surat']),
            'ringkasan' => $ringkasan,
        ])->layout('layouts::app');
    }
}

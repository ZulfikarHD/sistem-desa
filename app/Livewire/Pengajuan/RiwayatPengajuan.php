<?php

namespace App\Livewire\Pengajuan;

use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Status & Riwayat Pengajuan')]
class RiwayatPengajuan extends Component
{
    use WithPagination;

    /** Filter status pengajuan; kosong = semua status. */
    #[Url(as: 'status')]
    public string $statusFilter = '';

    /**
     * Opsi filter status untuk dropdown.
     *
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return ['' => 'Semua status'] + PengajuanSurat::statusOptions();
    }

    /**
     * Reset halaman pagination saat filter berubah.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = PengajuanSurat::query()
            ->with([
                'jenisSurat:id,nama_surat',
                'suratTerbit:id,pengajuan_id,file_path,tanggal_pengambilan,jam_kerja_label',
            ])
            ->where('user_id', auth()->id())
            ->latest('tanggal_pengajuan')
            ->latest('id');

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.pengajuan.riwayat-pengajuan', [
            'pengajuanList' => $query->paginate(10),
            'statusOptions' => $this->statusOptions(),
        ])->layout('layouts::app');
    }
}

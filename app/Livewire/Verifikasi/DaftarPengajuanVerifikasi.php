<?php

namespace App\Livewire\Verifikasi;

use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Daftar Pengajuan Surat')]
class DaftarPengajuanVerifikasi extends Component
{
    use WithPagination;

    /** Filter status; default menampilkan pengajuan menunggu verifikasi. */
    #[Url(as: 'status')]
    public string $statusFilter = PengajuanSurat::STATUS_DIAJUKAN;

    /**
     * Opsi filter status untuk dropdown admin.
     *
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return PengajuanSurat::statusOptions();
    }

    /**
     * Reset halaman pagination saat filter berubah.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Buka halaman detail pengajuan untuk verifikasi.
     */
    public function openDetail(int $pengajuanId): void
    {
        $this->redirect(route('verifikasi.show', $pengajuanId), navigate: true);
    }

    public function render(): View
    {
        $query = PengajuanSurat::query()
            ->with([
                'user:id,name',
                'jenisSurat:id,nama_surat',
            ])
            ->where('status', $this->statusFilter)
            ->latest('tanggal_pengajuan')
            ->latest('id');

        return view('livewire.verifikasi.daftar-pengajuan-verifikasi', [
            'pengajuanList' => $query->paginate(10),
            'statusOptions' => $this->statusOptions(),
        ])->layout('layouts::app');
    }
}

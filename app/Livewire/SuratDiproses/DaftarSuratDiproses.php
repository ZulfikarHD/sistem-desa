<?php

namespace App\Livewire\SuratDiproses;

use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Surat Diproses')]
class DaftarSuratDiproses extends Component
{
    use WithPagination;

    /**
     * Buka halaman detail surat yang sedang diproses.
     */
    public function openDetail(int $pengajuanId): void
    {
        $this->redirect(route('surat-diproses.show', $pengajuanId), navigate: true);
    }

    public function render(): View
    {
        // Hanya status diproses (US-8.5) — terpisah dari daftar menunggu verifikasi.
        $pengajuanList = PengajuanSurat::query()
            ->with([
                'user:id,name',
                'jenisSurat:id,nama_surat',
                'suratTerbit:id,pengajuan_id,nomor_surat,tanggal_terbit',
            ])
            ->where('status', PengajuanSurat::STATUS_DIPROSES)
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->paginate(10);

        return view('livewire.surat-diproses.daftar-surat-diproses', [
            'pengajuanList' => $pengajuanList,
        ])->layout('layouts::app');
    }
}

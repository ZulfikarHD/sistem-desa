<?php

namespace App\Livewire\Pengajuan;

use App\Models\PengajuanSurat;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Pengajuan')]
class DetailPengajuanWarga extends Component
{
    public PengajuanSurat $pengajuan;

    /**
     * Muat pengajuan milik warga yang sedang login.
     */
    public function mount(PengajuanSurat $pengajuan): void
    {
        abort_unless($pengajuan->user_id === auth()->id(), 403);

        $pengajuan->load([
            'jenisSurat:id,nama_surat,deskripsi',
            'dokumenPersyaratan:id,pengajuan_id,jenis_dokumen,file_path',
            'suratTerbit:id,pengajuan_id,nomor_surat,file_path,tanggal_pengambilan,jam_kerja_label',
        ]);

        $this->pengajuan = $pengajuan;
    }

    public function render(): View
    {
        return view('livewire.pengajuan.detail-pengajuan-warga')
            ->layout('layouts::app');
    }
}

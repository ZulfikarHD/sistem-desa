<?php

namespace App\Livewire\JenisSurat;

use App\Models\JenisSurat;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Jenis Surat')]
class DataJenisSurat extends Component
{
    use WithPagination;

    /** Kata kunci pencarian nama / deskripsi / persyaratan. */
    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** Tampilkan arsip (soft-deleted) alih-alih data aktif. */
    #[Url(as: 'arsip', except: false)]
    public bool $showTrashed = false;

    /** Apakah modal konfirmasi hapus permanen sedang terbuka. */
    public bool $showForceDeleteConfirm = false;

    /** ID jenis surat yang akan dihapus permanen. */
    public ?int $forceDeletingId = null;

    /**
     * Reset halaman daftar saat kata kunci pencarian berubah.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset halaman saat beralih antara daftar aktif dan arsip.
     */
    public function updatedShowTrashed(): void
    {
        $this->resetPage();
        $this->closeForceDeleteConfirm();
    }

    /**
     * Soft delete: arsipkan jenis surat (masih bisa dipulihkan).
     */
    public function softDelete(int $id): void
    {
        $jenisSurat = JenisSurat::query()->findOrFail($id);
        $jenisSurat->delete();

        Flux::toast(variant: 'success', text: 'Jenis surat dipindahkan ke arsip.');
        $this->resetPage();
    }

    /**
     * Pulihkan jenis surat dari arsip.
     */
    public function restore(int $id): void
    {
        $jenisSurat = JenisSurat::onlyTrashed()->findOrFail($id);
        $jenisSurat->restore();

        Flux::toast(variant: 'success', text: 'Jenis surat berhasil dipulihkan.');
        $this->resetPage();
    }

    /**
     * Buka konfirmasi hapus permanen.
     */
    public function confirmForceDelete(int $id): void
    {
        JenisSurat::onlyTrashed()->findOrFail($id);

        $this->forceDeletingId = $id;
        $this->showForceDeleteConfirm = true;
    }

    /**
     * Tutup modal konfirmasi hapus permanen.
     */
    public function closeForceDeleteConfirm(): void
    {
        $this->showForceDeleteConfirm = false;
        $this->forceDeletingId = null;
    }

    /**
     * Hard delete: hapus permanen dari database (hanya dari arsip).
     * Baris persyaratan ikut terhapus via cascade FK.
     */
    public function forceDelete(): void
    {
        if ($this->forceDeletingId === null) {
            return;
        }

        $jenisSurat = JenisSurat::onlyTrashed()->findOrFail($this->forceDeletingId);
        $jenisSurat->forceDelete();

        $this->closeForceDeleteConfirm();
        Flux::toast(variant: 'success', text: 'Jenis surat dihapus permanen.');
        $this->resetPage();
    }

    public function render(): View
    {
        $query = $this->showTrashed
            ? JenisSurat::onlyTrashed()->latest('deleted_at')
            : JenisSurat::query()->latest();

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('nama_surat', 'like', $term)
                    ->orWhere('deskripsi', 'like', $term)
                    ->orWhere('persyaratan_dokumen', 'like', $term);
            });
        }

        return view('livewire.jenis-surat.data-jenis-surat', [
            'jenisSuratList' => $query->withCount('persyaratan')->paginate(10),
        ]);
    }
}

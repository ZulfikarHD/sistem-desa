<?php

namespace App\Livewire\JenisSurat;

use App\Models\JenisSurat;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tampilan read-only daftar & detail persyaratan dokumen (US-2.2 / US-2.3 + US-9.4 badge).
 */
#[Title('Persyaratan Dokumen')]
class PersyaratanDokumen extends Component
{
    use WithPagination;

    /** Kata kunci pencarian nama / deskripsi / teks syarat. */
    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** Apakah modal detail sedang terbuka. */
    public bool $showDetail = false;

    /** ID jenis surat yang sedang dilihat detailnya. */
    public ?int $selectedId = null;

    /**
     * Reset halaman daftar saat kata kunci pencarian berubah.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Buka modal detail untuk jenis surat yang dipilih.
     */
    public function openDetail(int $id): void
    {
        // Pastikan hanya data aktif (bukan arsip) yang bisa dibuka
        JenisSurat::query()->findOrFail($id);

        $this->selectedId = $id;
        $this->showDetail = true;
    }

    /**
     * Tutup modal detail dan bersihkan state pilihan.
     */
    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->selectedId = null;
    }

    public function render(): View
    {
        $query = JenisSurat::query()
            ->with('persyaratan')
            ->latest();

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('nama_surat', 'like', $term)
                    ->orWhere('deskripsi', 'like', $term)
                    ->orWhere('persyaratan_dokumen', 'like', $term)
                    ->orWhereHas('persyaratan', function ($persyaratanQuery) use ($term): void {
                        $persyaratanQuery->where('nama', 'like', $term);
                    });
            });
        }

        $selectedJenisSurat = null;
        if ($this->showDetail && $this->selectedId !== null) {
            $selectedJenisSurat = JenisSurat::query()
                ->with('persyaratan')
                ->find($this->selectedId);
        }

        // Guest memakai layout publik (app layout bergantung auth()->user())
        $layout = auth()->check() ? 'layouts::app' : 'layouts::public';

        return view('livewire.jenis-surat.persyaratan-dokumen', [
            'jenisSuratList' => $query->paginate(9),
            'selectedJenisSurat' => $selectedJenisSurat,
        ])->layout($layout);
    }
}

<?php

namespace App\Livewire\JenisSurat;

use App\Models\JenisSurat;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
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

    /** Apakah modal form tambah/ubah sedang terbuka. */
    public bool $showForm = false;

    /** Apakah modal konfirmasi hapus permanen sedang terbuka. */
    public bool $showForceDeleteConfirm = false;

    /** ID jenis surat yang sedang diubah; null = mode tambah. */
    public ?int $editingId = null;

    /** ID jenis surat yang akan dihapus permanen. */
    public ?int $forceDeletingId = null;

    public string $nama_surat = '';

    public string $deskripsi = '';

    public string $persyaratan_dokumen = '';

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
        $this->closeForm();
        $this->closeForceDeleteConfirm();
    }

    /**
     * Buka modal form untuk menambah jenis surat baru.
     */
    public function create(): void
    {
        if ($this->showTrashed) {
            return;
        }

        $this->resetForm();
        $this->showForm = true;
    }

    /**
     * Buka modal form untuk mengubah jenis surat yang dipilih.
     */
    public function edit(int $id): void
    {
        if ($this->showTrashed) {
            return;
        }

        $jenisSurat = JenisSurat::query()->findOrFail($id);

        $this->editingId = $jenisSurat->id;
        $this->nama_surat = $jenisSurat->nama_surat;
        $this->deskripsi = $jenisSurat->deskripsi ?? '';
        $this->persyaratan_dokumen = $jenisSurat->persyaratan_dokumen;
        $this->resetValidation();
        $this->showForm = true;
    }

    /**
     * Tutup modal dan bersihkan state form.
     */
    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
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

    /**
     * Simpan data baru atau pembaruan jenis surat.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules());

        $payload = [
            'nama_surat' => $validated['nama_surat'],
            'deskripsi' => $validated['deskripsi'] !== '' ? $validated['deskripsi'] : null,
            'persyaratan_dokumen' => $validated['persyaratan_dokumen'],
        ];

        if ($this->editingId === null) {
            JenisSurat::query()->create($payload);
            Flux::toast(variant: 'success', text: 'Jenis surat berhasil ditambahkan.');
        } else {
            $jenisSurat = JenisSurat::query()->findOrFail($this->editingId);
            $jenisSurat->update($payload);
            Flux::toast(variant: 'success', text: 'Jenis surat berhasil diperbarui.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    /**
     * Aturan validasi form tambah/ubah.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'nama_surat' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jenis_surat', 'nama_surat')->ignore($this->editingId),
            ],
            'deskripsi' => ['nullable', 'string'],
            'persyaratan_dokumen' => ['required', 'string'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nama_surat.required' => 'Nama surat wajib diisi.',
            'nama_surat.unique' => 'Nama surat sudah digunakan.',
            'nama_surat.max' => 'Nama surat maksimal 100 karakter.',
            'persyaratan_dokumen.required' => 'Persyaratan dokumen wajib diisi.',
        ];
    }

    /**
     * Bersihkan field form ke keadaan awal (mode tambah).
     * Dipanggil juga saat modal ditutup (wire:close).
     */
    public function resetForm(): void
    {
        $this->editingId = null;
        $this->nama_surat = '';
        $this->deskripsi = '';
        $this->persyaratan_dokumen = '';
        $this->resetValidation();
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
            'jenisSuratList' => $query->paginate(10),
        ]);
    }
}

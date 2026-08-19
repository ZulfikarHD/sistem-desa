<?php

namespace App\Livewire\Pengguna;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Pengguna')]
class DataPengguna extends Component
{
    use WithPagination;

    /** Opsi filter peran pengguna. */
    public const ROLE_OPTIONS = [
        '' => 'Semua peran',
        'warga' => 'Warga',
        'admin' => 'Admin',
    ];

    /** Kata kunci pencarian nama, email, atau NIK. */
    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** Filter peran: kosong = semua. */
    #[Url(as: 'role', except: '')]
    public string $roleFilter = '';

    /**
     * Reset halaman daftar saat kata kunci pencarian berubah.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset halaman saat filter peran berubah.
     */
    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = User::query()->latest();

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('nik', 'like', $term);
            });
        }

        if ($this->roleFilter !== '' && array_key_exists($this->roleFilter, self::ROLE_OPTIONS)) {
            $query->where('role', $this->roleFilter);
        }

        return view('livewire.pengguna.data-pengguna', [
            'penggunaList' => $query->paginate(10),
            'roleOptions' => self::ROLE_OPTIONS,
        ]);
    }
}

<?php

namespace App\Livewire\Pengguna;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tambah Pengguna')]
class FormPengguna extends Component
{
    use PasswordValidationRules, ProfileValidationRules;

    /** Opsi peran yang dapat dipilih admin. */
    public const ROLE_OPTIONS = [
        'warga' => 'Warga',
        'admin' => 'Admin / Petugas Desa',
    ];

    public string $nik = '';

    public string $name = '';

    public string $no_telepon = '';

    public string $alamat = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'warga';

    /**
     * Validasi dan simpan akun pengguna baru.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->messages());

        User::query()->create([
            'nik' => $validated['nik'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        Flux::toast(variant: 'success', text: 'Pengguna berhasil ditambahkan.');

        $this->redirect(route('pengguna.index'), navigate: true);
    }

    /**
     * Kembali ke daftar tanpa menyimpan.
     */
    public function cancel(): void
    {
        $this->redirect(route('pengguna.index'), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            ...$this->registrationRules(),
            'password' => $this->passwordRules(),
            'role' => ['required', 'string', Rule::in(array_keys(self::ROLE_OPTIONS))],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran tidak valid.',
        ];
    }

    public function render(): View
    {
        return view('livewire.pengguna.form-pengguna', [
            'roleOptions' => self::ROLE_OPTIONS,
        ]);
    }
}

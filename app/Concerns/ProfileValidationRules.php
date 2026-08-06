<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Aturan validasi untuk registrasi akun warga (US-1.1).
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function registrationRules(): array
    {
        return [
            'nik' => $this->nikRules(),
            'name' => $this->nameRules(),
            'no_telepon' => $this->phoneRules(),
            'alamat' => $this->addressRules(),
            'email' => $this->emailRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:100'];
    }

    /**
     * Validasi NIK: wajib, tepat 16 digit angka, unik.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nikRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'digits:16',
            $userId === null
                ? Rule::unique(User::class, 'nik')
                : Rule::unique(User::class, 'nik')->ignore($userId),
        ];
    }

    /**
     * Validasi nomor telepon.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function phoneRules(): array
    {
        return ['required', 'string', 'max:20'];
    }

    /**
     * Validasi alamat.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function addressRules(): array
    {
        return ['required', 'string'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:100',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}

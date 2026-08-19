<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="pengguna-form-title">{{ __('Tambah Pengguna') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Buat akun warga atau admin. Email langsung ditandai terverifikasi sehingga pengguna dapat login tanpa verifikasi surel.') }}
            </flux:text>
        </div>

        <flux:button
            variant="ghost"
            icon="arrow-left"
            wire:click="cancel"
            data-test="pengguna-back-button"
        >
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <form wire:submit="save" class="mx-auto w-full max-w-2xl space-y-6" data-test="pengguna-form">
        <flux:select
            wire:model="role"
            label="{{ __('Peran') }}"
            description="{{ __('Pilih Warga untuk pemohon surat, atau Admin untuk petugas kantor desa.') }}"
            data-test="pengguna-role-input"
        >
            @foreach ($roleOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model="nik"
            label="{{ __('NIK') }}"
            inputmode="numeric"
            maxlength="16"
            placeholder="{{ __('16 digit NIK') }}"
            data-test="pengguna-nik-input"
        />

        <flux:input
            wire:model="name"
            label="{{ __('Nama Lengkap') }}"
            placeholder="{{ __('Nama lengkap') }}"
            data-test="pengguna-name-input"
        />

        <flux:input
            wire:model="no_telepon"
            label="{{ __('No. Telepon') }}"
            inputmode="tel"
            placeholder="{{ __('08xxxxxxxxxx') }}"
            data-test="pengguna-phone-input"
        />

        <flux:textarea
            wire:model="alamat"
            label="{{ __('Alamat') }}"
            rows="3"
            placeholder="{{ __('Alamat lengkap sesuai KTP') }}"
            data-test="pengguna-address-input"
        />

        <flux:input
            wire:model="email"
            type="email"
            label="{{ __('Email') }}"
            placeholder="email@example.com"
            data-test="pengguna-email-input"
        />

        <flux:input
            wire:model="password"
            type="password"
            label="{{ __('Password') }}"
            placeholder="{{ __('Minimal 8 karakter') }}"
            viewable
            data-test="pengguna-password-input"
        />

        <flux:input
            wire:model="password_confirmation"
            type="password"
            label="{{ __('Konfirmasi Password') }}"
            placeholder="{{ __('Ulangi password') }}"
            viewable
            data-test="pengguna-password-confirmation-input"
        />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="button" variant="ghost" wire:click="cancel" data-test="pengguna-cancel-button">
                {{ __('Batal') }}
            </flux:button>
            <flux:button type="submit" variant="primary" data-test="pengguna-save-button">
                {{ __('Simpan Pengguna') }}
            </flux:button>
        </div>
    </form>
</div>

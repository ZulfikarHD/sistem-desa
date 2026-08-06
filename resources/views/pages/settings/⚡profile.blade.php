<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pengaturan Profil')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';

    public string $no_telepon = '';

    public string $alamat = '';

    public string $email = '';

    /** NIK hanya ditampilkan, tidak dapat diubah (US-1.4). */
    #[Locked]
    public string $nik = '';

    /** Role hanya ditampilkan, tidak dapat diubah (US-1.4). */
    #[Locked]
    public string $role = '';

    /**
     * Isi form dari data user yang sedang login.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->no_telepon = $user->no_telepon;
        $this->alamat = $user->alamat;
        $this->email = $user->email;
        $this->nik = $user->nik;
        $this->role = $user->role;
    }

    /**
     * Perbarui data profil (tanpa NIK dan role).
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        // Hanya field yang diizinkan — NIK/role tidak pernah diisi dari request
        $user->fill([
            'name' => $validated['name'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profil berhasil diperbarui.'));
    }

    /**
     * Kirim ulang email verifikasi.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route(Auth::user()->homeRouteName(), absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    #[Computed]
    public function roleLabel(): string
    {
        return $this->role === 'admin' ? __('Admin') : __('Warga');
    }
}; ?>

<section class="w-full" data-test="profile-page">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Pengaturan Profil') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profil')" :subheading="__('Perbarui data kontak Anda')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" data-test="profile-form">
            <flux:input
                :value="$nik"
                :label="__('NIK')"
                type="text"
                readonly
                variant="filled"
                data-test="profile-nik"
                :description="__('NIK tidak dapat diubah sendiri.')"
            />

            <flux:input
                :value="$this->roleLabel"
                :label="__('Role')"
                type="text"
                readonly
                variant="filled"
                data-test="profile-role"
                :description="__('Role tidak dapat diubah sendiri.')"
            />

            <flux:input
                wire:model="name"
                :label="__('Nama')"
                type="text"
                required
                autofocus
                autocomplete="name"
                data-test="profile-name"
            />

            <flux:input
                wire:model="no_telepon"
                :label="__('No. Telepon')"
                type="text"
                inputmode="tel"
                required
                autocomplete="tel"
                data-test="profile-phone"
            />

            <flux:textarea
                wire:model="alamat"
                :label="__('Alamat')"
                required
                rows="3"
                data-test="profile-address"
            />

            <div>
                <flux:input
                    wire:model="email"
                    :label="__('Email')"
                    type="email"
                    required
                    autocomplete="email"
                    data-test="profile-email"
                />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Alamat email Anda belum diverifikasi.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Klik di sini untuk kirim ulang email verifikasi.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Simpan') }}
                    </flux:button>
                </div>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>

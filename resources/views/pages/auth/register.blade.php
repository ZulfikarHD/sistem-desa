<x-layouts::auth :title="__('Registrasi')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Registrasi Akun Warga')"
            :description="__('Isi data diri Anda untuk membuat akun pengajuan surat')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- NIK -->
            <flux:input
                name="nik"
                :label="__('NIK')"
                :value="old('nik')"
                type="text"
                inputmode="numeric"
                maxlength="16"
                required
                autofocus
                autocomplete="off"
                :placeholder="__('16 digit NIK')"
                data-test="register-nik"
            />

            <!-- Nama -->
            <flux:input
                name="name"
                :label="__('Nama')"
                :value="old('name')"
                type="text"
                required
                autocomplete="name"
                :placeholder="__('Nama lengkap')"
                data-test="register-name"
            />

            <!-- No. Telepon -->
            <flux:input
                name="no_telepon"
                :label="__('No. Telepon')"
                :value="old('no_telepon')"
                type="text"
                inputmode="tel"
                required
                autocomplete="tel"
                :placeholder="__('08xxxxxxxxxx')"
                data-test="register-phone"
            />

            <!-- Alamat -->
            <flux:textarea
                name="alamat"
                :label="__('Alamat')"
                required
                rows="3"
                :placeholder="__('Alamat lengkap sesuai KTP')"
                data-test="register-address"
            >{{ old('alamat') }}</flux:textarea>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
                data-test="register-email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Minimal 8 karakter')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                data-test="register-password"
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Konfirmasi Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulangi password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                data-test="register-password-confirmation"
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Daftar') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Sudah punya akun?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Masuk') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>

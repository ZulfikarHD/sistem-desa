<x-layouts::auth :title="__('Masuk')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Masuk ke akun Anda')"
            :description="__('Masukkan email dan password untuk masuk')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
                data-test="login-email"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                    data-test="login-password"
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Lupa Password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Masuk') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-brand-ink/70 rtl:space-x-reverse">
            <span>{{ __('Belum punya akun?') }}</span>
            <flux:link :href="route('register')" wire:navigate class="!text-brand-leaf !font-semibold">{{ __('Daftar') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>

<x-layouts::auth :title="__('Lupa Password')">
    <div class="flex flex-col gap-6" data-test="forgot-password-page">
        <x-auth-header
            :title="__('Lupa Password')"
            :description="__('Masukkan email Anda untuk menerima tautan reset password')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6" data-test="forgot-password-form">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
                data-test="forgot-password-email"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Kirim Tautan Reset Password') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Atau, kembali ke') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('halaman masuk') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>

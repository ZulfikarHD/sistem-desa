<x-layouts::auth :title="__('Reset Password')">
    <div class="flex flex-col gap-6" data-test="reset-password-page">
        <x-auth-header
            :title="__('Reset Password')"
            :description="__('Masukkan password baru Anda di bawah ini')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6" data-test="reset-password-form">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Email')"
                type="email"
                required
                autocomplete="email"
                data-test="reset-password-email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password Baru')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Minimal 8 karakter')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                data-test="reset-password-password"
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Konfirmasi Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulangi password baru')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                data-test="reset-password-password-confirmation"
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Reset Password') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>

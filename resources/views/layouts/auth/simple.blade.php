<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-brand-paper font-sans text-brand-ink antialiased">
        <div class="flex min-h-dvh flex-col items-center justify-center px-6 py-10">
            <div class="flex w-full max-w-md flex-col gap-8">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-brand-mist">
                        <x-app-logo-icon class="size-6 fill-current text-brand-forest" />
                    </span>
                    <span class="font-display text-xl font-semibold text-brand-forest">
                        {{ config('app.name') }}
                    </span>
                </a>

                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

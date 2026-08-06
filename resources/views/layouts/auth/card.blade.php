<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-brand-mist font-sans text-brand-ink antialiased">
        <div class="flex min-h-dvh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-brand-forest/10">
                        <x-app-logo-icon class="size-6 fill-current text-brand-forest" />
                    </span>
                    <span class="font-display text-lg font-semibold text-brand-forest">
                        {{ config('app.name') }}
                    </span>
                </a>

                <div class="rounded-2xl border border-brand-forest/10 bg-white text-brand-ink shadow-sm">
                    <div class="px-8 py-8 sm:px-10">{{ $slot }}</div>
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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-brand-paper font-sans text-brand-ink antialiased">
        <div class="grid min-h-dvh lg:grid-cols-2">
            {{-- Panel brand (desktop) --}}
            <aside class="relative hidden flex-col overflow-hidden bg-brand-fields p-10 text-white lg:flex">
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.1]"
                    aria-hidden="true"
                    style="background-image: repeating-linear-gradient(
                        -12deg,
                        transparent,
                        transparent 24px,
                        rgb(255 255 255 / 0.4) 24px,
                        rgb(255 255 255 / 0.4) 25px
                    );"
                ></div>

                <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">
                        <x-app-logo-icon class="size-6 fill-current text-brand-sun" />
                    </span>
                    <span class="font-display text-xl font-semibold tracking-tight">
                        {{ config('app.name') }}
                    </span>
                </a>

                <div class="relative z-10 mt-auto max-w-md space-y-4 pb-6">
                    <p class="font-display text-3xl font-semibold leading-tight tracking-tight">
                        {{ __('Pelayanan surat desa yang lebih mudah.') }}
                    </p>
                    <p class="text-base leading-relaxed text-white/75">
                        {{ __('Ajukan surat keterangan secara daring. Warga mendaftar dengan NIK; petugas desa memverifikasi dan menerbitkan surat.') }}
                    </p>
                </div>
            </aside>

            {{-- Form --}}
            <div class="flex flex-col justify-center px-6 py-10 sm:px-10 lg:px-14">
                <a
                    href="{{ route('home') }}"
                    class="mb-8 flex items-center gap-3 lg:hidden"
                    wire:navigate
                >
                    <span class="flex size-9 items-center justify-center rounded-lg bg-brand-mist">
                        <x-app-logo-icon class="size-5 fill-current text-brand-forest" />
                    </span>
                    <span class="font-display text-lg font-semibold text-brand-forest">
                        {{ config('app.name') }}
                    </span>
                </a>

                <div class="mx-auto w-full max-w-md">
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

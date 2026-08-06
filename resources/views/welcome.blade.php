<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('Beranda')])
    </head>
    <body class="min-h-dvh bg-brand-paper font-sans text-brand-ink antialiased">
        {{-- Hero full-bleed: brand + headline + CTA --}}
        <section class="relative flex min-h-dvh flex-col overflow-hidden bg-brand-fields text-white">
            {{-- Pola sawah subtle --}}
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.12] animate-fade-soft"
                aria-hidden="true"
                style="background-image: repeating-linear-gradient(
                    -12deg,
                    transparent,
                    transparent 28px,
                    rgb(255 255 255 / 0.35) 28px,
                    rgb(255 255 255 / 0.35) 29px
                );"
            ></div>

            <header class="relative z-10 flex items-center justify-between px-6 py-5 sm:px-10 lg:px-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">
                        <x-app-logo-icon class="size-6 fill-current text-brand-sun" />
                    </span>
                    <span class="font-display text-lg font-semibold tracking-tight sm:text-xl">
                        {{ config('app.name') }}
                    </span>
                </a>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-2 sm:gap-3">
                        @auth
                            <a
                                href="{{ route(auth()->user()->homeRouteName()) }}"
                                class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-brand-forest transition hover:bg-brand-mist"
                                wire:navigate
                            >
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10 hover:text-white sm:px-4"
                                wire:navigate
                                data-test="welcome-login"
                            >
                                {{ __('Masuk') }}
                            </a>
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-lg bg-brand-sun px-4 py-2 text-sm font-semibold text-brand-ink transition hover:brightness-110"
                                    wire:navigate
                                    data-test="welcome-register"
                                >
                                    {{ __('Daftar') }}
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <div class="relative z-10 flex flex-1 flex-col justify-center px-6 pb-20 pt-8 sm:px-10 lg:px-16 lg:pb-28">
                <div class="max-w-2xl">
                    <p class="animate-rise font-display text-4xl font-semibold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                        {{ config('app.name') }}
                    </p>

                    <h1 class="animate-rise-delay mt-5 max-w-xl text-xl font-medium leading-snug text-white/95 sm:text-2xl">
                        {{ __('Ajukan surat keterangan desa secara daring, tanpa antre di kantor.') }}
                    </h1>

                    <p class="animate-rise-delay mt-4 max-w-lg text-base leading-relaxed text-white/75 sm:text-lg">
                        {{ __('Layanan untuk warga dan petugas desa — daftar sekali, ajukan surat kapan saja.') }}
                    </p>

                    @guest
                        <div class="animate-rise-delay-2 mt-10 flex flex-wrap items-center gap-3">
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-sun px-6 py-3 text-sm font-semibold text-brand-ink shadow-lg shadow-black/20 transition hover:brightness-110"
                                    wire:navigate
                                >
                                    {{ __('Daftar sebagai Warga') }}
                                </a>
                            @endif
                            @if (Route::has('login'))
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-white/30 bg-white/5 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/15"
                                    wire:navigate
                                >
                                    {{ __('Sudah punya akun? Masuk') }}
                                </a>
                            @endif
                        </div>
                    @endguest
                </div>
            </div>

            {{-- Dekorasi siluet dokumen --}}
            <div
                class="pointer-events-none absolute -right-8 bottom-0 hidden w-[min(48vw,28rem)] opacity-20 lg:block"
                aria-hidden="true"
            >
                <svg viewBox="0 0 320 420" fill="none" class="h-auto w-full text-white">
                    <rect x="40" y="20" width="220" height="380" rx="12" stroke="currentColor" stroke-width="3" />
                    <path d="M70 80h160M70 120h140M70 160h160M70 200h100" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    <circle cx="220" cy="300" r="48" stroke="currentColor" stroke-width="3" />
                    <path d="M200 300h40M220 280v40" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                </svg>
            </div>
        </section>

        @fluxScripts
    </body>
</html>

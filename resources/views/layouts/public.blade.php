<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title ?? null])
    </head>
    <body class="min-h-dvh bg-brand-paper font-sans text-brand-ink antialiased">
        <header class="border-b border-brand-forest/10 bg-white/90 backdrop-blur-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate data-test="public-layout-home">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-brand-forest text-brand-sun">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </span>
                    <span class="font-display text-base font-semibold tracking-tight sm:text-lg">
                        {{ config('app.name') }}
                    </span>
                </a>

                <nav class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a
                            href="{{ route(auth()->user()->homeRouteName()) }}"
                            class="rounded-lg bg-brand-forest px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110"
                            wire:navigate
                            data-test="public-layout-dashboard"
                        >
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-brand-ink/80 transition hover:bg-brand-forest/5 hover:text-brand-ink sm:px-4"
                                wire:navigate
                                data-test="public-layout-login"
                            >
                                {{ __('Masuk') }}
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="rounded-lg bg-brand-sun px-4 py-2 text-sm font-semibold text-brand-ink transition hover:brightness-110"
                                wire:navigate
                                data-test="public-layout-register"
                            >
                                {{ __('Daftar') }}
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route(auth()->user()->homeRouteName()) }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item
                    icon="layout-grid"
                    :href="route(auth()->user()->homeRouteName())"
                    :current="request()->routeIs('dashboard', 'dashboard.admin')"
                    wire:navigate
                >
                    {{ __('Dashboard') }}
                </flux:navbar.item>

                @if (auth()->user()->isWarga())
                    <flux:navbar.item
                        icon="clipboard-document-list"
                        :href="route('persyaratan-dokumen.index')"
                        :current="request()->routeIs('persyaratan-dokumen.*')"
                        wire:navigate
                        data-test="header-persyaratan-dokumen"
                    >
                        {{ __('Persyaratan Dokumen') }}
                    </flux:navbar.item>
                @endif
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
                <flux:tooltip :content="__('Repository')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </flux:tooltip>
                <flux:tooltip :content="__('Documentation')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        :label="__('Documentation')"
                    />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route(auth()->user()->homeRouteName()) }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item
                        icon="layout-grid"
                        :href="route(auth()->user()->homeRouteName())"
                        :current="request()->routeIs('dashboard', 'dashboard.admin')"
                        wire:navigate
                    >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @if (auth()->user()->isWarga())
                        <flux:sidebar.item
                            icon="clipboard-document-list"
                            :href="route('persyaratan-dokumen.index')"
                            :current="request()->routeIs('persyaratan-dokumen.*')"
                            wire:navigate
                            data-test="sidebar-persyaratan-dokumen-mobile"
                        >
                            {{ __('Persyaratan Dokumen') }}
                        </flux:sidebar.item>
                    @endif

                    @if (auth()->user()->isAdmin())
                        <flux:sidebar.item
                            icon="document-text"
                            :href="route('jenis-surat.index')"
                            :current="request()->routeIs('jenis-surat.*')"
                            wire:navigate
                            data-test="sidebar-jenis-surat-mobile"
                        >
                            {{ __('Jenis Surat') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="clipboard-document-check"
                            :href="route('verifikasi.index')"
                            :current="request()->routeIs('verifikasi.*')"
                            wire:navigate
                            data-test="sidebar-verifikasi-pengajuan-mobile"
                        >
                            {{ __('Verifikasi Pengajuan') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="qr-code"
                            :href="route('scan-qr-pengambilan.index')"
                            :current="request()->routeIs('scan-qr-pengambilan.*')"
                            wire:navigate
                            data-test="sidebar-scan-qr-pengambilan-mobile"
                        >
                            {{ __('Scan QR Pengambilan') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="chart-bar"
                            :href="route('rekap-pengajuan.index')"
                            :current="request()->routeIs('rekap-pengajuan.*')"
                            wire:navigate
                            data-test="sidebar-rekap-pengajuan-mobile"
                        >
                            {{ __('Rekap Pengajuan') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

<div class="flex h-full w-full flex-1 flex-col gap-4 p-4 md:p-6" data-test="dashboard-warga">
    {{-- Header tipis + CTA dekat atas agar tidak terkubur di bawah daftar panjang --}}
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500" data-test="dashboard-warga-heading">
                {{ __('Dashboard Warga') }}
            </p>
            <h1 class="truncate text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                @if ($pengajuanAktif->isEmpty())
                    {{ __('Mulai pengajuan surat Anda') }}
                @elseif ($pengajuanAktif->count() === 1)
                    {{ __('Status surat Anda') }}
                @else
                    {{ __('Status :count surat aktif', ['count' => $pengajuanAktif->count()]) }}
                @endif
            </h1>
        </div>

        @if ($pengajuanAktif->isNotEmpty())
            <flux:button
                size="sm"
                variant="filled"
                icon="document-plus"
                :href="route('pengajuan-surat.create')"
                wire:navigate
                data-test="dashboard-warga-ajukan-baru"
            >
                {{ __('Ajukan Surat Baru') }}
            </flux:button>
        @endif
    </div>

    @if ($unreadCount > 0)
        <div
            class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-300/80 bg-amber-50 px-3 py-2 dark:border-amber-600/50 dark:bg-amber-950/40"
            data-test="dashboard-warga-notif-banner"
        >
            <flux:text class="text-sm text-amber-900 dark:text-amber-100">
                {{ __('Anda memiliki :count notifikasi baru', ['count' => $unreadCount]) }}
            </flux:text>
            <button
                type="button"
                class="text-sm font-medium text-amber-800 underline hover:no-underline dark:text-amber-200"
                data-test="dashboard-warga-notif-banner-link"
                x-on:click="$dispatch('buka-panel-notifikasi')"
            >
                {{ __('Lihat notifikasi') }}
            </button>
        </div>
    @endif

    @if ($pengajuanAktif->isEmpty())
        <section
            class="rounded-xl border border-dashed border-brand-leaf/30 bg-brand-mist/60 px-5 py-7 text-center dark:border-brand-leaf/40 dark:bg-zinc-900/50"
            data-test="dashboard-warga-hero-empty"
        >
            <flux:heading size="lg">{{ __('Belum ada pengajuan aktif') }}</flux:heading>
            <flux:text class="mx-auto mt-1.5 max-w-md text-sm">
                {{ __('Ajukan surat keterangan sekarang untuk memulai proses di kantor desa.') }}
            </flux:text>
            <div class="mt-4">
                <flux:button
                    variant="primary"
                    icon="document-plus"
                    :href="route('pengajuan-surat.create')"
                    wire:navigate
                    data-test="dashboard-warga-cta-ajukan"
                >
                    {{ __('Ajukan Surat Sekarang') }}
                </flux:button>
            </div>
        </section>
    @else
        <section class="space-y-2.5" data-test="dashboard-warga-hero">
            @foreach ($pengajuanAktif as $item)
                @php
                    /** @var \App\Models\PengajuanSurat $pengajuan */
                    $pengajuan = $item['model'];
                    $langkahAktif = $item['langkah_aktif'];
                    $isSiap = $pengajuan->status === \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL;
                    $heroClass = match ($pengajuan->status) {
                        \App\Models\PengajuanSurat::STATUS_DIAJUKAN => 'border-l-4 border-l-amber-500 border border-amber-200/80 bg-amber-50/90 dark:border-amber-700 dark:border-l-amber-400 dark:bg-amber-950/30',
                        \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                        \App\Models\PengajuanSurat::STATUS_DIPROSES => 'border-l-4 border-l-blue-500 border border-blue-200/80 bg-blue-50/90 dark:border-blue-700 dark:border-l-blue-400 dark:bg-blue-950/30',
                        \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'border-l-4 border-l-green-500 border border-green-200/80 bg-green-50/90 dark:border-green-700 dark:border-l-green-400 dark:bg-green-950/30',
                        default => 'border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900',
                    };
                @endphp
                <article
                    class="rounded-xl p-3.5 sm:p-4 {{ $heroClass }}"
                    wire:key="dashboard-warga-hero-{{ $pengajuan->id }}"
                    data-test="dashboard-warga-hero-card-{{ $pengajuan->id }}"
                    data-status="{{ $pengajuan->status }}"
                >
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <flux:heading size="lg" class="!text-base sm:!text-lg" data-test="dashboard-warga-hero-jenis-{{ $pengajuan->id }}">
                                    {{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}
                                </flux:heading>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <flux:text class="text-xs" data-test="dashboard-warga-hero-nomor-{{ $pengajuan->id }}">
                                        {{ $pengajuan->nomor_pengajuan }}
                                    </flux:text>
                                    <span class="text-zinc-300 dark:text-zinc-600" aria-hidden="true">·</span>
                                    <flux:badge
                                        size="sm"
                                        :color="\App\Models\PengajuanSurat::statusBadgeColor($pengajuan->status)"
                                        data-test="dashboard-warga-hero-status-{{ $pengajuan->id }}"
                                    >
                                        {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                                    </flux:badge>
                                    <p
                                        class="text-xs {{ $item['elapsed_amber'] ? 'font-medium text-amber-700 dark:text-amber-300' : 'text-zinc-500 dark:text-zinc-400' }}"
                                        data-test="dashboard-warga-hero-elapsed-{{ $pengajuan->id }}"
                                    >
                                        {{ __('Sudah :hari hari di status ini', ['hari' => $item['hari']]) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-1.5 sm:flex-row sm:items-center">
                                @if ($item['boleh_unduh'])
                                    <flux:button
                                        size="sm"
                                        variant="primary"
                                        icon="arrow-down-tray"
                                        :href="route('pengajuan-surat.unduh-surat', $pengajuan)"
                                        data-test="dashboard-warga-unduh-{{ $pengajuan->id }}"
                                    >
                                        <span class="sm:hidden">{{ __('Unduh') }}</span>
                                        <span class="hidden sm:inline">{{ __('Unduh Surat') }}</span>
                                    </flux:button>
                                @endif
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :href="route('pengajuan-surat.show', $pengajuan)"
                                    wire:navigate
                                    data-test="dashboard-warga-hero-detail-{{ $pengajuan->id }}"
                                >
                                    {{ __('Detail') }}
                                </flux:button>
                            </div>
                        </div>

                        @if ($langkahAktif !== null)
                            <ol
                                class="flex items-center gap-1.5"
                                data-test="dashboard-warga-hero-alur-{{ $pengajuan->id }}"
                                aria-label="{{ __('Alur: :status', ['status' => \App\Models\PengajuanSurat::statusLabel($pengajuan->status)]) }}"
                            >
                                @foreach ($langkahAlur as $index => $langkah)
                                    @php
                                        $selesai = $index < $langkahAktif;
                                        $aktif = $index === $langkahAktif;
                                        $dotClass = $aktif
                                            ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                            : ($selesai
                                                ? 'bg-brand-leaf text-white'
                                                : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400');
                                    @endphp
                                    <li class="flex items-center gap-1.5">
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-semibold {{ $dotClass }}"
                                            title="{{ __($langkah['label']) }}"
                                        >
                                            @if ($selesai)
                                                <flux:icon.check class="size-3" />
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </span>
                                        <span class="hidden text-xs text-zinc-600 dark:text-zinc-300 sm:inline {{ $aktif ? 'font-semibold text-zinc-900 dark:text-zinc-50' : '' }}">
                                            {{ __($langkah['label']) }}
                                        </span>
                                        @if (! $loop->last)
                                            <span class="h-px w-4 bg-zinc-300 dark:bg-zinc-600 sm:w-6" aria-hidden="true"></span>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        <p class="text-sm leading-snug text-zinc-700 dark:text-zinc-200" data-test="dashboard-warga-hero-penjelasan-{{ $pengajuan->id }}">
                            {{ $item['penjelasan'] }}
                        </p>

                        @if ($isSiap && $pengajuan->suratTerbit?->tanggal_pengambilan)
                            <div
                                class="rounded-lg border border-green-300/70 bg-white/80 px-3 py-2.5 dark:border-green-700/60 dark:bg-zinc-950/50"
                                data-test="dashboard-warga-hero-jadwal-{{ $pengajuan->id }}"
                            >
                                <p class="text-[11px] font-medium uppercase tracking-wide text-green-700 dark:text-green-300">
                                    {{ __('Jadwal pengambilan') }}
                                </p>
                                <p class="mt-0.5 text-base font-bold text-green-900 dark:text-green-100 sm:text-lg">
                                    {{ $pengajuan->suratTerbit->tanggal_pengambilan->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                                </p>
                                @if ($pengajuan->suratTerbit->jam_kerja_label)
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                        {{ $pengajuan->suratTerbit->jam_kerja_label }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- data-test quick-action tetap ada untuk kompatibilitas e2e lama --}}
        <div class="sr-only" data-test="dashboard-warga-quick-action" aria-hidden="true"></div>
    @endif

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="space-y-2" data-test="dashboard-warga-riwayat-section">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="lg">{{ __('Riwayat Terbaru') }}</flux:heading>
                <flux:button
                    size="sm"
                    variant="ghost"
                    :href="route('pengajuan-surat.riwayat')"
                    wire:navigate
                    data-test="dashboard-warga-riwayat-semua"
                >
                    {{ __('Lihat Semua Riwayat') }}
                </flux:button>
            </div>

            @if ($riwayatTerbaru->isEmpty())
                <flux:text class="text-sm text-zinc-500" data-test="dashboard-warga-riwayat-empty">
                    {{ __('Belum ada riwayat pengajuan.') }}
                </flux:text>
            @else
                <ul class="divide-y divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700" data-test="dashboard-warga-riwayat-table">
                    @foreach ($riwayatTerbaru as $item)
                        <li wire:key="dashboard-riwayat-{{ $item->id }}" data-test="dashboard-warga-riwayat-row-{{ $item->id }}">
                            <a
                                href="{{ route('pengajuan-surat.show', $item) }}"
                                wire:navigate
                                class="flex flex-col gap-1 px-3 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-50">
                                        {{ $item->jenisSurat?->nama_surat ?? '—' }}
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $item->nomor_pengajuan }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                    <flux:badge size="sm" :color="\App\Models\PengajuanSurat::statusBadgeColor($item->status)">
                                        {{ \App\Models\PengajuanSurat::statusLabel($item->status) }}
                                    </flux:badge>
                                    <span class="text-xs text-zinc-500">
                                        {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="space-y-2" data-test="dashboard-warga-notif-section">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="lg">{{ __('Notifikasi Terbaru') }}</flux:heading>
                <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 underline hover:no-underline dark:text-zinc-300"
                    data-test="dashboard-warga-notif-semua"
                    x-on:click="$dispatch('buka-panel-notifikasi')"
                >
                    {{ __('Lihat Semua Notifikasi') }}
                </button>
            </div>

            @if ($notifikasiTerbaru->isEmpty())
                <flux:text class="text-sm text-zinc-500" data-test="dashboard-warga-notif-empty">
                    {{ __('Belum ada notifikasi.') }}
                </flux:text>
            @else
                <ul class="divide-y divide-zinc-200 rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700" data-test="dashboard-warga-notif-list">
                    @foreach ($notifikasiTerbaru as $notif)
                        <li
                            class="flex items-start gap-3 px-3 py-2.5"
                            wire:key="dashboard-notif-{{ $notif->id }}"
                            data-test="dashboard-warga-notif-item-{{ $notif->id }}"
                        >
                            @if ($notif->status_baca === \App\Models\Notifikasi::STATUS_BELUM)
                                <span
                                    class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"
                                    data-test="dashboard-warga-notif-dot-{{ $notif->id }}"
                                    aria-hidden="true"
                                ></span>
                            @else
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-transparent" aria-hidden="true"></span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-sm {{ $notif->status_baca === \App\Models\Notifikasi::STATUS_BELUM ? 'font-semibold' : '' }}">
                                    {{ $notif->pesan }}
                                </p>
                                <p class="mt-0.5 text-xs text-zinc-500">
                                    {{ $notif->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6" data-test="dashboard-warga">
    <div>
        <flux:heading size="xl" data-test="dashboard-warga-heading">{{ __('Dashboard Warga') }}</flux:heading>
        <flux:text class="mt-1">
            {{ __('Pantau status surat Anda tanpa harus mencari ke menu lain.') }}
        </flux:text>
    </div>

    {{-- Banner notifikasi belum dibaca --}}
    @if ($unreadCount > 0)
        <div
            class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 dark:border-amber-600/50 dark:bg-amber-950/40"
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

    {{-- Hero: status pengajuan aktif --}}
    @if ($pengajuanAktif->isEmpty())
        <section
            class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-600 dark:bg-zinc-900/40"
            data-test="dashboard-warga-hero-empty"
        >
            <flux:heading size="lg">{{ __('Belum ada pengajuan aktif') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Ajukan surat keterangan sekarang untuk memulai proses di kantor desa.') }}
            </flux:text>
            <div class="mt-6">
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
        <section class="space-y-4" data-test="dashboard-warga-hero">
            @foreach ($pengajuanAktif as $item)
                @php
                    /** @var \App\Models\PengajuanSurat $pengajuan */
                    $pengajuan = $item['model'];
                    $heroClass = match ($pengajuan->status) {
                        \App\Models\PengajuanSurat::STATUS_DIAJUKAN => 'border-l-4 border-l-amber-500 border border-amber-200 bg-amber-50/80 dark:border-amber-700 dark:border-l-amber-400 dark:bg-amber-950/30',
                        \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                        \App\Models\PengajuanSurat::STATUS_DIPROSES => 'border-l-4 border-l-blue-500 border border-blue-200 bg-blue-50/80 dark:border-blue-700 dark:border-l-blue-400 dark:bg-blue-950/30',
                        \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'border-l-4 border-l-green-500 border border-green-200 bg-green-50/80 dark:border-green-700 dark:border-l-green-400 dark:bg-green-950/30',
                        default => 'border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900',
                    };
                @endphp
                <article
                    class="rounded-xl p-5 md:p-6 {{ $heroClass }}"
                    wire:key="dashboard-warga-hero-{{ $pengajuan->id }}"
                    data-test="dashboard-warga-hero-card-{{ $pengajuan->id }}"
                    data-status="{{ $pengajuan->status }}"
                >
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0 flex-1 space-y-3">
                            <div>
                                <flux:heading size="lg" data-test="dashboard-warga-hero-jenis-{{ $pengajuan->id }}">
                                    {{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}
                                </flux:heading>
                                <flux:text class="mt-1 text-sm" data-test="dashboard-warga-hero-nomor-{{ $pengajuan->id }}">
                                    {{ $pengajuan->nomor_pengajuan }}
                                </flux:text>
                            </div>

                            <div>
                                <flux:badge
                                    size="lg"
                                    :color="\App\Models\PengajuanSurat::statusBadgeColor($pengajuan->status)"
                                    data-test="dashboard-warga-hero-status-{{ $pengajuan->id }}"
                                >
                                    {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                                </flux:badge>
                                <p
                                    class="mt-2 text-sm {{ $item['elapsed_amber'] ? 'font-medium text-amber-700 dark:text-amber-300' : 'text-zinc-500 dark:text-zinc-400' }}"
                                    data-test="dashboard-warga-hero-elapsed-{{ $pengajuan->id }}"
                                >
                                    {{ __('Sudah :hari hari di status ini', ['hari' => $item['hari']]) }}
                                </p>
                            </div>

                            <p class="text-base text-zinc-800 dark:text-zinc-100" data-test="dashboard-warga-hero-penjelasan-{{ $pengajuan->id }}">
                                {{ $item['penjelasan'] }}
                            </p>

                            @if ($pengajuan->status === \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL && $pengajuan->suratTerbit?->tanggal_pengambilan)
                                <div
                                    class="rounded-lg bg-white/70 px-4 py-3 dark:bg-zinc-950/40"
                                    data-test="dashboard-warga-hero-jadwal-{{ $pengajuan->id }}"
                                >
                                    <p class="text-2xl font-bold tracking-tight text-green-800 dark:text-green-200">
                                        {{ $pengajuan->suratTerbit->tanggal_pengambilan->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                                    </p>
                                    @if ($pengajuan->suratTerbit->jam_kerja_label)
                                        <p class="mt-1 text-base font-semibold text-green-700 dark:text-green-300">
                                            {{ $pengajuan->suratTerbit->jam_kerja_label }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($item['boleh_unduh'])
                            <div class="shrink-0">
                                <flux:button
                                    variant="primary"
                                    icon="arrow-down-tray"
                                    :href="route('pengajuan-surat.unduh-surat', $pengajuan)"
                                    data-test="dashboard-warga-unduh-{{ $pengajuan->id }}"
                                >
                                    {{ __('Unduh Surat') }}
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    {{-- Quick action: tidak mendominasi --}}
    @if ($pengajuanAktif->isNotEmpty())
        <div class="flex justify-start" data-test="dashboard-warga-quick-action">
            <flux:button
                variant="filled"
                icon="document-plus"
                :href="route('pengajuan-surat.create')"
                wire:navigate
                data-test="dashboard-warga-ajukan-baru"
            >
                {{ __('Ajukan Surat Baru') }}
            </flux:button>
        </div>
    @endif

    {{-- Riwayat singkat --}}
    <section class="space-y-3" data-test="dashboard-warga-riwayat-section">
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
            <flux:table data-test="dashboard-warga-riwayat-table">
                <flux:table.columns>
                    <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                    <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($riwayatTerbaru as $item)
                        <flux:table.row wire:key="dashboard-riwayat-{{ $item->id }}" data-test="dashboard-warga-riwayat-row-{{ $item->id }}">
                            <flux:table.cell>{{ $item->jenisSurat?->nama_surat ?? '—' }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ $item->nomor_pengajuan }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="\App\Models\PengajuanSurat::statusBadgeColor($item->status)">
                                    {{ \App\Models\PengajuanSurat::statusLabel($item->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </section>

    {{-- Notifikasi singkat --}}
    <section class="space-y-3" data-test="dashboard-warga-notif-section">
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
                        class="flex items-start gap-3 px-4 py-3"
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

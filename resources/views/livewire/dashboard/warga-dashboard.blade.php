<div class="flex h-full w-full flex-1 flex-col gap-5 p-4 md:gap-6 md:p-6" data-test="dashboard-warga">
    {{-- Judul halaman disengaja kecil: jawaban status, bukan sapaan dashboard --}}
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Halo, :name', ['name' => $namaWarga]) }}
            </p>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500" data-test="dashboard-warga-heading">
                {{ __('Dashboard Warga') }}
            </p>
            <h1 class="mt-0.5 text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50 md:text-2xl">
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
                variant="ghost"
                icon="document-plus"
                :href="route('pengajuan-surat.create')"
                wire:navigate
                class="hidden sm:inline-flex"
                data-test="dashboard-warga-ajukan-baru-header"
            >
                {{ __('Ajukan Surat Baru') }}
            </flux:button>
        @endif
    </div>

    {{-- Banner notifikasi belum dibaca — sinyal update tanpa mencari lonceng --}}
    @if ($unreadCount > 0)
        <div
            class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-300/80 bg-amber-50 px-4 py-3 dark:border-amber-600/50 dark:bg-amber-950/40"
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

    {{-- Hero: jawaban utama "sudah sampai mana surat saya?" --}}
    @if ($pengajuanAktif->isEmpty())
        <section
            class="flex min-h-[52vh] flex-col items-center justify-center rounded-2xl border border-dashed border-brand-leaf/30 bg-brand-mist/60 px-6 py-12 text-center dark:border-brand-leaf/40 dark:bg-zinc-900/50"
            data-test="dashboard-warga-hero-empty"
        >
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-forest/10 text-brand-forest dark:bg-brand-mist/10 dark:text-brand-mist">
                <flux:icon.document-plus class="size-7" />
            </div>
            <flux:heading size="lg" class="mt-5">{{ __('Belum ada pengajuan aktif') }}</flux:heading>
            <flux:text class="mx-auto mt-2 max-w-md">
                {{ __('Ajukan surat keterangan sekarang untuk memulai proses di kantor desa.') }}
            </flux:text>
            <div class="mt-8">
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
                    $langkahAktif = $item['langkah_aktif'];
                    $heroClass = match ($pengajuan->status) {
                        \App\Models\PengajuanSurat::STATUS_DIAJUKAN => 'border-l-[6px] border-l-amber-500 border border-amber-200/90 bg-amber-50 dark:border-amber-700 dark:border-l-amber-400 dark:bg-amber-950/35',
                        \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                        \App\Models\PengajuanSurat::STATUS_DIPROSES => 'border-l-[6px] border-l-blue-500 border border-blue-200/90 bg-blue-50 dark:border-blue-700 dark:border-l-blue-400 dark:bg-blue-950/35',
                        \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'border-l-[6px] border-l-green-500 border border-green-200/90 bg-green-50 dark:border-green-700 dark:border-l-green-400 dark:bg-green-950/35',
                        default => 'border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900',
                    };
                @endphp
                <article
                    class="rounded-2xl p-5 shadow-sm md:min-h-[42vh] md:p-8 {{ $heroClass }}"
                    wire:key="dashboard-warga-hero-{{ $pengajuan->id }}"
                    data-test="dashboard-warga-hero-card-{{ $pengajuan->id }}"
                    data-status="{{ $pengajuan->status }}"
                >
                    <div class="flex h-full flex-col gap-6">
                        {{-- Identitas surat + aksi unduh --}}
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    {{ __('Jenis surat') }}
                                </p>
                                <flux:heading size="xl" class="mt-1" data-test="dashboard-warga-hero-jenis-{{ $pengajuan->id }}">
                                    {{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}
                                </flux:heading>
                                <flux:text class="mt-1 text-sm" data-test="dashboard-warga-hero-nomor-{{ $pengajuan->id }}">
                                    {{ $pengajuan->nomor_pengajuan }}
                                </flux:text>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                @if ($item['boleh_unduh'])
                                    <flux:button
                                        variant="primary"
                                        icon="arrow-down-tray"
                                        :href="route('pengajuan-surat.unduh-surat', $pengajuan)"
                                        data-test="dashboard-warga-unduh-{{ $pengajuan->id }}"
                                    >
                                        {{ __('Unduh Surat') }}
                                    </flux:button>
                                @endif
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :href="route('pengajuan-surat.show', $pengajuan)"
                                    wire:navigate
                                    data-test="dashboard-warga-hero-detail-{{ $pengajuan->id }}"
                                >
                                    {{ __('Lihat detail') }}
                                </flux:button>
                            </div>
                        </div>

                        {{-- Status besar + elapsed --}}
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    {{ __('Status saat ini') }}
                                </p>
                                <div class="mt-2 flex flex-wrap items-center gap-3">
                                    <flux:badge
                                        size="lg"
                                        :color="\App\Models\PengajuanSurat::statusBadgeColor($pengajuan->status)"
                                        data-test="dashboard-warga-hero-status-{{ $pengajuan->id }}"
                                    >
                                        {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                                    </flux:badge>
                                    <p
                                        class="text-sm {{ $item['elapsed_amber'] ? 'font-medium text-amber-700 dark:text-amber-300' : 'text-zinc-500 dark:text-zinc-400' }}"
                                        data-test="dashboard-warga-hero-elapsed-{{ $pengajuan->id }}"
                                    >
                                        {{ __('Sudah :hari hari di status ini', ['hari' => $item['hari']]) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Alur visual: di mana surat saya sekarang --}}
                        @if ($langkahAktif !== null)
                            <ol
                                class="grid grid-cols-3 gap-2"
                                data-test="dashboard-warga-hero-alur-{{ $pengajuan->id }}"
                                aria-label="{{ __('Alur pengajuan surat') }}"
                            >
                                @foreach ($langkahAlur as $index => $langkah)
                                    @php
                                        $selesai = $index < $langkahAktif;
                                        $aktif = $index === $langkahAktif;
                                        $dotClass = $aktif
                                            ? 'bg-zinc-900 text-white ring-2 ring-offset-2 ring-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 dark:ring-zinc-100 dark:ring-offset-transparent'
                                            : ($selesai
                                                ? 'bg-brand-leaf text-white'
                                                : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400');
                                        $labelClass = $aktif
                                            ? 'font-semibold text-zinc-900 dark:text-zinc-50'
                                            : ($selesai
                                                ? 'font-medium text-brand-leaf dark:text-green-300'
                                                : 'text-zinc-400 dark:text-zinc-500');
                                    @endphp
                                    <li class="flex flex-col items-center gap-2 text-center">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold {{ $dotClass }}">
                                            @if ($selesai)
                                                <flux:icon.check class="size-4" />
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </span>
                                        <span class="text-xs sm:text-sm {{ $labelClass }}">{{ __($langkah['label']) }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        <p class="text-base leading-relaxed text-zinc-800 dark:text-zinc-100 md:text-lg" data-test="dashboard-warga-hero-penjelasan-{{ $pengajuan->id }}">
                            {{ $item['penjelasan'] }}
                        </p>

                        {{-- Jadwal pengambilan: informasi paling penting saat siap_diambil --}}
                        @if ($pengajuan->status === \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL && $pengajuan->suratTerbit?->tanggal_pengambilan)
                            <div
                                class="rounded-xl border border-green-300/70 bg-white/80 px-5 py-5 dark:border-green-700/60 dark:bg-zinc-950/50"
                                data-test="dashboard-warga-hero-jadwal-{{ $pengajuan->id }}"
                            >
                                <p class="text-xs font-medium uppercase tracking-wide text-green-700 dark:text-green-300">
                                    {{ __('Jadwal pengambilan') }}
                                </p>
                                <p class="mt-2 text-2xl font-bold tracking-tight text-green-900 dark:text-green-100 md:text-3xl">
                                    {{ $pengajuan->suratTerbit->tanggal_pengambilan->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                                </p>
                                @if ($pengajuan->suratTerbit->jam_kerja_label)
                                    <p class="mt-2 text-base font-semibold text-green-800 dark:text-green-200 md:text-lg">
                                        {{ $pengajuan->suratTerbit->jam_kerja_label }}
                                    </p>
                                @endif
                                <p class="mt-3 text-sm text-green-800/80 dark:text-green-200/80">
                                    {{ __('Bawa KTP asli saat mengambil surat di kantor desa.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    {{-- Quick action: mudah ditemukan, hierarki visual di bawah hero --}}
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

    {{-- Sekunder: riwayat + notifikasi — jawaban sekunder setelah status --}}
    <div class="grid gap-6 lg:grid-cols-2">
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
                {{-- Daftar ramah warga (bukan tabel admin) — kolom tetap: jenis, nomor, status, tanggal --}}
                <ul class="divide-y divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700" data-test="dashboard-warga-riwayat-table">
                    @foreach ($riwayatTerbaru as $item)
                        <li wire:key="dashboard-riwayat-{{ $item->id }}" data-test="dashboard-warga-riwayat-row-{{ $item->id }}">
                            <a
                                href="{{ route('pengajuan-surat.show', $item) }}"
                                wire:navigate
                                class="flex flex-col gap-2 px-4 py-3 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-zinc-900 dark:text-zinc-50">
                                        {{ $item->jenisSurat?->nama_surat ?? '—' }}
                                    </p>
                                    <p class="mt-0.5 text-sm text-zinc-500">{{ $item->nomor_pengajuan }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                    <flux:badge size="sm" :color="\App\Models\PengajuanSurat::statusBadgeColor($item->status)">
                                        {{ \App\Models\PengajuanSurat::statusLabel($item->status) }}
                                    </flux:badge>
                                    <span class="text-sm text-zinc-500">
                                        {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

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
</div>

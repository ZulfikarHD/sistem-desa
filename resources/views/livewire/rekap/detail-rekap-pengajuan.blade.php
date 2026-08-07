<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="rekap-detail-heading">
                {{ __('Detail Rekap Pengajuan') }}
            </flux:heading>
            <flux:text class="mt-1" data-test="rekap-detail-nomor">
                {{ $pengajuan->nomor_pengajuan }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($this->dapatUnduhPdf())
                <flux:button
                    variant="primary"
                    icon="arrow-down-tray"
                    :href="route('surat-diproses.pdf.download', $pengajuan)"
                    data-test="rekap-detail-unduh-pdf"
                >
                    {{ __('Unduh PDF Surat') }}
                </flux:button>
            @endif

            <flux:button
                variant="ghost"
                icon="arrow-left"
                :href="route('rekap-pengajuan.index')"
                wire:navigate
                data-test="rekap-detail-kembali"
            >
                {{ __('Kembali ke Rekap') }}
            </flux:button>
        </div>
    </div>

    {{-- Ringkasan Pengajuan --}}
    <div
        class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
        data-test="rekap-detail-ringkasan"
    >
        <flux:heading size="sm" class="mb-4">{{ __('Ringkasan Pengajuan') }}</flux:heading>

        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="grid gap-1">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nama Warga') }}</dt>
                <dd data-test="rekap-detail-nama">{{ $pengajuan->user?->name ?? '—' }}</dd>
            </div>
            <div class="grid gap-1">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('NIK') }}</dt>
                <dd data-test="rekap-detail-nik">{{ $pengajuan->user?->nik ?? '—' }}</dd>
            </div>
            <div class="grid gap-1">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jenis Surat') }}</dt>
                <dd data-test="rekap-detail-jenis">{{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}</dd>
            </div>
            <div class="grid gap-1">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nomor Pengajuan') }}</dt>
                <dd data-test="rekap-detail-nomor-pengajuan">{{ $pengajuan->nomor_pengajuan }}</dd>
            </div>
            <div class="grid gap-1">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nomor Surat Resmi') }}</dt>
                <dd data-test="rekap-detail-nomor-surat">
                    {{ $pengajuan->suratTerbit?->nomor_surat ?? '—' }}
                </dd>
            </div>
            <div class="grid gap-1">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status Terakhir') }}</dt>
                <dd>
                    <flux:badge
                        :color="\App\Models\PengajuanSurat::statusBadgeColor($pengajuan->status)"
                        data-test="rekap-detail-status"
                    >
                        {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                    </flux:badge>
                </dd>
            </div>
        </dl>
    </div>

    {{-- Timeline Proses (vertikal seperti tracking kurir) --}}
    <div
        class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
        data-test="rekap-detail-timeline"
    >
        <flux:heading size="sm" class="mb-6">{{ __('Timeline Proses') }}</flux:heading>

        @if (count($timelineItems) === 0)
            <flux:text data-test="rekap-detail-timeline-empty">
                {{ __('Belum ada riwayat proses.') }}
            </flux:text>
        @else
            <ol class="relative space-y-0">
                @foreach ($timelineItems as $index => $item)
                    <li
                        class="relative flex gap-4 pb-8 last:pb-0"
                        wire:key="rekap-timeline-{{ $item['key'] }}"
                        data-test="rekap-timeline-item-{{ $item['key'] }}"
                    >
                        {{-- Garis vertikal penghubung --}}
                        @if (! $loop->last)
                            <span
                                class="absolute top-8 bottom-0 left-[15px] w-0.5 bg-zinc-200 dark:bg-zinc-600"
                                aria-hidden="true"
                            ></span>
                        @endif

                        {{-- Ikon lingkaran berwarna --}}
                        <div
                            @class([
                                'relative z-10 flex size-8 shrink-0 items-center justify-center rounded-full ring-4 ring-white dark:ring-zinc-900',
                                'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-200' => $item['color'] === 'zinc',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $item['color'] === 'blue',
                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $item['color'] === 'red',
                                'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $item['color'] === 'green',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $item['color'] === 'amber',
                            ])
                            data-test="rekap-timeline-icon-{{ $item['key'] }}"
                        >
                            <flux:icon :name="$item['icon']" variant="micro" />
                        </div>

                        <div class="min-w-0 flex-1 pt-0.5">
                            <p
                                class="text-sm font-medium text-zinc-900 dark:text-zinc-100"
                                data-test="rekap-timeline-label-{{ $item['key'] }}"
                            >
                                {{ $item['label'] }}
                            </p>
                            <p
                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                data-test="rekap-timeline-waktu-{{ $item['key'] }}"
                            >
                                {{ $this->formatWaktuWib($item['waktu']) }}
                            </p>
                            <p
                                class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400"
                                data-test="rekap-timeline-aktor-{{ $item['key'] }}"
                            >
                                {{ __('Oleh') }}: {{ $item['aktor'] }}
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="detail-pengajuan-warga-heading">
                {{ __('Detail Pengajuan') }}
            </flux:heading>
            <flux:text class="mt-1" data-test="detail-pengajuan-warga-nomor">
                {{ $pengajuan->nomor_pengajuan }}
            </flux:text>
        </div>

        <flux:button
            variant="ghost"
            icon="arrow-left"
            :href="route('pengajuan-surat.riwayat')"
            wire:navigate
            data-test="detail-pengajuan-warga-back"
        >
            {{ __('Kembali ke Riwayat') }}
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Informasi Pengajuan') }}</flux:heading>

            <dl class="grid gap-3 text-sm">
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jenis Surat') }}</dt>
                    <dd data-test="detail-pengajuan-warga-jenis-surat">
                        {{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Pengajuan') }}</dt>
                    <dd data-test="detail-pengajuan-warga-tanggal">
                        {{ $pengajuan->tanggal_pengajuan?->translatedFormat('d M Y') }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                    <dd>
                            @php
                                $statusVariant = match ($pengajuan->status) {
                                    \App\Models\PengajuanSurat::STATUS_DITOLAK => 'danger',
                                    \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                                    \App\Models\PengajuanSurat::STATUS_SELESAI => 'success',
                                    \App\Models\PengajuanSurat::STATUS_DIPROSES,
                                    \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'warning',
                                    default => 'neutral',
                                };
                            @endphp
                        <flux:badge :variant="$statusVariant" data-test="detail-pengajuan-warga-status">
                            {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                        </flux:badge>
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Keperluan') }}</dt>
                    <dd class="whitespace-pre-wrap" data-test="detail-pengajuan-warga-keperluan">
                        {{ $pengajuan->keperluan }}
                    </dd>
                </div>
                @if ($pengajuan->status === \App\Models\PengajuanSurat::STATUS_DITOLAK && $pengajuan->catatan_admin)
                    <div class="grid gap-1">
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Catatan Admin') }}</dt>
                        <dd class="whitespace-pre-wrap text-red-600 dark:text-red-400" data-test="detail-pengajuan-warga-catatan">
                            {{ $pengajuan->catatan_admin }}
                        </dd>
                    </div>
                @endif

                @if ($pengajuan->suratTerbit?->tanggal_pengambilan)
                    <div class="grid gap-1">
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Pengambilan') }}</dt>
                        <dd data-test="detail-pengajuan-warga-tanggal-pengambilan">
                            {{ $pengajuan->suratTerbit->tanggal_pengambilan->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}
                        </dd>
                    </div>
                    @if ($pengajuan->suratTerbit->jam_kerja_label)
                        <div class="grid gap-1">
                            <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jam Kerja') }}</dt>
                            <dd data-test="detail-pengajuan-warga-jam-kerja">
                                {{ $pengajuan->suratTerbit->jam_kerja_label }}
                            </dd>
                        </div>
                    @endif
                @endif
            </dl>

            <div class="flex flex-wrap items-center gap-2">
                @if ($pengajuan->dapatUnduhSurat())
                    <flux:button
                        variant="primary"
                        icon="arrow-down-tray"
                        :href="route('pengajuan-surat.unduh-surat', $pengajuan)"
                        data-test="detail-pengajuan-warga-unduh-surat"
                    >
                        {{ __('Unduh Surat') }}
                    </flux:button>
                    <flux:button
                        variant="ghost"
                        icon="printer"
                        :href="route('pengajuan-surat.cetak-surat', $pengajuan)"
                        target="_blank"
                        data-test="detail-pengajuan-warga-cetak-surat"
                    >
                        {{ __('Cetak Surat') }}
                    </flux:button>
                @endif

                @if ($pengajuan->status === \App\Models\PengajuanSurat::STATUS_DITOLAK)
                    <flux:button
                        variant="primary"
                        icon="arrow-path"
                        :href="route('pengajuan-surat.resubmit', $pengajuan)"
                        wire:navigate
                        data-test="detail-pengajuan-warga-ajukan-ulang"
                    >
                        {{ __('Ajukan Ulang') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Dokumen yang Diunggah') }}</flux:heading>

            @if ($pengajuan->dokumenPersyaratan->isEmpty())
                <flux:text data-test="detail-pengajuan-warga-dokumen-empty">
                    {{ __('Belum ada dokumen yang diunggah.') }}
                </flux:text>
            @else
                <ul class="flex flex-col gap-2 text-sm" data-test="detail-pengajuan-warga-dokumen-list">
                    @foreach ($pengajuan->dokumenPersyaratan as $dokumen)
                        <li wire:key="detail-dokumen-{{ $dokumen->id }}">
                            <flux:badge variant="neutral">{{ $dokumen->jenis_dokumen }}</flux:badge>
                            <span class="ms-2 text-zinc-600 dark:text-zinc-300">
                                {{ basename($dokumen->file_path) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($pengajuan->jenisSurat?->deskripsi)
                <div class="mt-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Deskripsi Jenis Surat') }}</flux:heading>
                    <flux:text class="mt-2 whitespace-pre-wrap" data-test="detail-pengajuan-warga-deskripsi">
                        {{ $pengajuan->jenisSurat->deskripsi }}
                    </flux:text>
                </div>
            @endif
        </div>
    </div>
</div>

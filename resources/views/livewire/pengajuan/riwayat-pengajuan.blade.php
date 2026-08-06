<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="riwayat-pengajuan-heading">
                {{ __('Status & Riwayat Pengajuan') }}
            </flux:heading>
            <flux:text class="mt-1">
                {{ __('Pantau status pengajuan surat Anda dan ajukan ulang jika ditolak.') }}
            </flux:text>
        </div>

        <flux:button
            variant="primary"
            icon="document-plus"
            :href="route('pengajuan-surat.create')"
            wire:navigate
            data-test="riwayat-pengajuan-buat-baru"
        >
            {{ __('Pengajuan Baru') }}
        </flux:button>
    </div>

    <div class="max-w-xs">
        <flux:field>
            <flux:label>{{ __('Filter Status') }}</flux:label>
            <flux:select
                wire:model.live="statusFilter"
                data-test="riwayat-pengajuan-status-filter"
            >
                @foreach ($statusOptions as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>
    </div>

    @if ($pengajuanList->isEmpty())
        <div
            class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600"
            data-test="riwayat-pengajuan-empty"
        >
            <flux:heading size="sm">{{ __('Belum ada riwayat pengajuan') }}</flux:heading>
            <flux:text class="mt-2">
                @if ($statusFilter !== '')
                    {{ __('Tidak ada pengajuan dengan status filter ini.') }}
                @else
                    {{ __('Mulai dengan mengajukan surat keterangan pertama Anda.') }}
                @endif
            </flux:text>
        </div>
    @else
        <flux:table :paginate="$pengajuanList" data-test="riwayat-pengajuan-table">
            <flux:table.columns>
                <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Pengambilan') }}</flux:table.column>
                <flux:table.column>{{ __('Catatan Admin') }}</flux:table.column>
                <flux:table.column>{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($pengajuanList as $item)
                    <flux:table.row
                        wire:key="riwayat-pengajuan-{{ $item->id }}"
                        data-test="riwayat-pengajuan-row-{{ $item->id }}"
                    >
                        <flux:table.cell variant="strong" data-test="riwayat-pengajuan-nomor-{{ $item->id }}">
                            {{ $item->nomor_pengajuan }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $item->jenisSurat?->nama_surat ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $statusVariant = match ($item->status) {
                                    \App\Models\PengajuanSurat::STATUS_DITOLAK => 'danger',
                                    \App\Models\PengajuanSurat::STATUS_SELESAI => 'success',
                                    \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                                    \App\Models\PengajuanSurat::STATUS_DIPROSES,
                                    \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'warning',
                                    default => 'neutral',
                                };
                            @endphp
                            <flux:badge
                                :variant="$statusVariant"
                                data-test="riwayat-pengajuan-status-{{ $item->id }}"
                            >
                                {{ \App\Models\PengajuanSurat::statusLabel($item->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell data-test="riwayat-pengajuan-pengambilan-{{ $item->id }}">
                            @if ($item->suratTerbit?->tanggal_pengambilan)
                                <div class="text-sm">
                                    <div data-test="riwayat-pengajuan-tanggal-ambil-{{ $item->id }}">
                                        {{ $item->suratTerbit->tanggal_pengambilan->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}
                                    </div>
                                    @if ($item->suratTerbit->jam_kerja_label)
                                        <div
                                            class="text-zinc-500 dark:text-zinc-400"
                                            data-test="riwayat-pengajuan-jam-kerja-{{ $item->id }}"
                                        >
                                            {{ $item->suratTerbit->jam_kerja_label }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($item->status === \App\Models\PengajuanSurat::STATUS_DITOLAK && $item->catatan_admin)
                                <span
                                    class="line-clamp-2 text-sm"
                                    data-test="riwayat-pengajuan-catatan-{{ $item->id }}"
                                >
                                    {{ $item->catatan_admin }}
                                </span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap items-center gap-1">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="eye"
                                    :href="route('pengajuan-surat.show', $item)"
                                    wire:navigate
                                    data-test="riwayat-pengajuan-detail-{{ $item->id }}"
                                >
                                    {{ __('Detail') }}
                                </flux:button>

                                @if ($item->dapatUnduhSurat())
                                    <flux:button
                                        variant="primary"
                                        size="sm"
                                        icon="arrow-down-tray"
                                        :href="route('pengajuan-surat.unduh-surat', $item)"
                                        data-test="riwayat-pengajuan-unduh-surat-{{ $item->id }}"
                                    >
                                        {{ __('Unduh Surat') }}
                                    </flux:button>
                                @endif

                                @if ($item->status === \App\Models\PengajuanSurat::STATUS_DITOLAK)
                                    <flux:button
                                        variant="primary"
                                        size="sm"
                                        icon="arrow-path"
                                        :href="route('pengajuan-surat.resubmit', $item)"
                                        wire:navigate
                                        data-test="riwayat-pengajuan-ajukan-ulang-{{ $item->id }}"
                                    >
                                        {{ __('Ajukan Ulang') }}
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

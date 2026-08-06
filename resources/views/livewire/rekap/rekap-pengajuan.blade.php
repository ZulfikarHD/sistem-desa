<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="rekap-pengajuan-heading">
                {{ __('Rekap Pengajuan') }}
            </flux:heading>
            <flux:text class="mt-1">
                {{ __('Rekapitulasi seluruh pengajuan surat untuk pelaporan desa. Filter dan ekspor sesuai kebutuhan.') }}
            </flux:text>
        </div>

        <flux:button
            variant="primary"
            icon="arrow-down-tray"
            wire:click="exportCsv"
            data-test="rekap-pengajuan-export-csv"
        >
            {{ __('Export CSV') }}
        </flux:button>
    </div>

    {{-- Ringkasan jumlah per status (mengikuti filter jenis + tanggal, mengabaikan status) --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7" data-test="rekap-pengajuan-ringkasan">
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-total">
            <flux:text class="text-sm">{{ __('Total') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['total']) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-diajukan">
            <flux:text class="text-sm">{{ __('Diajukan') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['diajukan']) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-disetujui">
            <flux:text class="text-sm">{{ __('Disetujui') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['disetujui']) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-diproses">
            <flux:text class="text-sm">{{ __('Diproses') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['diproses']) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-siap-diambil">
            <flux:text class="text-sm">{{ __('Siap Diambil') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['siap_diambil']) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-selesai">
            <flux:text class="text-sm">{{ __('Selesai') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['selesai']) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="rekap-ringkasan-ditolak">
            <flux:text class="text-sm">{{ __('Ditolak') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format($ringkasan['ditolak']) }}</flux:heading>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-test="rekap-pengajuan-filters">
        <flux:field>
            <flux:label>{{ __('Jenis Surat') }}</flux:label>
            <flux:select
                wire:model.live="jenisSuratFilter"
                data-test="rekap-pengajuan-jenis-filter"
            >
                <flux:select.option value="">{{ __('Semua jenis surat') }}</flux:select.option>
                @foreach ($jenisSuratOptions as $jenis)
                    <flux:select.option value="{{ $jenis->id }}">{{ $jenis->nama_surat }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:select
                wire:model.live="statusFilter"
                data-test="rekap-pengajuan-status-filter"
            >
                @foreach ($statusOptions as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Tanggal Dari') }}</flux:label>
            <flux:input
                type="date"
                wire:model.live="tanggalDari"
                max="2999-12-31"
                data-test="rekap-pengajuan-tanggal-dari"
            />
            <flux:error name="tanggalDari" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Tanggal Sampai') }}</flux:label>
            <flux:input
                type="date"
                wire:model.live="tanggalSampai"
                max="2999-12-31"
                data-test="rekap-pengajuan-tanggal-sampai"
            />
            <flux:error name="tanggalSampai" />
        </flux:field>
    </div>

    <div>
        <flux:button
            variant="ghost"
            size="sm"
            wire:click="resetFilters"
            data-test="rekap-pengajuan-reset-filters"
        >
            {{ __('Reset Filter') }}
        </flux:button>
    </div>

    @if ($pengajuanList->isEmpty())
        <div
            class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600"
            data-test="rekap-pengajuan-empty"
        >
            <flux:heading size="sm">{{ __('Tidak ada data pengajuan') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Tidak ada pengajuan yang cocok dengan filter saat ini.') }}
            </flux:text>
        </div>
    @else
        <flux:table :paginate="$pengajuanList" data-test="rekap-pengajuan-table">
            <flux:table.columns>
                <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                <flux:table.column>{{ __('Nama Warga') }}</flux:table.column>
                <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                <flux:table.column>{{ __('Tanggal Pengajuan') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Admin Verifikator') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($pengajuanList as $item)
                    @php
                        $statusVariant = match ($item->status) {
                            \App\Models\PengajuanSurat::STATUS_DITOLAK => 'danger',
                            \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                            \App\Models\PengajuanSurat::STATUS_SELESAI => 'success',
                            \App\Models\PengajuanSurat::STATUS_DIPROSES,
                            \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'warning',
                            default => 'zinc',
                        };
                    @endphp
                    <flux:table.row
                        wire:key="rekap-pengajuan-{{ $item->id }}"
                        data-test="rekap-pengajuan-row-{{ $item->id }}"
                    >
                        <flux:table.cell variant="strong" data-test="rekap-pengajuan-nomor-{{ $item->id }}">
                            {{ $item->nomor_pengajuan }}
                        </flux:table.cell>
                        <flux:table.cell data-test="rekap-pengajuan-warga-{{ $item->id }}">
                            {{ $item->user?->name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="rekap-pengajuan-jenis-{{ $item->id }}">
                            {{ $item->jenisSurat?->nama_surat ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="rekap-pengajuan-tanggal-{{ $item->id }}">
                            {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :variant="$statusVariant"
                                data-test="rekap-pengajuan-status-{{ $item->id }}"
                            >
                                {{ __($this->statusLabel($item->status)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell data-test="rekap-pengajuan-admin-{{ $item->id }}">
                            {{ $item->diverifikasiOleh?->name ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

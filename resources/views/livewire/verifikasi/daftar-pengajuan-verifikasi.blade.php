<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" data-test="verifikasi-pengajuan-heading">
            {{ __('Verifikasi Pengajuan') }}
        </flux:heading>
        <flux:text class="mt-1">
            {{ __('Daftar pengajuan surat yang menunggu pemeriksaan dokumen oleh petugas desa.') }}
        </flux:text>
    </div>

    <div class="max-w-xs">
        <flux:field>
            <flux:label>{{ __('Filter Status') }}</flux:label>
            <flux:select
                wire:model.live="statusFilter"
                data-test="verifikasi-pengajuan-status-filter"
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
            data-test="verifikasi-pengajuan-empty"
        >
            <flux:heading size="sm">{{ __('Tidak ada pengajuan') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Tidak ada pengajuan dengan status filter ini.') }}
            </flux:text>
        </div>
    @else
        <flux:table :paginate="$pengajuanList" data-test="verifikasi-pengajuan-table">
            <flux:table.columns>
                <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                <flux:table.column>{{ __('Nama Warga') }}</flux:table.column>
                <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                <flux:table.column>{{ __('Tanggal Pengajuan') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($pengajuanList as $item)
                    <flux:table.row
                        wire:key="verifikasi-pengajuan-{{ $item->id }}"
                        wire:click="openDetail({{ $item->id }})"
                        class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        data-test="verifikasi-pengajuan-row-{{ $item->id }}"
                    >
                        <flux:table.cell variant="strong" data-test="verifikasi-pengajuan-nomor-{{ $item->id }}">
                            {{ $item->nomor_pengajuan }}
                        </flux:table.cell>
                        <flux:table.cell data-test="verifikasi-pengajuan-warga-{{ $item->id }}">
                            {{ $item->user?->name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="verifikasi-pengajuan-jenis-{{ $item->id }}">
                            {{ $item->jenisSurat?->nama_surat ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="verifikasi-pengajuan-tanggal-{{ $item->id }}">
                            {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

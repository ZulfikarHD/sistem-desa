<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" data-test="surat-diproses-heading">
            {{ __('Surat Diproses') }}
        </flux:heading>
        <flux:text class="mt-1">
            {{ __('Daftar pengajuan yang sudah disetujui dan sedang disiapkan suratnya.') }}
        </flux:text>
    </div>

    @if ($pengajuanList->isEmpty())
        <div
            class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600"
            data-test="surat-diproses-empty"
        >
            <flux:heading size="sm">{{ __('Tidak ada surat yang sedang diproses') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Tidak ada surat yang sedang diproses saat ini.') }}
            </flux:text>
        </div>
    @else
        <flux:table :paginate="$pengajuanList" data-test="surat-diproses-table">
            <flux:table.columns>
                <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                <flux:table.column>{{ __('Nama Warga') }}</flux:table.column>
                <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                <flux:table.column>{{ __('Tanggal Pengajuan') }}</flux:table.column>
                <flux:table.column>{{ __('Nomor Surat') }}</flux:table.column>
                <flux:table.column>{{ __('Tanggal Digenerate') }}</flux:table.column>
                <flux:table.column>{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($pengajuanList as $item)
                    <flux:table.row
                        wire:key="surat-diproses-{{ $item->id }}"
                        data-test="surat-diproses-row-{{ $item->id }}"
                    >
                        <flux:table.cell variant="strong" data-test="surat-diproses-nomor-{{ $item->id }}">
                            {{ $item->nomor_pengajuan }}
                        </flux:table.cell>
                        <flux:table.cell data-test="surat-diproses-warga-{{ $item->id }}">
                            {{ $item->user?->name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="surat-diproses-jenis-{{ $item->id }}">
                            {{ $item->jenisSurat?->nama_surat ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="surat-diproses-tanggal-{{ $item->id }}">
                            {{ $item->tanggal_pengajuan?->translatedFormat('d M Y') }}
                        </flux:table.cell>
                        <flux:table.cell data-test="surat-diproses-nomor-surat-{{ $item->id }}">
                            {{ $item->suratTerbit?->nomor_surat ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell data-test="surat-diproses-tanggal-terbit-{{ $item->id }}">
                            {{ $item->suratTerbit?->tanggal_terbit?->translatedFormat('d M Y') ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="eye"
                                wire:click="openDetail({{ $item->id }})"
                                data-test="surat-diproses-detail-{{ $item->id }}"
                            >
                                {{ __('Lihat Detail') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

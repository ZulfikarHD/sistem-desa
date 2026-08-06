<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="surat-diproses-detail-heading">
                {{ __('Detail Surat Diproses') }}
            </flux:heading>
            <flux:text class="mt-1" data-test="surat-diproses-detail-nomor">
                {{ $pengajuan->nomor_pengajuan }}
            </flux:text>
        </div>

        <flux:button
            variant="ghost"
            icon="arrow-left"
            :href="route('surat-diproses.index')"
            wire:navigate
            data-test="surat-diproses-detail-back"
        >
            {{ __('Kembali ke Surat Diproses') }}
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Data Pengajuan') }}</flux:heading>

            <dl class="grid gap-3 text-sm">
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nama Warga') }}</dt>
                    <dd data-test="surat-diproses-detail-nama-warga">{{ $pengajuan->user?->name ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('NIK') }}</dt>
                    <dd data-test="surat-diproses-detail-nik">{{ $pengajuan->user?->nik ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jenis Surat') }}</dt>
                    <dd data-test="surat-diproses-detail-jenis-surat">{{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nomor Surat') }}</dt>
                    <dd data-test="surat-diproses-detail-nomor-surat">
                        {{ $pengajuan->suratTerbit?->nomor_surat ?? '—' }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                    <dd>
                        <flux:badge :color="\App\Models\PengajuanSurat::statusBadgeColor($pengajuan->status)" data-test="surat-diproses-detail-status">
                            {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                        </flux:badge>
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Keperluan') }}</dt>
                    <dd class="whitespace-pre-wrap" data-test="surat-diproses-detail-keperluan">{{ $pengajuan->keperluan }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Pratinjau Surat') }}</flux:heading>

            @if ($pengajuan->suratTerbit && $this->suratPdfExists())
                <iframe
                    src="{{ route('surat-diproses.pdf.show', $pengajuan) }}"
                    title="{{ __('Pratinjau surat terbit') }}"
                    class="h-96 w-full rounded-lg border border-zinc-200 dark:border-zinc-700"
                    data-test="surat-diproses-detail-pdf-preview"
                ></iframe>

                <div>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="arrow-down-tray"
                        :href="route('surat-diproses.pdf.download', $pengajuan)"
                        data-test="surat-diproses-detail-pdf-download"
                    >
                        {{ __('Unduh PDF Surat') }}
                    </flux:button>
                </div>
            @else
                <flux:callout variant="warning" data-test="surat-diproses-detail-pdf-missing">
                    {{ __('PDF surat belum tersedia.') }}
                </flux:callout>
            @endif
        </div>
    </div>

    @if ($this->canMarkSiapDiambil())
        <div
            class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
            data-test="surat-diproses-detail-siap-diambil-panel"
        >
            <div>
                <flux:heading size="sm">{{ __('Tanggal Pengambilan & Siap Diambil') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Pilih tanggal pengambilan pada hari kerja. Tanggal lampau tidak dapat dipilih.') }}
                </flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Tanggal Pengambilan') }}</flux:label>
                    <flux:input
                        type="date"
                        wire:model.live="tanggalPengambilan"
                        :min="$this->tanggalMinHariIni()"
                        data-test="surat-diproses-detail-tanggal-pengambilan"
                    />
                    <flux:error name="tanggalPengambilan" />
                </flux:field>

                <div class="grid gap-1 text-sm">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jam Kerja') }}</dt>
                    <dd data-test="surat-diproses-detail-jam-kerja-preview">
                        @if ($this->jamKerjaPreview())
                            {{ $this->jamKerjaPreview() }}
                        @elseif ($tanggalPengambilan !== '')
                            <span class="text-red-600 dark:text-red-400">
                                {{ __('Kantor tutup pada tanggal ini (akhir pekan atau libur nasional).') }}
                            </span>
                        @else
                            <span class="text-zinc-400">{{ __('Pilih tanggal untuk melihat jam kerja') }}</span>
                        @endif
                    </dd>
                </div>
            </div>

            <flux:callout variant="secondary" data-test="surat-diproses-detail-jam-kerja-info">
                {{ __('Senin–Kamis: 08.00–16.00 WIB · Jumat: 08.00–16.30 WIB · Sabtu–Minggu & libur nasional: tutup') }}
            </flux:callout>

            <div>
                <flux:button
                    variant="primary"
                    icon="check-badge"
                    wire:click="tandaiSiapDiambil"
                    :disabled="! $this->isTanggalPengambilanSiap()"
                    data-test="surat-diproses-detail-siap-diambil-button"
                >
                    {{ __('Siap Diambil') }}
                </flux:button>
            </div>
        </div>
    @endif

    @if ($this->sudahLewatDiproses() && $pengajuan->suratTerbit)
        <div
            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
            data-test="surat-diproses-detail-status-info"
        >
            <flux:heading size="sm">{{ __('Informasi Status Terkini') }}</flux:heading>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                    <dd data-test="surat-diproses-detail-status-terkini">
                        {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Pengambilan') }}</dt>
                    <dd data-test="surat-diproses-detail-tanggal-pengambilan-set">
                        {{ $pengajuan->suratTerbit->tanggal_pengambilan?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '—' }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jam Kerja') }}</dt>
                    <dd data-test="surat-diproses-detail-jam-kerja-set">
                        {{ $pengajuan->suratTerbit->jam_kerja_label ?? '—' }}
                    </dd>
                </div>
                @if ($pengajuan->suratTerbit->siap_diambil_at)
                    <div class="grid gap-1">
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Ditandai Siap Diambil') }}</dt>
                        <dd data-test="surat-diproses-detail-siap-diambil-at">
                            {{ $pengajuan->suratTerbit->siap_diambil_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif
</div>

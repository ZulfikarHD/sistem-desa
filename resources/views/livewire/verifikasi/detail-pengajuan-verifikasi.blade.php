<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="verifikasi-detail-heading">
                {{ __('Detail Pengajuan') }}
            </flux:heading>
            <flux:text class="mt-1" data-test="verifikasi-detail-nomor">
                {{ $pengajuan->nomor_pengajuan }}
            </flux:text>
        </div>

        <flux:button
            variant="ghost"
            icon="arrow-left"
            :href="route('verifikasi.index')"
            wire:navigate
            data-test="verifikasi-detail-back"
        >
            {{ __('Kembali ke Daftar') }}
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Data Pengajuan') }}</flux:heading>

            <dl class="grid gap-3 text-sm">
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nama Warga') }}</dt>
                    <dd data-test="verifikasi-detail-nama-warga">{{ $pengajuan->user?->name ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('NIK') }}</dt>
                    <dd data-test="verifikasi-detail-nik">{{ $pengajuan->user?->nik ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jenis Surat') }}</dt>
                    <dd data-test="verifikasi-detail-jenis-surat">{{ $pengajuan->jenisSurat?->nama_surat ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Pengajuan') }}</dt>
                    <dd data-test="verifikasi-detail-tanggal">
                        {{ $pengajuan->tanggal_pengajuan?->translatedFormat('d M Y') }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                    <dd>
                        @php
                            $statusVariant = match ($pengajuan->status) {
                                \App\Models\PengajuanSurat::STATUS_DITOLAK => 'danger',
                                \App\Models\PengajuanSurat::STATUS_SELESAI => 'success',
                                \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                                \App\Models\PengajuanSurat::STATUS_DIPROSES,
                                \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL => 'warning',
                                default => 'neutral',
                            };
                        @endphp
                        <flux:badge :variant="$statusVariant" data-test="verifikasi-detail-status">
                            {{ \App\Models\PengajuanSurat::statusLabel($pengajuan->status) }}
                        </flux:badge>
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Keperluan') }}</dt>
                    <dd class="whitespace-pre-wrap" data-test="verifikasi-detail-keperluan">{{ $pengajuan->keperluan }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Dokumen Persyaratan') }}</flux:heading>

            @if ($pengajuan->dokumenPersyaratan->isEmpty())
                <flux:text data-test="verifikasi-detail-dokumen-empty">
                    {{ __('Belum ada dokumen yang diunggah.') }}
                </flux:text>
            @else
                <div class="flex flex-col gap-6">
                    @foreach ($pengajuan->dokumenPersyaratan as $dokumen)
                        <div
                            wire:key="verifikasi-dokumen-{{ $dokumen->id }}"
                            class="flex flex-col gap-3"
                            data-test="verifikasi-detail-dokumen-{{ $dokumen->id }}"
                        >
                            <flux:heading size="xs">{{ $dokumen->jenis_dokumen }}</flux:heading>

                            @if ($this->isPreviewableImage($dokumen))
                                <img
                                    src="{{ route('verifikasi.dokumen.show', $dokumen) }}"
                                    alt="{{ __('Pratinjau :jenis', ['jenis' => $dokumen->jenis_dokumen]) }}"
                                    class="max-h-80 w-full rounded-lg border border-zinc-200 object-contain dark:border-zinc-700"
                                    data-test="verifikasi-detail-dokumen-preview-{{ $dokumen->id }}"
                                />
                            @elseif ($this->isPreviewablePdf($dokumen))
                                <iframe
                                    src="{{ route('verifikasi.dokumen.show', $dokumen) }}"
                                    title="{{ __('Pratinjau :jenis', ['jenis' => $dokumen->jenis_dokumen]) }}"
                                    class="h-80 w-full rounded-lg border border-zinc-200 dark:border-zinc-700"
                                    data-test="verifikasi-detail-dokumen-preview-{{ $dokumen->id }}"
                                ></iframe>
                            @else
                                <flux:callout variant="warning" data-test="verifikasi-detail-dokumen-fallback-{{ $dokumen->id }}">
                                    {{ __('Pratinjau tidak tersedia. Unduh dokumen untuk memeriksa berkas.') }}
                                </flux:callout>
                            @endif

                            <div>
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="arrow-down-tray"
                                    :href="route('verifikasi.dokumen.download', $dokumen)"
                                    data-test="verifikasi-detail-dokumen-download-{{ $dokumen->id }}"
                                >
                                    {{ __('Unduh Dokumen') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if ($this->canVerify())
        <div
            class="flex flex-wrap gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700"
            data-test="verifikasi-detail-actions"
        >
            <flux:button
                variant="primary"
                icon="check"
                wire:click="setujui"
                data-test="verifikasi-detail-setujui-button"
            >
                {{ __('Setujui') }}
            </flux:button>

            <flux:button
                variant="danger"
                icon="x-mark"
                wire:click="openTolakModal"
                data-test="verifikasi-detail-tolak-button"
            >
                {{ __('Tolak') }}
            </flux:button>
        </div>
    @endif

    @if ($this->canMarkSiapDiambil())
        <div
            class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
            data-test="verifikasi-detail-siap-diambil-panel"
        >
            <div>
                <flux:heading size="sm">{{ __('Dokumen Siap Diambil') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Pilih tanggal pengambilan pada hari kerja. Jam mengikuti jam kerja kantor (bukan time-picker bebas).') }}
                </flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Tanggal Pengambilan') }}</flux:label>
                    <flux:input
                        type="date"
                        wire:model.live="tanggalPengambilan"
                        data-test="verifikasi-detail-tanggal-pengambilan"
                    />
                    <flux:error name="tanggalPengambilan" />
                </flux:field>

                <div class="grid gap-1 text-sm">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jam Kerja') }}</dt>
                    <dd data-test="verifikasi-detail-jam-kerja-preview">
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

            <flux:callout variant="secondary" data-test="verifikasi-detail-jam-kerja-info">
                {{ __('Senin–Kamis: 08.00–16.00 WIB · Jumat: 08.00–16.30 WIB · Sabtu–Minggu & libur nasional: tutup') }}
            </flux:callout>

            <div>
                <flux:button
                    variant="primary"
                    icon="check-badge"
                    wire:click="tandaiDokumenSiapDiambil"
                    :disabled="! $this->isTanggalPengambilanSiap()"
                    data-test="verifikasi-detail-siap-diambil-button"
                >
                    {{ __('Dokumen Siap Diambil') }}
                </flux:button>
            </div>
        </div>
    @endif

    @if ($pengajuan->status === \App\Models\PengajuanSurat::STATUS_SIAP_DIAMBIL && $pengajuan->suratTerbit)
        <div
            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
            data-test="verifikasi-detail-siap-diambil-info"
        >
            <flux:heading size="sm">{{ __('Informasi Pengambilan') }}</flux:heading>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Pengambilan') }}</dt>
                    <dd data-test="verifikasi-detail-tanggal-pengambilan-set">
                        {{ $pengajuan->suratTerbit->tanggal_pengambilan?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '—' }}
                    </dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jam Kerja') }}</dt>
                    <dd data-test="verifikasi-detail-jam-kerja-set">
                        {{ $pengajuan->suratTerbit->jam_kerja_label ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    @endif

    <flux:modal
        wire:model.self="showTolakModal"
        wire:close="closeTolakModal"
        class="md:w-[32rem]"
        data-test="verifikasi-detail-tolak-modal"
    >
        <form wire:submit="tolak" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tolak Pengajuan') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Berikan alasan penolakan agar warga memahami keputusan ini.') }}
                </flux:text>
            </div>

            <flux:textarea
                wire:model="catatanAdmin"
                label="{{ __('Alasan Penolakan') }}"
                placeholder="{{ __('Contoh: Dokumen KTP tidak terbaca dengan jelas.') }}"
                rows="4"
                data-test="verifikasi-detail-catatan-admin"
            />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="closeTolakModal"
                    data-test="verifikasi-detail-tolak-cancel"
                >
                    {{ __('Batal') }}
                </flux:button>
                <flux:button
                    type="submit"
                    variant="danger"
                    data-test="verifikasi-detail-tolak-confirm"
                >
                    {{ __('Tolak Pengajuan') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>

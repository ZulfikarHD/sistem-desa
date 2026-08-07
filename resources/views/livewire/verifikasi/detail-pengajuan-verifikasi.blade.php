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
                            $statusColor = \App\Models\PengajuanSurat::statusBadgeColor($pengajuan->status);
                        @endphp
                        <flux:badge :color="$statusColor" data-test="verifikasi-detail-status">
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

            <div class="flex flex-col gap-6">
                <section class="flex flex-col gap-3" data-test="verifikasi-detail-dokumen-online">
                    <flux:heading size="xs">{{ __('Diunggah online') }}</flux:heading>

                    @forelse ($this->itemDokumenOnline() as $index => $item)
                        @php
                            /** @var \App\Models\JenisSuratPersyaratan|null $syarat */
                            $syarat = $item['syarat'];
                            /** @var \App\Models\DokumenPersyaratan|null $dokumen */
                            $dokumen = $item['dokumen'];
                            $status = $item['status'];
                            $label = $syarat?->nama
                                ?? ($dokumen?->labelDokumen() ?? __('Dokumen'));
                            $rowKey = $dokumen?->id ?? ('syarat-'.($syarat?->id ?? $index));
                        @endphp

                        <div
                            wire:key="verifikasi-dokumen-online-{{ $rowKey }}"
                            class="flex flex-col gap-3"
                            data-test="verifikasi-detail-dokumen-online-item-{{ $rowKey }}"
                            data-status="{{ $status }}"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:heading size="xs" data-test="verifikasi-detail-dokumen-online-label-{{ $rowKey }}">
                                    {{ $label }}
                                </flux:heading>

                                @if ($status === 'optional_empty')
                                    <flux:badge
                                        color="amber"
                                        size="sm"
                                        data-test="verifikasi-detail-dokumen-optional-empty-{{ $syarat?->id }}"
                                    >
                                        {{ __('Tidak diunggah — diperbolehkan') }}
                                    </flux:badge>
                                @elseif ($status === 'missing_required')
                                    <flux:badge
                                        color="red"
                                        size="sm"
                                        data-test="verifikasi-detail-dokumen-missing-{{ $syarat?->id }}"
                                    >
                                        {{ __('Belum diunggah') }}
                                    </flux:badge>
                                @endif
                            </div>

                            @if ($dokumen !== null)
                                <div data-test="verifikasi-detail-dokumen-{{ $dokumen->id }}">
                                    @if ($this->isPreviewableImage($dokumen))
                                        <img
                                            src="{{ route('verifikasi.dokumen.show', $dokumen) }}"
                                            alt="{{ __('Pratinjau :jenis', ['jenis' => $label]) }}"
                                            class="max-h-80 w-full rounded-lg border border-zinc-200 object-contain dark:border-zinc-700"
                                            data-test="verifikasi-detail-dokumen-preview-{{ $dokumen->id }}"
                                        />
                                    @elseif ($this->isPreviewablePdf($dokumen))
                                        <iframe
                                            src="{{ route('verifikasi.dokumen.show', $dokumen) }}"
                                            title="{{ __('Pratinjau :jenis', ['jenis' => $label]) }}"
                                            class="h-80 w-full rounded-lg border border-zinc-200 dark:border-zinc-700"
                                            data-test="verifikasi-detail-dokumen-preview-{{ $dokumen->id }}"
                                        ></iframe>
                                    @else
                                        <flux:callout variant="warning" data-test="verifikasi-detail-dokumen-fallback-{{ $dokumen->id }}">
                                            {{ __('Pratinjau tidak tersedia. Unduh dokumen untuk memeriksa berkas.') }}
                                        </flux:callout>
                                    @endif

                                    <div class="mt-3">
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
                            @elseif ($status === 'optional_empty')
                                <flux:text class="text-sm text-zinc-500">
                                    {{ __('Warga tidak mengunggah berkas ini (opsional).') }}
                                </flux:text>
                            @endif
                        </div>
                    @empty
                        <flux:text data-test="verifikasi-detail-dokumen-empty">
                            {{ __('Belum ada dokumen yang diunggah.') }}
                        </flux:text>
                    @endforelse
                </section>

                <section class="flex flex-col gap-3" data-test="verifikasi-detail-checklist-fisik">
                    <flux:heading size="xs">{{ __('Harus dicek / dibawa ke kantor') }}</flux:heading>

                    @if ($this->itemChecklistFisik()->isEmpty())
                        <flux:text class="text-sm text-zinc-500" data-test="verifikasi-detail-checklist-fisik-empty">
                            {{ __('Tidak ada syarat yang harus dibawa ke kantor untuk jenis surat ini.') }}
                        </flux:text>
                    @else
                        <ul class="space-y-2" data-test="verifikasi-detail-checklist-fisik-list">
                            @foreach ($this->itemChecklistFisik() as $syaratFisik)
                                <li
                                    wire:key="verifikasi-checklist-fisik-{{ $syaratFisik->id }}"
                                    class="flex items-start gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                                    data-test="verifikasi-detail-checklist-fisik-item-{{ $syaratFisik->id }}"
                                >
                                    <flux:icon.clipboard-document-check class="mt-0.5 size-5 shrink-0 text-blue-600 dark:text-blue-400" />
                                    <div class="space-y-1">
                                        <span
                                            class="text-sm font-medium"
                                            data-test="verifikasi-detail-checklist-fisik-nama-{{ $syaratFisik->id }}"
                                        >
                                            {{ $syaratFisik->nama }}
                                        </span>
                                        <flux:text class="text-sm">
                                            {{ __('Periksa berkas fisik saat warga datang / saat pengambilan.') }}
                                        </flux:text>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
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

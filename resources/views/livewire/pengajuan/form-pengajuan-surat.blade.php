<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" data-test="pengajuan-surat-heading">
            {{ __('Pengajuan Surat Keterangan') }}
        </flux:heading>
        <flux:text class="mt-1">
            @if ($resubmitFromId)
                {{ __('Perbaiki pengajuan sebelumnya berdasarkan catatan admin, lalu kirim ulang.') }}
            @else
                {{ __('Isi formulir berikut untuk mengajukan surat keterangan ke kantor desa.') }}
            @endif
        </flux:text>
    </div>

    @if ($catatanAdminReferensi)
        <flux:callout icon="exclamation-triangle" variant="warning" data-test="pengajuan-surat-catatan-admin-referensi">
            <flux:callout.heading>{{ __('Catatan Admin dari Pengajuan Sebelumnya') }}</flux:callout.heading>
            <flux:callout.text>
                <span data-test="pengajuan-surat-nomor-sebelumnya">
                    {{ __('Nomor pengajuan sebelumnya:') }}
                    <strong>{{ $nomorPengajuanSebelumnya }}</strong>
                </span>
                <br />
                {{ $catatanAdminReferensi }}
            </flux:callout.text>
        </flux:callout>
    @endif

    @if ($submittedNomor)
        <flux:callout icon="check-circle" variant="success" data-test="pengajuan-surat-success">
            <flux:callout.heading>{{ __('Pengajuan Berhasil Dikirim') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Nomor pengajuan Anda:') }}
                <strong data-test="pengajuan-surat-nomor">{{ $submittedNomor }}</strong>.
                {{ __('Simpan nomor ini untuk melacak status pengajuan.') }}
            </flux:callout.text>
            <x-slot name="actions">
                <flux:button
                    variant="primary"
                    wire:click="createAnother"
                    data-test="pengajuan-surat-create-another"
                >
                    {{ __('Ajukan Surat Lain') }}
                </flux:button>
            </x-slot>
        </flux:callout>
    @else
        <form wire:submit="submit" class="mx-auto w-full max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ __('Jenis Surat') }}</flux:label>
                <flux:select
                    wire:model.live="jenis_surat_id"
                    placeholder="{{ __('Pilih jenis surat...') }}"
                    data-test="pengajuan-surat-jenis-select"
                >
                    @foreach ($jenisSuratOptions as $option)
                        <flux:select.option
                            value="{{ $option->id }}"
                            data-test="pengajuan-surat-jenis-option-{{ $option->id }}"
                        >
                            {{ $option->nama_surat }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="jenis_surat_id" />
            </flux:field>

            @if ($jenisSuratOptions->isEmpty())
                <flux:callout icon="exclamation-triangle" variant="warning" data-test="pengajuan-surat-no-jenis">
                    <flux:callout.heading>{{ __('Belum Ada Jenis Surat') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('Master data jenis surat belum tersedia. Silakan hubungi admin desa atau cek kembali nanti.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            <flux:field>
                <flux:label>{{ __('Keperluan') }}</flux:label>
                <flux:textarea
                    wire:model="keperluan"
                    rows="5"
                    placeholder="{{ __('Jelaskan keperluan pengajuan surat, misalnya untuk keperluan administrasi bank.') }}"
                    data-test="pengajuan-surat-keperluan-input"
                />
                <flux:error name="keperluan" />
            </flux:field>

            @if ($jenis_surat_id && $this->persyaratanRows->isEmpty())
                <flux:callout icon="exclamation-triangle" variant="warning" data-test="pengajuan-surat-persyaratan-kosong">
                    <flux:callout.heading>{{ __('Persyaratan Belum Diatur') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('Jenis surat ini belum punya daftar persyaratan. Silakan hubungi admin desa.') }}
                    </flux:callout.text>
                </flux:callout>
            @endif

            @if ($this->persyaratanRows->isNotEmpty())
                <div class="space-y-4" data-test="pengajuan-surat-persyaratan-section">
                    <div>
                        <flux:heading size="sm">{{ __('Persyaratan') }}</flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Periksa setiap syarat di bawah. Badge mengikuti aturan yang diatur admin.') }}
                        </flux:text>
                    </div>

                    <ul class="space-y-3" data-test="pengajuan-surat-persyaratan-list">
                        @foreach ($this->persyaratanRows as $syarat)
                            <li
                                class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                                wire:key="persyaratan-{{ $syarat->id }}"
                                data-test="pengajuan-surat-persyaratan-item-{{ $syarat->id }}"
                                data-cara="{{ $syarat->cara_pemenuhan }}"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium" data-test="pengajuan-surat-persyaratan-nama-{{ $syarat->id }}">
                                        {{ $syarat->nama }}
                                    </span>
                                    <flux:badge
                                        :color="$syarat->badgeColor()"
                                        size="sm"
                                        data-test="pengajuan-surat-persyaratan-badge-{{ $syarat->id }}"
                                    >
                                        {{ __($syarat->badgeLabel()) }}
                                    </flux:badge>
                                </div>

                                @if ($syarat->cara_pemenuhan === \App\Models\JenisSuratPersyaratan::CARA_BAWA_KANTOR)
                                    <flux:text class="mt-2 text-sm" data-test="pengajuan-surat-bawa-kantor-help-{{ $syarat->id }}">
                                        {{ __($bantuanBawaKantor) }}
                                    </flux:text>
                                @endif

                                @if ($syarat->cara_pemenuhan === \App\Models\JenisSuratPersyaratan::CARA_INFO)
                                    <flux:text class="mt-2 text-sm" data-test="pengajuan-surat-info-help-{{ $syarat->id }}">
                                        {{ __('Catatan informasi — tidak perlu diunggah maupun dibawa.') }}
                                    </flux:text>
                                @endif

                                @if ($syarat->cara_pemenuhan === \App\Models\JenisSuratPersyaratan::CARA_UNGGAH)
                                    <div class="mt-3" data-test="pengajuan-surat-dokumen-slot-{{ $syarat->id }}">
                                        <flux:field>
                                            <flux:label>
                                                {{ __('Unggah :nama', ['nama' => $syarat->nama]) }}
                                                @unless ($syarat->is_wajib)
                                                    <span class="font-normal text-zinc-500">({{ __('opsional') }})</span>
                                                @endunless
                                            </flux:label>
                                            <flux:input
                                                type="file"
                                                wire:model="dokumenFiles.{{ $syarat->id }}"
                                                accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                                data-test="pengajuan-surat-dokumen-input-{{ $syarat->id }}"
                                            />
                                            <flux:error name="dokumenFiles.{{ $syarat->id }}" />

                                            @if (! empty($dokumenFiles[$syarat->id]))
                                                @php $file = $dokumenFiles[$syarat->id]; @endphp
                                                <div
                                                    class="mt-3 flex items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                                                    data-test="pengajuan-surat-dokumen-preview-{{ $syarat->id }}"
                                                >
                                                    @if (str_starts_with($file->getMimeType(), 'image/'))
                                                        <img
                                                            src="{{ $file->temporaryUrl() }}"
                                                            alt="{{ __('Preview :nama', ['nama' => $syarat->nama]) }}"
                                                            class="h-20 w-20 rounded object-cover"
                                                        />
                                                    @else
                                                        <flux:icon.document class="size-10 text-zinc-400" />
                                                    @endif

                                                    <div class="min-w-0 flex-1">
                                                        <flux:text class="truncate font-medium">
                                                            {{ $file->getClientOriginalName() }}
                                                        </flux:text>
                                                        <flux:text class="text-sm">
                                                            {{ number_format($file->getSize() / 1024, 1) }} KB
                                                        </flux:text>
                                                    </div>

                                                    <flux:button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        icon="x-mark"
                                                        wire:click="removeDokumen({{ $syarat->id }})"
                                                        data-test="pengajuan-surat-dokumen-remove-{{ $syarat->id }}"
                                                    >
                                                        {{ __('Hapus') }}
                                                    </flux:button>
                                                </div>
                                            @endif
                                        </flux:field>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($this->unggahPersyaratan->isNotEmpty())
                        <div
                            wire:loading
                            wire:target="dokumenFiles"
                            class="text-sm text-zinc-500"
                            data-test="pengajuan-surat-dokumen-loading"
                        >
                            {{ __('Mengunggah file...') }}
                        </div>
                        <flux:text class="text-sm" data-test="pengajuan-surat-dokumen-section">
                            {{ __('Format yang diterima: JPG, PNG, atau PDF. Ukuran maksimum :size MB per file.', ['size' => number_format($maxFileSizeKb / 1024, 0)]) }}
                        </flux:text>
                    @endif
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="paper-airplane"
                    data-test="pengajuan-surat-submit-button"
                    :disabled="$jenisSuratOptions->isEmpty()"
                >
                    {{ __('Kirim Pengajuan') }}
                </flux:button>

                <flux:button
                    :href="route('persyaratan-dokumen.index')"
                    variant="ghost"
                    wire:navigate
                    data-test="pengajuan-surat-lihat-persyaratan"
                >
                    {{ __('Lihat Persyaratan Dokumen') }}
                </flux:button>
            </div>
        </form>
    @endif
</div>

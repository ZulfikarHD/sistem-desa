<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" data-test="pengajuan-surat-heading">
            {{ __('Pengajuan Surat Keterangan') }}
        </flux:heading>
        <flux:text class="mt-1">
            {{ __('Isi formulir berikut untuk mengajukan surat keterangan ke kantor desa.') }}
        </flux:text>
    </div>

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

            @if ($this->requiredDokumenTypes !== [])
                <div class="space-y-4" data-test="pengajuan-surat-dokumen-section">
                    <div>
                        <flux:heading size="sm">{{ __('Unggah Dokumen Persyaratan') }}</flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Format yang diterima: JPG, PNG, atau PDF. Ukuran maksimum :size MB per file.', ['size' => number_format($maxFileSizeKb / 1024, 0)]) }}
                        </flux:text>
                    </div>

                    @if (in_array(\App\Models\DokumenPersyaratan::JENIS_KTP, $this->requiredDokumenTypes, true))
                        <flux:field>
                            <flux:label>{{ __('Fotokopi KTP') }}</flux:label>
                            <flux:input
                                type="file"
                                wire:model="dokumenKtp"
                                accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                data-test="pengajuan-surat-dokumen-ktp-input"
                            />
                            <flux:error name="dokumenKtp" />

                            @if ($dokumenKtp)
                                <div
                                    class="mt-3 flex items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                                    data-test="pengajuan-surat-dokumen-ktp-preview"
                                >
                                    @if (str_starts_with($dokumenKtp->getMimeType(), 'image/'))
                                        <img
                                            src="{{ $dokumenKtp->temporaryUrl() }}"
                                            alt="{{ __('Preview KTP') }}"
                                            class="h-20 w-20 rounded object-cover"
                                        />
                                    @else
                                        <flux:icon.document class="size-10 text-zinc-400" />
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <flux:text class="truncate font-medium">
                                            {{ $dokumenKtp->getClientOriginalName() }}
                                        </flux:text>
                                        <flux:text class="text-sm">
                                            {{ number_format($dokumenKtp->getSize() / 1024, 1) }} KB
                                        </flux:text>
                                    </div>

                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="x-mark"
                                        wire:click="removeDokumenKtp"
                                        data-test="pengajuan-surat-dokumen-ktp-remove"
                                    >
                                        {{ __('Hapus') }}
                                    </flux:button>
                                </div>
                            @endif
                        </flux:field>
                    @endif

                    @if (in_array(\App\Models\DokumenPersyaratan::JENIS_KK, $this->requiredDokumenTypes, true))
                        <flux:field>
                            <flux:label>{{ __('Fotokopi Kartu Keluarga (KK)') }}</flux:label>
                            <flux:input
                                type="file"
                                wire:model="dokumenKk"
                                accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                data-test="pengajuan-surat-dokumen-kk-input"
                            />
                            <flux:error name="dokumenKk" />

                            @if ($dokumenKk)
                                <div
                                    class="mt-3 flex items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                                    data-test="pengajuan-surat-dokumen-kk-preview"
                                >
                                    @if (str_starts_with($dokumenKk->getMimeType(), 'image/'))
                                        <img
                                            src="{{ $dokumenKk->temporaryUrl() }}"
                                            alt="{{ __('Preview KK') }}"
                                            class="h-20 w-20 rounded object-cover"
                                        />
                                    @else
                                        <flux:icon.document class="size-10 text-zinc-400" />
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <flux:text class="truncate font-medium">
                                            {{ $dokumenKk->getClientOriginalName() }}
                                        </flux:text>
                                        <flux:text class="text-sm">
                                            {{ number_format($dokumenKk->getSize() / 1024, 1) }} KB
                                        </flux:text>
                                    </div>

                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="x-mark"
                                        wire:click="removeDokumenKk"
                                        data-test="pengajuan-surat-dokumen-kk-remove"
                                    >
                                        {{ __('Hapus') }}
                                    </flux:button>
                                </div>
                            @endif
                        </flux:field>
                    @endif

                    <div wire:loading wire:target="dokumenKtp,dokumenKk" class="text-sm text-zinc-500">
                        {{ __('Mengunggah file...') }}
                    </div>
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

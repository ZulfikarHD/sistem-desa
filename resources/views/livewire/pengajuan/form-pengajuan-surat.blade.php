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
                    wire:model="jenis_surat_id"
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

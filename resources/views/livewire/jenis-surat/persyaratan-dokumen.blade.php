<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-2">
        <flux:heading size="xl" data-test="persyaratan-dokumen-heading">
            {{ __('Persyaratan Dokumen') }}
        </flux:heading>
        <flux:text>
            {{ __('Lihat jenis surat keterangan beserta deskripsi dan dokumen yang perlu disiapkan sebelum mengajukan.') }}
        </flux:text>
    </div>

    @guest
        <flux:callout icon="information-circle" variant="secondary" data-test="persyaratan-dokumen-guest-cta">
            <flux:callout.heading>{{ __('Daftar/Login untuk Mengajukan') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Halaman ini hanya menampilkan informasi persyaratan. Untuk mengajukan surat, silakan daftar atau masuk terlebih dahulu.') }}
            </flux:callout.text>
            <x-slot name="actions">
                @if (Route::has('register'))
                    <flux:button
                        :href="route('register')"
                        variant="primary"
                        size="sm"
                        wire:navigate
                        data-test="persyaratan-dokumen-cta-register"
                    >
                        {{ __('Daftar') }}
                    </flux:button>
                @endif
                @if (Route::has('login'))
                    <flux:button
                        :href="route('login')"
                        variant="filled"
                        size="sm"
                        wire:navigate
                        data-test="persyaratan-dokumen-cta-login"
                    >
                        {{ __('Login untuk Mengajukan') }}
                    </flux:button>
                @endif
            </x-slot>
        </flux:callout>
    @endguest

    <div class="max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Cari nama, deskripsi, atau persyaratan...') }}"
            data-test="persyaratan-dokumen-search"
        />
    </div>

    @if ($jenisSuratList->isEmpty())
        <div
            class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600"
            data-test="persyaratan-dokumen-empty"
        >
            <flux:heading size="sm">
                {{ __('Belum ada jenis surat') }}
            </flux:heading>
            <flux:text class="mt-2">
                @if (trim($search) !== '')
                    {{ __('Tidak ada hasil untuk pencarian Anda. Coba kata kunci lain.') }}
                @else
                    {{ __('Daftar jenis surat belum tersedia. Silakan cek kembali nanti.') }}
                @endif
            </flux:text>
        </div>
    @else
        <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
            data-test="persyaratan-dokumen-list"
        >
            @foreach ($jenisSuratList as $item)
                <article
                    wire:key="persyaratan-dokumen-card-{{ $item->id }}"
                    class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900"
                    data-test="persyaratan-dokumen-card-{{ $item->id }}"
                >
                    <div class="space-y-1">
                        <flux:heading size="sm" data-test="persyaratan-dokumen-nama-{{ $item->id }}">
                            {{ $item->nama_surat }}
                        </flux:heading>
                        <flux:text class="line-clamp-2" data-test="persyaratan-dokumen-deskripsi-{{ $item->id }}">
                            {{ $item->deskripsi ?: __('Tidak ada deskripsi.') }}
                        </flux:text>
                    </div>

                    <div class="space-y-1">
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            {{ __('Persyaratan') }}
                        </flux:text>
                        <flux:text
                            class="line-clamp-3 whitespace-pre-line"
                            data-test="persyaratan-dokumen-preview-{{ $item->id }}"
                        >
                            {{ $item->persyaratan_dokumen }}
                        </flux:text>
                    </div>

                    <div class="mt-auto pt-1">
                        <flux:button
                            variant="primary"
                            size="sm"
                            icon="eye"
                            wire:click="openDetail({{ $item->id }})"
                            class="w-full sm:w-auto"
                            data-test="persyaratan-dokumen-detail-button-{{ $item->id }}"
                        >
                            {{ __('Lihat Detail') }}
                        </flux:button>
                    </div>
                </article>
            @endforeach
        </div>

        <div data-test="persyaratan-dokumen-pagination">
            <flux:pagination :paginator="$jenisSuratList" />
        </div>
    @endif

    <flux:modal
        wire:model.self="showDetail"
        wire:close="closeDetail"
        class="md:w-[32rem]"
        data-test="persyaratan-dokumen-detail-modal"
    >
        @if ($selectedJenisSurat)
            <div class="space-y-6" data-test="persyaratan-dokumen-detail-content">
                <div>
                    <flux:heading size="lg" data-test="persyaratan-dokumen-detail-title">
                        {{ $selectedJenisSurat->nama_surat }}
                    </flux:heading>
                    <flux:text class="mt-2" data-test="persyaratan-dokumen-detail-deskripsi">
                        {{ $selectedJenisSurat->deskripsi ?: __('Tidak ada deskripsi.') }}
                    </flux:text>
                </div>

                <div class="space-y-2">
                    <flux:heading size="sm">{{ __('Persyaratan Dokumen') }}</flux:heading>
                    <flux:text
                        class="whitespace-pre-line"
                        data-test="persyaratan-dokumen-detail-persyaratan"
                    >
                        {{ $selectedJenisSurat->persyaratan_dokumen }}
                    </flux:text>
                </div>

                <flux:callout icon="information-circle" data-test="persyaratan-dokumen-detail-note">
                    <flux:callout.text>
                        @guest
                            {{ __('Siapkan dokumen di atas sebelum mengajukan surat. Daftar atau masuk terlebih dahulu untuk mengajukan (fitur pengajuan akan tersedia di tahap berikutnya).') }}
                        @else
                            {{ __('Siapkan dokumen di atas sebelum mengajukan surat. Fitur pengajuan akan tersedia di tahap berikutnya.') }}
                        @endguest
                    </flux:callout.text>
                </flux:callout>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="closeDetail"
                        data-test="persyaratan-dokumen-detail-close"
                    >
                        {{ __('Tutup') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

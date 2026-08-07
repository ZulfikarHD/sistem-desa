<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="jenis-surat-heading">{{ __('Jenis Surat') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Kelola master data jenis surat keterangan dan persyaratan dokumennya.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:switch
                wire:model.live="showTrashed"
                label="{{ __('Tampilkan arsip') }}"
                data-test="jenis-surat-trash-toggle"
            />

            @unless ($showTrashed)
                <flux:button
                    variant="primary"
                    icon="plus"
                    wire:click="create"
                    data-test="jenis-surat-create-button"
                >
                    {{ __('Tambah Jenis Surat') }}
                </flux:button>
            @endunless
        </div>
    </div>

    <div class="max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Cari nama, deskripsi, atau persyaratan...') }}"
            data-test="jenis-surat-search"
        />
    </div>

    @if ($jenisSuratList->isEmpty())
        <div
            class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600"
            data-test="jenis-surat-empty"
        >
            <flux:heading size="sm">
                {{ $showTrashed ? __('Arsip kosong') : __('Belum ada data jenis surat') }}
            </flux:heading>
            <flux:text class="mt-2">
                @if (trim($search) !== '')
                    {{ __('Tidak ada hasil untuk pencarian Anda. Coba kata kunci lain.') }}
                @elseif ($showTrashed)
                    {{ __('Belum ada jenis surat yang diarsipkan.') }}
                @else
                    {{ __('Tambahkan jenis surat pertama agar warga dapat melihat persyaratan dokumen.') }}
                @endif
            </flux:text>
        </div>
    @else
        <flux:table :paginate="$jenisSuratList" data-test="jenis-surat-table">
            <flux:table.columns>
                <flux:table.column>{{ __('Nama Surat') }}</flux:table.column>
                <flux:table.column>{{ __('Deskripsi') }}</flux:table.column>
                <flux:table.column>{{ __('Persyaratan') }}</flux:table.column>
                <flux:table.column>{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($jenisSuratList as $item)
                    <flux:table.row wire:key="jenis-surat-{{ $item->id }}" data-test="jenis-surat-row-{{ $item->id }}">
                        <flux:table.cell variant="strong" data-test="jenis-surat-nama-{{ $item->id }}">
                            {{ $item->nama_surat }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="line-clamp-2">{{ $item->deskripsi ?: '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="line-clamp-2 whitespace-pre-line" data-test="jenis-surat-persyaratan-summary-{{ $item->id }}">
                                {{ $item->persyaratan_dokumen }}
                            </span>
                            @if (($item->persyaratan_count ?? 0) === 0)
                                <flux:badge color="amber" size="sm" class="mt-1" data-test="jenis-surat-fallback-badge-{{ $item->id }}">
                                    {{ __('Belum ada baris syarat') }}
                                </flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @if ($showTrashed)
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-uturn-left"
                                        wire:click="restore({{ $item->id }})"
                                        data-test="jenis-surat-restore-{{ $item->id }}"
                                    >
                                        {{ __('Pulihkan') }}
                                    </flux:button>
                                    <flux:button
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        wire:click="confirmForceDelete({{ $item->id }})"
                                        data-test="jenis-surat-force-delete-{{ $item->id }}"
                                    >
                                        {{ __('Hapus Permanen') }}
                                    </flux:button>
                                @else
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        wire:click="edit({{ $item->id }})"
                                        data-test="jenis-surat-edit-{{ $item->id }}"
                                    >
                                        {{ __('Ubah') }}
                                    </flux:button>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="archive-box"
                                        wire:click="softDelete({{ $item->id }})"
                                        wire:confirm="{{ __('Pindahkan jenis surat ini ke arsip? Data masih bisa dipulihkan.') }}"
                                        data-test="jenis-surat-soft-delete-{{ $item->id }}"
                                    >
                                        {{ __('Arsipkan') }}
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <flux:modal wire:model.self="showForm" wire:close="resetForm" class="md:w-[42rem]" data-test="jenis-surat-form-modal">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg" data-test="jenis-surat-form-title">
                    {{ $editingId ? __('Ubah Jenis Surat') : __('Tambah Jenis Surat') }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ __('Nama surat wajib diisi. Tambahkan minimal satu baris persyaratan. Deskripsi bersifat opsional.') }}
                </flux:text>
            </div>

            <flux:input
                wire:model="nama_surat"
                label="{{ __('Nama Surat') }}"
                placeholder="{{ __('Contoh: Surat Keterangan Domisili') }}"
                data-test="jenis-surat-nama-input"
            />

            <flux:textarea
                wire:model="deskripsi"
                label="{{ __('Deskripsi') }}"
                description="{{ __('Opsional') }}"
                rows="3"
                placeholder="{{ __('Ringkasan singkat kegunaan surat ini') }}"
                data-test="jenis-surat-deskripsi-input"
            />

            <div class="space-y-3" data-test="jenis-surat-persyaratan-section">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="sm">{{ __('Persyaratan dokumen') }}</flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Satu baris = satu syarat. Pilih bagaimana warga memenuhinya.') }}
                        </flux:text>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <flux:button
                            type="button"
                            variant="ghost"
                            size="sm"
                            icon="document-duplicate"
                            wire:click="applyDomisiliTemplate"
                            data-test="jenis-surat-template-domisili"
                        >
                            {{ __('Template KTP + KK + Pengantar RT') }}
                        </flux:button>
                        <flux:button
                            type="button"
                            variant="filled"
                            size="sm"
                            icon="plus"
                            wire:click="addPersyaratanRow"
                            data-test="jenis-surat-add-persyaratan"
                        >
                            {{ __('Tambah syarat') }}
                        </flux:button>
                    </div>
                </div>

                @error('persyaratanRows')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                <div class="space-y-4">
                    @forelse ($persyaratanRows as $index => $row)
                        <div
                            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
                            wire:key="persyaratan-row-{{ $row['key'] ?? $index }}"
                            data-test="jenis-surat-persyaratan-row-{{ $index }}"
                        >
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <flux:text class="text-sm font-medium">
                                    {{ __('Syarat') }} {{ $index + 1 }}
                                </flux:text>
                                <div class="flex flex-wrap gap-1">
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="chevron-up"
                                        wire:click="movePersyaratanRowUp({{ $index }})"
                                        :disabled="$index === 0"
                                        data-test="jenis-surat-persyaratan-up-{{ $index }}"
                                    />
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="chevron-down"
                                        wire:click="movePersyaratanRowDown({{ $index }})"
                                        :disabled="$index === count($persyaratanRows) - 1"
                                        data-test="jenis-surat-persyaratan-down-{{ $index }}"
                                    />
                                    <flux:button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        wire:click="removePersyaratanRow({{ $index }})"
                                        :disabled="count($persyaratanRows) <= 1"
                                        data-test="jenis-surat-persyaratan-remove-{{ $index }}"
                                    >
                                        {{ __('Hapus') }}
                                    </flux:button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <flux:input
                                    wire:model="persyaratanRows.{{ $index }}.nama"
                                    label="{{ __('Nama syarat') }}"
                                    placeholder="{{ __('Contoh: Fotokopi KTP') }}"
                                    data-test="jenis-surat-persyaratan-nama-{{ $index }}"
                                />

                                <div>
                                    <flux:radio.group
                                        wire:model.live="persyaratanRows.{{ $index }}.cara_pemenuhan"
                                        label="{{ __('Bagaimana warga memenuhi?') }}"
                                        data-test="jenis-surat-persyaratan-cara-{{ $index }}"
                                    >
                                        @foreach ($caraPemenuhanOptions as $value => $label)
                                            <flux:radio value="{{ $value }}" data-test="jenis-surat-persyaratan-cara-{{ $index }}-{{ $value }}">
                                                {{ __($label) }}
                                            </flux:radio>
                                        @endforeach
                                    </flux:radio.group>

                                    @php
                                        $caraAktif = $row['cara_pemenuhan'] ?? '';
                                        $helperCara = $caraPemenuhanHelpers[$caraAktif] ?? null;
                                    @endphp
                                    @if ($helperCara)
                                        <flux:text class="mt-1 text-sm" data-test="jenis-surat-persyaratan-cara-helper-{{ $index }}">
                                            {{ __($helperCara) }}
                                        </flux:text>
                                    @endif
                                </div>

                                @if (($row['cara_pemenuhan'] ?? '') === \App\Models\JenisSuratPersyaratan::CARA_UNGGAH)
                                    <div data-test="jenis-surat-persyaratan-wajib-wrap-{{ $index }}">
                                        <flux:radio.group
                                            wire:model.live="persyaratanRows.{{ $index }}.is_wajib"
                                            label="{{ __('Apakah harus diunggah?') }}"
                                            data-test="jenis-surat-persyaratan-wajib-{{ $index }}"
                                        >
                                            {{-- Nilai 1/0 agar Livewire boolean cast benar (string "false" bersifat truthy). --}}
                                            <flux:radio value="1" data-test="jenis-surat-persyaratan-wajib-{{ $index }}-true">
                                                {{ __('Wajib') }}
                                            </flux:radio>
                                            <flux:radio value="0" data-test="jenis-surat-persyaratan-wajib-{{ $index }}-false">
                                                {{ __('Boleh dikosongkan') }}
                                            </flux:radio>
                                        </flux:radio.group>
                                        <flux:text class="mt-1 text-sm">
                                            @if (filter_var($row['is_wajib'] ?? true, FILTER_VALIDATE_BOOLEAN))
                                                {{ __('Harus diunggah sebelum pengajuan dikirim.') }}
                                            @else
                                                {{ __('Opsional / jika ada; pengajuan tetap bisa dikirim tanpa file ini.') }}
                                            @endif
                                        </flux:text>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <flux:text data-test="jenis-surat-persyaratan-empty">
                            {{ __('Belum ada baris persyaratan. Klik Tambah syarat.') }}
                        </flux:text>
                    @endforelse
                </div>
            </div>

            <div
                class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-800/40"
                data-test="jenis-surat-pratinjau"
            >
                <flux:heading size="sm">{{ __('Pratinjau untuk warga') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ __('Tampilan badge yang sama seperti yang dilihat warga.') }}
                </flux:text>

                <ul class="mt-3 space-y-2" data-test="jenis-surat-pratinjau-list">
                    @forelse ($persyaratanRows as $index => $row)
                        @php
                            $namaPratinjau = trim((string) ($row['nama'] ?? ''));
                            $cara = $row['cara_pemenuhan'] ?? '';
                            $isWajibPratinjau = filter_var($row['is_wajib'] ?? true, FILTER_VALIDATE_BOOLEAN);
                            $badge = match ($cara) {
                                \App\Models\JenisSuratPersyaratan::CARA_UNGGAH => $isWajibPratinjau
                                    ? 'Wajib diunggah'
                                    : 'Boleh dikosongkan',
                                \App\Models\JenisSuratPersyaratan::CARA_BAWA_KANTOR => 'Bawa ke kantor',
                                default => 'Informasi',
                            };
                            $badgeColor = match ($cara) {
                                \App\Models\JenisSuratPersyaratan::CARA_UNGGAH => $isWajibPratinjau ? 'red' : 'amber',
                                \App\Models\JenisSuratPersyaratan::CARA_BAWA_KANTOR => 'blue',
                                default => 'zinc',
                            };
                        @endphp
                        <li
                            class="flex flex-wrap items-center gap-2"
                            wire:key="pratinjau-{{ $row['key'] ?? $index }}"
                            data-test="jenis-surat-pratinjau-item-{{ $index }}"
                        >
                            <span class="text-sm">{{ $namaPratinjau !== '' ? $namaPratinjau : __('(Nama syarat belum diisi)') }}</span>
                            <flux:badge :color="$badgeColor" size="sm" data-test="jenis-surat-pratinjau-badge-{{ $index }}">
                                {{ __($badge) }}
                            </flux:badge>
                        </li>
                    @empty
                        <li>
                            <flux:text>{{ __('Belum ada syarat untuk dipratinjau.') }}</flux:text>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="button" variant="ghost" wire:click="closeForm" data-test="jenis-surat-cancel-button">
                    {{ __('Batal') }}
                </flux:button>
                <flux:button type="submit" variant="primary" data-test="jenis-surat-save-button">
                    {{ __('Simpan') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal
        wire:model.self="showForceDeleteConfirm"
        wire:close="closeForceDeleteConfirm"
        class="min-w-[22rem]"
        data-test="jenis-surat-force-delete-modal"
    >
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus permanen?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Jenis surat akan dihapus selamanya dari database. Tindakan ini tidak dapat dibatalkan.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="closeForceDeleteConfirm"
                    data-test="jenis-surat-force-delete-cancel"
                >
                    {{ __('Batal') }}
                </flux:button>
                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="forceDelete"
                    data-test="jenis-surat-force-delete-confirm"
                >
                    {{ __('Hapus Permanen') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

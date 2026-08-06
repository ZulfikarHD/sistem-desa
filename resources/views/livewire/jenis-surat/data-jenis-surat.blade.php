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
                            <span class="line-clamp-2 whitespace-pre-line">{{ $item->persyaratan_dokumen }}</span>
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

    <flux:modal wire:model.self="showForm" wire:close="resetForm" class="md:w-[32rem]" data-test="jenis-surat-form-modal">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg" data-test="jenis-surat-form-title">
                    {{ $editingId ? __('Ubah Jenis Surat') : __('Tambah Jenis Surat') }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ __('Nama surat dan persyaratan dokumen wajib diisi. Deskripsi bersifat opsional.') }}
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

            <flux:textarea
                wire:model="persyaratan_dokumen"
                label="{{ __('Persyaratan Dokumen') }}"
                rows="5"
                placeholder="{{ __("- Fotokopi KTP\n- Fotokopi Kartu Keluarga\n- Surat pengantar RT/RW") }}"
                data-test="jenis-surat-persyaratan-input"
            />

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

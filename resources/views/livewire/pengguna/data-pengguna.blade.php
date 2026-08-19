<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="pengguna-heading">{{ __('Kelola Pengguna') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Daftar akun warga dan admin. Admin dapat menambahkan pengguna baru tanpa melalui halaman registrasi publik.') }}
            </flux:text>
        </div>

        <flux:button
            variant="primary"
            icon="plus"
            :href="route('pengguna.create')"
            wire:navigate
            data-test="pengguna-create-button"
        >
            {{ __('Tambah Pengguna') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="max-w-md flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Cari nama, email, atau NIK...') }}"
                data-test="pengguna-search"
            />
        </div>

        <div class="w-full sm:w-48">
            <flux:select
                wire:model.live="roleFilter"
                label="{{ __('Peran') }}"
                data-test="pengguna-role-filter"
            >
                @foreach ($roleOptions as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    @if ($penggunaList->isEmpty())
        <div
            class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600"
            data-test="pengguna-empty"
        >
            <flux:heading size="sm">{{ __('Belum ada pengguna') }}</flux:heading>
            <flux:text class="mt-2">
                @if (trim($search) !== '' || $roleFilter !== '')
                    {{ __('Tidak ada hasil untuk filter Anda. Coba kata kunci atau peran lain.') }}
                @else
                    {{ __('Tambahkan pengguna pertama agar warga atau petugas desa dapat masuk ke sistem.') }}
                @endif
            </flux:text>
        </div>
    @else
        <flux:table :paginate="$penggunaList" data-test="pengguna-table">
            <flux:table.columns>
                <flux:table.column>{{ __('Nama') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('NIK') }}</flux:table.column>
                <flux:table.column>{{ __('Peran') }}</flux:table.column>
                <flux:table.column>{{ __('Terdaftar') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($penggunaList as $pengguna)
                    <flux:table.row wire:key="pengguna-{{ $pengguna->id }}" data-test="pengguna-row-{{ $pengguna->id }}">
                        <flux:table.cell variant="strong" data-test="pengguna-nama-{{ $pengguna->id }}">
                            {{ $pengguna->name }}
                        </flux:table.cell>
                        <flux:table.cell data-test="pengguna-email-{{ $pengguna->id }}">
                            {{ $pengguna->email }}
                        </flux:table.cell>
                        <flux:table.cell data-test="pengguna-nik-{{ $pengguna->id }}">
                            {{ $pengguna->nik }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                :color="$pengguna->isAdmin() ? 'blue' : 'zinc'"
                                size="sm"
                                data-test="pengguna-role-{{ $pengguna->id }}"
                            >
                                {{ $pengguna->isAdmin() ? __('Admin') : __('Warga') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell data-test="pengguna-created-{{ $pengguna->id }}">
                            {{ $pengguna->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

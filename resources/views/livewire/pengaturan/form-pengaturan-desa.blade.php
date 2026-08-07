<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" data-test="pengaturan-desa-heading">{{ __('Pengaturan Desa') }}</flux:heading>
        <flux:text class="mt-1">
            {{ __('Identitas kantor desa untuk kop bukti pengambilan dan nomor surat. Jam kerja & libur nasional tetap di konfigurasi sistem.') }}
        </flux:text>
    </div>

    <form wire:submit="simpan" class="flex max-w-2xl flex-col gap-6" data-test="pengaturan-desa-form">
        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Identitas Kantor') }}</flux:heading>

            <flux:input wire:model="nama_desa" label="{{ __('Nama Desa') }}" data-test="pengaturan-desa-nama" />
            <flux:input wire:model="kecamatan" label="{{ __('Kecamatan') }}" data-test="pengaturan-desa-kecamatan" />
            <flux:input wire:model="kabupaten" label="{{ __('Kabupaten / Kota') }}" data-test="pengaturan-desa-kabupaten" />
            <flux:input wire:model="provinsi" label="{{ __('Provinsi') }}" data-test="pengaturan-desa-provinsi" />
            <flux:textarea wire:model="alamat_kantor" label="{{ __('Alamat Kantor') }}" rows="2" data-test="pengaturan-desa-alamat" />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="kode_pos" label="{{ __('Kode Pos') }}" data-test="pengaturan-desa-kode-pos" />
                <flux:input wire:model="telepon" label="{{ __('Telepon') }}" data-test="pengaturan-desa-telepon" />
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Penandatangan (arsip)') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Disimpan untuk keperluan administrasi; tidak dicetak pada bukti pengambilan.') }}
            </flux:text>
            <flux:input wire:model="penandatangan_nama" label="{{ __('Nama Penandatangan') }}" data-test="pengaturan-desa-penandatangan-nama" />
            <flux:input wire:model="penandatangan_jabatan" label="{{ __('Jabatan') }}" data-test="pengaturan-desa-penandatangan-jabatan" />
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Kode Nomor Surat') }}</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="kode_klasifikasi" label="{{ __('Kode Klasifikasi') }}" data-test="pengaturan-desa-kode-klasifikasi" />
                <flux:input wire:model="kode_desa" label="{{ __('Kode Desa') }}" data-test="pengaturan-desa-kode-desa" />
            </div>
        </div>

        <div>
            <flux:button type="submit" variant="primary" data-test="pengaturan-desa-simpan">
                {{ __('Simpan Pengaturan') }}
            </flux:button>
        </div>
    </form>
</div>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6" data-test="dashboard-admin">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl" data-test="dashboard-admin-heading">{{ __('Dashboard Admin') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Pantau umur pengajuan di setiap tahap agar tidak ada yang terpendam.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button
                variant="primary"
                icon="clipboard-document-check"
                :href="route('verifikasi.index')"
                wire:navigate
                data-test="dashboard-admin-qa-verifikasi"
            >
                {{ __('Verifikasi Pengajuan Baru') }}
            </flux:button>
            <flux:button
                variant="filled"
                icon="document-text"
                :href="route('surat-diproses.index')"
                wire:navigate
                data-test="dashboard-admin-qa-diproses"
            >
                {{ __('Proses Surat') }}
            </flux:button>
        </div>
    </div>

    @if ($semuaKartuAktifKosong)
        <div
            class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-zinc-600 dark:bg-zinc-900/40"
            data-test="dashboard-admin-empty"
        >
            <flux:heading size="sm">{{ __('Tidak ada pengajuan yang perlu ditangani saat ini.') }}</flux:heading>
        </div>
    @endif

    {{-- Kartu statistik --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-test="dashboard-admin-cards">
        @php
            $kartuList = [
                [
                    'key' => 'diajukan',
                    'label' => __('Menunggu Verifikasi'),
                    'data' => $kartuDiajukan,
                    'test' => 'dashboard-admin-card-diajukan',
                ],
                [
                    'key' => 'diproses',
                    'label' => __('Sedang Diproses'),
                    'data' => $kartuDiproses,
                    'test' => 'dashboard-admin-card-diproses',
                ],
                [
                    'key' => 'siap_diambil',
                    'label' => __('Siap Diambil'),
                    'data' => $kartuSiapDiambil,
                    'test' => 'dashboard-admin-card-siap-diambil',
                ],
                [
                    'key' => 'selesai',
                    'label' => __('Selesai Bulan Ini'),
                    'data' => $kartuSelesaiBulanIni,
                    'test' => 'dashboard-admin-card-selesai',
                ],
            ];
        @endphp

        @foreach ($kartuList as $kartu)
            @php
                $severity = $kartu['data']['severity'];
                $cardClass = match ($severity) {
                    'urgent' => 'border-red-400 bg-red-50 dark:border-red-500/60 dark:bg-red-950/40',
                    'warning' => 'border-amber-400 bg-amber-50 dark:border-amber-500/60 dark:bg-amber-950/30',
                    default => 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900',
                };
            @endphp
            <a
                href="{{ $kartu['data']['href'] }}"
                wire:navigate
                class="block rounded-xl border-2 p-4 transition hover:shadow-sm {{ $cardClass }}"
                data-test="{{ $kartu['test'] }}"
                data-severity="{{ $severity }}"
            >
                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $kartu['label'] }}</flux:text>
                <div class="mt-2 text-3xl font-semibold tracking-tight" data-test="{{ $kartu['test'] }}-total">{{ $kartu['data']['total'] }}</div>
                @if ($kartu['data']['sub_label'])
                    <flux:text
                        class="mt-2 text-sm {{ $severity === 'urgent' ? 'font-medium text-red-700 dark:text-red-300' : ($severity === 'warning' ? 'font-medium text-amber-700 dark:text-amber-300' : '') }}"
                        data-test="{{ $kartu['test'] }}-sub"
                    >
                        {{ $kartu['data']['sub_label'] }}
                    </flux:text>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Perlu Ditindaklanjuti Segera --}}
    @if ($perluDitindaklanjuti->isNotEmpty())
        <section class="space-y-3" data-test="dashboard-admin-urgent-section">
            <flux:heading size="lg">{{ __('Perlu Ditindaklanjuti Segera') }}</flux:heading>

            <flux:table data-test="dashboard-admin-urgent-table">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama Warga') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Sudah berapa lama') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($perluDitindaklanjuti as $item)
                        <flux:table.row
                            wire:key="dashboard-urgent-{{ $item['id'] }}"
                            data-test="dashboard-admin-urgent-row-{{ $item['id'] }}"
                        >
                            <flux:table.cell variant="strong">{{ $item['nomor_pengajuan'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['nama_warga'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['jenis_surat'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="\App\Models\PengajuanSurat::statusBadgeColor($item['status'])">
                                    {{ \App\Models\PengajuanSurat::statusLabel($item['status']) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell data-test="dashboard-admin-urgent-hari-{{ $item['id'] }}">
                                {{ $item['hari'] }} {{ __('hari') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    wire:click="tangani({{ $item['id'] }})"
                                    data-test="dashboard-admin-tangani-{{ $item['id'] }}"
                                >
                                    {{ __('Tangani') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </section>
    @endif

    {{-- Semua Pengajuan Aktif Terbaru --}}
    <section class="space-y-3" data-test="dashboard-admin-aktif-section">
        <flux:heading size="lg">{{ __('Semua Pengajuan Aktif Terbaru') }}</flux:heading>

        @if ($pengajuanAktifTerbaru->isEmpty())
            <flux:text class="text-sm text-zinc-500" data-test="dashboard-admin-aktif-empty">
                {{ __('Belum ada pengajuan aktif.') }}
            </flux:text>
        @else
            <flux:table data-test="dashboard-admin-aktif-table">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nomor Pengajuan') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama Warga') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis Surat') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Di status ini selama') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($pengajuanAktifTerbaru as $item)
                        @php
                            $rowClass = match ($item['severity']) {
                                'urgent' => 'bg-red-50/80 dark:bg-red-950/30',
                                'warning' => 'bg-amber-50/80 dark:bg-amber-950/20',
                                default => '',
                            };
                        @endphp
                        <flux:table.row
                            wire:key="dashboard-aktif-{{ $item['id'] }}"
                            class="{{ $rowClass }}"
                            data-test="dashboard-admin-aktif-row-{{ $item['id'] }}"
                            data-severity="{{ $item['severity'] }}"
                        >
                            <flux:table.cell variant="strong">{{ $item['nomor_pengajuan'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['nama_warga'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['jenis_surat'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="\App\Models\PengajuanSurat::statusBadgeColor($item['status'])">
                                    {{ \App\Models\PengajuanSurat::statusLabel($item['status']) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $item['hari'] }} {{ __('hari') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    wire:click="lihatDetail({{ $item['id'] }})"
                                    data-test="dashboard-admin-detail-{{ $item['id'] }}"
                                >
                                    {{ __('Lihat Detail') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </section>
</div>

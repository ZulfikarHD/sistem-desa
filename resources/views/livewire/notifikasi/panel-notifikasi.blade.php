<div
    wire:poll.30s="refreshNotifikasi"
    x-data="{ open: false }"
    x-on:buka-panel-notifikasi.window="$el.querySelector('[data-test=panel-notifikasi-toggle]')?.click()"
    class="px-2"
    data-test="panel-notifikasi"
>
    <flux:dropdown position="bottom" align="start">
        <flux:button
            variant="ghost"
            size="sm"
            icon="bell"
            class="relative"
            data-test="panel-notifikasi-toggle"
            x-on:click="open = !open"
        >
            @if ($unreadCount > 0)
                <span
                    class="absolute -end-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
                    data-test="panel-notifikasi-badge"
                >
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80 max-w-[calc(100vw-2rem)]">
            <div class="flex items-center justify-between px-3 py-2">
                <flux:heading size="sm">{{ __('Notifikasi') }}</flux:heading>
                @if ($unreadCount > 0)
                    <flux:badge variant="danger" size="sm" data-test="panel-notifikasi-unread-label">
                        {{ $unreadCount }} {{ __('belum dibaca') }}
                    </flux:badge>
                @endif
            </div>

            <flux:menu.separator />

            @forelse ($notifikasiList as $notifikasi)
                <flux:menu.item
                    wire:key="notifikasi-item-{{ $notifikasi->id }}"
                    as="button"
                    type="button"
                    wire:click="bukaNotifikasi({{ $notifikasi->id }})"
                    class="cursor-pointer whitespace-normal text-start {{ $notifikasi->status_baca === \App\Models\Notifikasi::STATUS_BELUM ? 'bg-zinc-50 dark:bg-zinc-800/80' : '' }}"
                    data-test="panel-notifikasi-item-{{ $notifikasi->id }}"
                >
                    <div class="flex flex-col gap-0.5">
                        <span class="text-sm {{ $notifikasi->status_baca === \App\Models\Notifikasi::STATUS_BELUM ? 'font-semibold' : '' }}">
                            {{ $notifikasi->pesan }}
                        </span>
                        <span class="text-xs text-zinc-500">
                            {{ $notifikasi->created_at->diffForHumans() }}
                        </span>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-4 text-center text-sm text-zinc-500" data-test="panel-notifikasi-empty">
                    {{ __('Belum ada notifikasi.') }}
                </div>
            @endforelse

            @if ($notifikasiList->hasPages())
                <flux:menu.separator />
                <div class="flex items-center justify-between px-3 py-2 text-xs">
                    @if ($notifikasiList->onFirstPage())
                        <span class="text-zinc-400">{{ __('Sebelumnya') }}</span>
                    @else
                        <button
                            type="button"
                            wire:click="previousPage('notifPage')"
                            class="cursor-pointer text-zinc-600 hover:underline dark:text-zinc-300"
                            data-test="panel-notifikasi-prev"
                        >
                            {{ __('Sebelumnya') }}
                        </button>
                    @endif

                    <span class="text-zinc-500">
                        {{ $notifikasiList->currentPage() }} / {{ $notifikasiList->lastPage() }}
                    </span>

                    @if ($notifikasiList->hasMorePages())
                        <button
                            type="button"
                            wire:click="nextPage('notifPage')"
                            class="cursor-pointer text-zinc-600 hover:underline dark:text-zinc-300"
                            data-test="panel-notifikasi-next"
                        >
                            {{ __('Berikutnya') }}
                        </button>
                    @else
                        <span class="text-zinc-400">{{ __('Berikutnya') }}</span>
                    @endif
                </div>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>

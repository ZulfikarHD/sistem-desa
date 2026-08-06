<?php

namespace App\Livewire\Notifikasi;

use App\Models\Notifikasi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PanelNotifikasi extends Component
{
    use WithPagination;

    /**
     * Refresh daftar notifikasi (dipanggil wire:poll).
     */
    public function refreshNotifikasi(): void
    {
        $this->resetPage();
    }

    /**
     * Tandai notifikasi dibaca lalu arahkan ke detail pengajuan.
     */
    public function bukaNotifikasi(int $notifikasiId): void
    {
        $notifikasi = Notifikasi::query()
            ->whereKey($notifikasiId)
            ->where('user_id', Auth::id())
            ->first();

        abort_unless($notifikasi, 404);

        if ($notifikasi->status_baca === Notifikasi::STATUS_BELUM) {
            $notifikasi->update(['status_baca' => Notifikasi::STATUS_DIBACA]);
        }

        $this->redirect(route('pengajuan-surat.show', $notifikasi->pengajuan_id), navigate: true);
    }

    public function render(): View
    {
        $userId = Auth::id();

        return view('livewire.notifikasi.panel-notifikasi', [
            'notifikasiList' => Notifikasi::query()
                ->where('user_id', $userId)
                ->latest('created_at')
                ->latest('id')
                ->paginate(5, pageName: 'notifPage'),
            'unreadCount' => Notifikasi::query()
                ->where('user_id', $userId)
                ->where('status_baca', Notifikasi::STATUS_BELUM)
                ->count(),
        ]);
    }
}

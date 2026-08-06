<?php

namespace App\Livewire\Verifikasi;

use App\Models\SuratTerbit;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Scan QR Pengambilan')]
class ScanQrPengambilan extends Component
{
    /** Token QR dari kamera atau input manual. */
    public string $qrToken = '';

    /** Hasil scan terakhir untuk ditampilkan di UI. */
    public ?string $hasilPesan = null;

    public bool $hasilSukses = false;

    /**
     * Proses scan QR pengambilan (US-7.4).
     */
    public function prosesScan(?string $token = null): void
    {
        $tokenUntukScan = trim($token ?? $this->qrToken);

        $this->qrToken = $tokenUntukScan;
        $this->hasilPesan = null;
        $this->hasilSukses = false;

        $this->validate([
            'qrToken' => ['required', 'string', 'min:16', 'max:64'],
        ], [
            'qrToken.required' => 'Token QR wajib diisi.',
            'qrToken.min' => 'Token QR tidak valid.',
            'qrToken.max' => 'Token QR tidak valid.',
        ]);

        $adminId = Auth::id();

        if ($adminId === null) {
            Flux::toast(variant: 'danger', text: 'Sesi admin tidak ditemukan.');

            return;
        }

        $hasil = SuratTerbit::scanUntukPengambilan($tokenUntukScan, $adminId);

        $this->hasilSukses = $hasil['ok'];
        $this->hasilPesan = $hasil['message'];

        if ($hasil['ok']) {
            $this->qrToken = '';
            Flux::toast(variant: 'success', text: $hasil['message']);
        } else {
            Flux::toast(variant: 'danger', text: $hasil['message']);
        }
    }

    public function render(): View
    {
        return view('livewire.verifikasi.scan-qr-pengambilan')
            ->layout('layouts::app');
    }
}

<?php

namespace App\Livewire\Pengajuan;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengajuan Surat Keterangan')]
class FormPengajuanSurat extends Component
{
    /** ID jenis surat terpilih dari dropdown master data. */
    public ?int $jenis_surat_id = null;

    /** Alasan/keperluan pengajuan surat. */
    public string $keperluan = '';

    /** Nomor pengajuan setelah submit berhasil; null = form masih aktif. */
    public ?string $submittedNomor = null;

    /**
     * Simpan pengajuan surat baru dengan nomor otomatis.
     */
    public function submit(): void
    {
        $validated = $this->validate($this->rules(), $this->messages());

        $attempts = 0;

        while ($attempts < 3) {
            try {
                $nomorPengajuan = $this->generateNomorPengajuan();

                PengajuanSurat::query()->create([
                    'user_id' => auth()->id(),
                    'jenis_surat_id' => $validated['jenis_surat_id'],
                    'nomor_pengajuan' => $nomorPengajuan,
                    'keperluan' => $validated['keperluan'],
                    'status' => PengajuanSurat::STATUS_DIAJUKAN,
                    'tanggal_pengajuan' => now()->toDateString(),
                ]);

                $this->submittedNomor = $nomorPengajuan;
                $this->reset(['jenis_surat_id', 'keperluan']);
                $this->resetValidation();

                Flux::toast(
                    variant: 'success',
                    text: 'Pengajuan surat berhasil dikirim dengan nomor '.$nomorPengajuan.'.',
                );

                return;
            } catch (QueryException $exception) {
                if (! $this->isNomorPengajuanCollision($exception)) {
                    throw $exception;
                }

                $attempts++;
            }
        }

        $this->addError('keperluan', 'Gagal membuat nomor pengajuan. Silakan coba lagi.');
    }

    /**
     * Mulai pengajuan baru setelah submit sebelumnya.
     */
    public function createAnother(): void
    {
        $this->submittedNomor = null;
        $this->reset(['jenis_surat_id', 'keperluan']);
        $this->resetValidation();
    }

    /**
     * Generate nomor pengajuan unik harian dalam transaksi DB.
     */
    protected function generateNomorPengajuan(): string
    {
        $datePrefix = now()->format('Ymd');
        $prefix = 'PJ-'.$datePrefix.'-';

        return DB::transaction(function () use ($prefix): string {
            $lastNumber = PengajuanSurat::query()
                ->where('nomor_pengajuan', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('nomor_pengajuan')
                ->value('nomor_pengajuan');

            $sequence = 1;

            if ($lastNumber !== null) {
                $sequence = (int) substr($lastNumber, -4) + 1;
            }

            return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Deteksi pelanggaran unique constraint nomor_pengajuan.
     */
    protected function isNomorPengajuanCollision(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'nomor_pengajuan')
            || str_contains($message, 'unique');
    }

    /**
     * Aturan validasi form pengajuan.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surat', 'id')->whereNull('deleted_at'),
            ],
            'keperluan' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'jenis_surat_id.required' => 'Jenis surat wajib dipilih.',
            'jenis_surat_id.exists' => 'Jenis surat tidak valid atau sudah tidak tersedia.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'keperluan.max' => 'Keperluan maksimal 2000 karakter.',
        ];
    }

    public function render(): View
    {
        return view('livewire.pengajuan.form-pengajuan-surat', [
            'jenisSuratOptions' => JenisSurat::query()
                ->orderBy('nama_surat')
                ->get(['id', 'nama_surat']),
        ])->layout('layouts::app');
    }
}

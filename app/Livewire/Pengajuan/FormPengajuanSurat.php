<?php

namespace App\Livewire\Pengajuan;

use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Pengajuan Surat Keterangan')]
class FormPengajuanSurat extends Component
{
    use WithFileUploads;

    /** Ukuran maksimum unggahan dokumen dalam kilobyte (2MB). */
    public const MAX_FILE_SIZE_KB = 2048;

    /** ID jenis surat terpilih dari dropdown master data. */
    public ?int $jenis_surat_id = null;

    /** Alasan/keperluan pengajuan surat. */
    public string $keperluan = '';

    /** File KTP yang diunggah (sementara sebelum submit). */
    public ?TemporaryUploadedFile $dokumenKtp = null;

    /** File KK yang diunggah (sementara sebelum submit). */
    public ?TemporaryUploadedFile $dokumenKk = null;

    /** Nomor pengajuan setelah submit berhasil; null = form masih aktif. */
    public ?string $submittedNomor = null;

    /** ID pengajuan ditolak yang menjadi referensi ajukan ulang (US-3.4). */
    public ?int $resubmitFromId = null;

    /** Catatan admin dari pengajuan sebelumnya untuk referensi perbaikan. */
    public ?string $catatanAdminReferensi = null;

    /** Nomor pengajuan sebelumnya (hanya tampilan referensi). */
    public ?string $nomorPengajuanSebelumnya = null;

    /**
     * Muat data pra-isi saat ajukan ulang dari pengajuan ditolak.
     */
    public function mount(?PengajuanSurat $pengajuan = null): void
    {
        if ($pengajuan === null) {
            return;
        }

        abort_unless($pengajuan->user_id === auth()->id(), 403);
        abort_unless($pengajuan->status === PengajuanSurat::STATUS_DITOLAK, 404);

        $this->resubmitFromId = $pengajuan->id;
        $this->jenis_surat_id = $pengajuan->jenis_surat_id;
        $this->keperluan = $pengajuan->keperluan;
        $this->catatanAdminReferensi = $pengajuan->catatan_admin;
        $this->nomorPengajuanSebelumnya = $pengajuan->nomor_pengajuan;
    }

    /**
     * Reset unggahan saat jenis surat berubah.
     */
    public function updatedJenisSuratId(): void
    {
        $this->resetDokumenUploads();
    }

    /**
     * Hapus preview KTP.
     */
    public function removeDokumenKtp(): void
    {
        $this->dokumenKtp = null;
        $this->resetValidation('dokumenKtp');
    }

    /**
     * Hapus preview KK.
     */
    public function removeDokumenKk(): void
    {
        $this->dokumenKk = null;
        $this->resetValidation('dokumenKk');
    }

    /**
     * Jenis dokumen wajib berdasarkan persyaratan jenis surat terpilih.
     *
     * @return list<string>
     */
    #[Computed]
    public function requiredDokumenTypes(): array
    {
        if ($this->jenis_surat_id === null) {
            return [];
        }

        $jenisSurat = JenisSurat::query()->find($this->jenis_surat_id);

        if ($jenisSurat === null) {
            return [];
        }

        return $this->detectRequiredDokumenTypes($jenisSurat->persyaratan_dokumen);
    }

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

                DB::transaction(function () use ($validated, $nomorPengajuan): void {
                    $pengajuan = PengajuanSurat::query()->create([
                        'user_id' => auth()->id(),
                        'jenis_surat_id' => $validated['jenis_surat_id'],
                        'nomor_pengajuan' => $nomorPengajuan,
                        'keperluan' => $validated['keperluan'],
                        'status' => PengajuanSurat::STATUS_DIAJUKAN,
                        'tanggal_pengajuan' => now()->toDateString(),
                    ]);

                    $this->storeUploadedDokumen($pengajuan);
                });

                $this->submittedNomor = $nomorPengajuan;
                $this->reset(['jenis_surat_id', 'keperluan', 'dokumenKtp', 'dokumenKk']);
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
        $this->resubmitFromId = null;
        $this->catatanAdminReferensi = null;
        $this->nomorPengajuanSebelumnya = null;
        $this->reset(['jenis_surat_id', 'keperluan', 'dokumenKtp', 'dokumenKk']);
        $this->resetValidation();
    }

    /**
     * Simpan file unggahan ke storage dan catat path di dokumen_persyaratan.
     */
    protected function storeUploadedDokumen(PengajuanSurat $pengajuan): void
    {
        if ($this->dokumenKtp !== null) {
            $this->persistDokumenFile($pengajuan, DokumenPersyaratan::JENIS_KTP, $this->dokumenKtp);
        }

        if ($this->dokumenKk !== null) {
            $this->persistDokumenFile($pengajuan, DokumenPersyaratan::JENIS_KK, $this->dokumenKk);
        }
    }

    /**
     * Simpan satu file ke disk lokal dan buat record dokumen_persyaratan.
     */
    protected function persistDokumenFile(
        PengajuanSurat $pengajuan,
        string $jenisDokumen,
        TemporaryUploadedFile $file,
    ): void {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::lower($jenisDokumen).'_'.Str::uuid()->toString().'.'.$extension;
        $directory = 'pengajuan-dokumen/'.$pengajuan->id;
        $path = $file->storeAs($directory, $filename);

        DokumenPersyaratan::query()->create([
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => $jenisDokumen,
            'file_path' => $path,
        ]);
    }

    /**
     * Deteksi KTP/KK dari teks persyaratan dokumen jenis surat.
     *
     * @return list<string>
     */
    protected function detectRequiredDokumenTypes(string $persyaratanDokumen): array
    {
        $text = strtoupper($persyaratanDokumen);
        $required = [];

        if (str_contains($text, 'KTP')) {
            $required[] = DokumenPersyaratan::JENIS_KTP;
        }

        if (str_contains($text, 'KK') || str_contains($text, 'KARTU KELUARGA')) {
            $required[] = DokumenPersyaratan::JENIS_KK;
        }

        return $required;
    }

    /**
     * Reset semua unggahan dokumen sementara.
     */
    protected function resetDokumenUploads(): void
    {
        $this->dokumenKtp = null;
        $this->dokumenKk = null;
        $this->resetValidation(['dokumenKtp', 'dokumenKk']);
    }

    /**
     * Generate nomor pengajuan unik harian dalam transaksi DB.
     */
    protected function generateNomorPengajuan(): string
    {
        $datePrefix = now()->format('Ymd');
        $prefix = 'PJ-'.$datePrefix.'-';

        return DB::transaction(function () use ($prefix): string {
            $maxSequence = PengajuanSurat::query()
                ->where('nomor_pengajuan', 'like', $prefix.'%')
                ->lockForUpdate()
                ->pluck('nomor_pengajuan')
                ->map(fn (string $nomor): int => (int) substr($nomor, strlen($prefix)))
                ->max();

            $sequence = ($maxSequence ?? 0) + 1;

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
        $optionalFileRules = [
            'nullable',
            'file',
            'mimes:jpg,jpeg,png,pdf',
            'max:'.self::MAX_FILE_SIZE_KB,
        ];

        $requiredFileRules = [
            'required',
            'file',
            'mimes:jpg,jpeg,png,pdf',
            'max:'.self::MAX_FILE_SIZE_KB,
        ];

        $dokumenKtpRules = in_array(DokumenPersyaratan::JENIS_KTP, $this->requiredDokumenTypes, true)
            ? $requiredFileRules
            : $optionalFileRules;

        $dokumenKkRules = in_array(DokumenPersyaratan::JENIS_KK, $this->requiredDokumenTypes, true)
            ? $requiredFileRules
            : $optionalFileRules;

        return [
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surat', 'id')->whereNull('deleted_at'),
            ],
            'keperluan' => ['required', 'string', 'max:2000'],
            'dokumenKtp' => $dokumenKtpRules,
            'dokumenKk' => $dokumenKkRules,
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
            'dokumenKtp.required' => 'Fotokopi KTP wajib diunggah.',
            'dokumenKtp.file' => 'File KTP harus berupa file yang valid.',
            'dokumenKtp.mimes' => 'Format KTP harus JPG, PNG, atau PDF.',
            'dokumenKtp.max' => 'Ukuran file KTP maksimal 2MB.',
            'dokumenKk.required' => 'Fotokopi Kartu Keluarga (KK) wajib diunggah.',
            'dokumenKk.file' => 'File KK harus berupa file yang valid.',
            'dokumenKk.mimes' => 'Format KK harus JPG, PNG, atau PDF.',
            'dokumenKk.max' => 'Ukuran file KK maksimal 2MB.',
        ];
    }

    public function render(): View
    {
        return view('livewire.pengajuan.form-pengajuan-surat', [
            'jenisSuratOptions' => JenisSurat::query()
                ->orderBy('nama_surat')
                ->get(['id', 'nama_surat']),
            'maxFileSizeKb' => self::MAX_FILE_SIZE_KB,
        ])->layout('layouts::app');
    }
}

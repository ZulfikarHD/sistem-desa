<?php

namespace App\Models;

use Database\Factories\LogVerifikasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Log audit keputusan verifikasi pengajuan oleh admin (US-4.3).
 *
 * @property int $id
 * @property int $pengajuan_id
 * @property int $admin_id
 * @property string $aksi
 * @property string|null $keterangan
 * @property Carbon $created_at
 */
#[Fillable([
    'pengajuan_id',
    'admin_id',
    'aksi',
    'keterangan',
])]
class LogVerifikasi extends Model
{
    /** @use HasFactory<LogVerifikasiFactory> */
    use HasFactory;

    public const AKSI_SETUJUI = 'setujui';

    public const AKSI_TOLAK = 'tolak';

    /**
     * Nama tabel sesuai data model Phase 04 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'log_verifikasi';

    /**
     * Hanya kolom created_at; tidak ada updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Pengajuan yang diverifikasi.
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'pengajuan_id');
    }

    /**
     * Admin yang melakukan verifikasi.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

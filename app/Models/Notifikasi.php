<?php

namespace App\Models;

use Database\Factories\NotifikasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Notifikasi in-app perubahan status pengajuan (Phase 05).
 *
 * @property int $id
 * @property int $user_id
 * @property int $pengajuan_id
 * @property string $pesan
 * @property string $status_baca
 * @property Carbon $created_at
 */
#[Fillable([
    'user_id',
    'pengajuan_id',
    'pesan',
    'status_baca',
    'created_at',
])]
class Notifikasi extends Model
{
    /** @use HasFactory<NotifikasiFactory> */
    use HasFactory;

    public const STATUS_BELUM = 'belum';

    public const STATUS_DIBACA = 'dibaca';

    /**
     * Nama tabel sesuai data model Phase 05 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'notifikasi';

    /**
     * Hanya kolom created_at; tidak ada updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Warga penerima notifikasi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pengajuan terkait notifikasi.
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'pengajuan_id');
    }
}

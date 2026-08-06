<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reset status diproses lama (arti US-4.4: admin buka detail) ke diajukan.
     *
     * Hanya baris yang belum diverifikasi (diverifikasi_oleh null) — itu makna
     * "menunggu verifikasi" sebelum US-7.1. Status diproses pasca-disetujui
     * memiliki diverifikasi_oleh terisi dan tidak diubah.
     */
    public function up(): void
    {
        DB::table('pengajuan_surat')
            ->where('status', 'diproses')
            ->whereNull('diverifikasi_oleh')
            ->update(['status' => 'diajukan']);
    }

    /**
     * Tidak dapat di-rollback secara aman — arah migrasi perilaku satu arah.
     */
    public function down(): void
    {
        //
    }
};

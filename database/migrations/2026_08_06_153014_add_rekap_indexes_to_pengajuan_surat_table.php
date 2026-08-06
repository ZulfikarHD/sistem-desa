<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index untuk filter rekap pengajuan (status, jenis, tanggal).
     * jenis_surat_id dan tanggal_pengajuan sudah terindeks dari migrasi awal;
     * status hanya ada di composite (user_id, status) — perlu index terpisah untuk filter admin.
     */
    public function up(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->index('status', 'pengajuan_surat_status_index');
            $table->index(
                ['jenis_surat_id', 'status', 'tanggal_pengajuan'],
                'pengajuan_surat_rekap_filter_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->dropIndex('pengajuan_surat_rekap_filter_index');
            $table->dropIndex('pengajuan_surat_status_index');
        });
    }
};

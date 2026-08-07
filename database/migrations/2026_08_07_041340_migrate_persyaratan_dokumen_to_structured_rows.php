<?php

use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi data sekali jalan: textarea persyaratan_dokumen → baris terstruktur (US-9.2).
 * DDL ada di migration create_jenis_surat_persyaratan_table; file ini hanya DML.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('jenis_surat_persyaratan')) {
            return;
        }

        // Termasuk soft-deleted agar data arsip juga punya baris terstruktur.
        $jenisSuratList = JenisSurat::withTrashed()->orderBy('id')->get();

        foreach ($jenisSuratList as $jenisSurat) {
            $sudahAda = JenisSuratPersyaratan::query()
                ->where('jenis_surat_id', $jenisSurat->id)
                ->exists();

            if ($sudahAda) {
                continue;
            }

            $rows = JenisSuratPersyaratan::parseFromFreeText($jenisSurat->persyaratan_dokumen);

            foreach ($rows as $row) {
                JenisSuratPersyaratan::query()->create([
                    'jenis_surat_id' => $jenisSurat->id,
                    'nama' => $row['nama'],
                    'cara_pemenuhan' => $row['cara_pemenuhan'],
                    'is_wajib' => $row['is_wajib'],
                    'urutan' => $row['urutan'],
                ]);
            }

            $ringkasan = JenisSuratPersyaratan::generateRingkasan($rows);

            // Update tanpa memicu updated_at berlebihan / event model yang tidak perlu.
            DB::table('jenis_surat')
                ->where('id', $jenisSurat->id)
                ->update(['persyaratan_dokumen' => $ringkasan !== '' ? $ringkasan : $jenisSurat->persyaratan_dokumen]);
        }
    }

    /**
     * Rollback menghapus baris terstruktur yang dibuat migrasi ini.
     * Teks ringkasan persyaratan_dokumen tidak dikembalikan ke versi pra-migrasi.
     */
    public function down(): void
    {
        if (! Schema::hasTable('jenis_surat_persyaratan')) {
            return;
        }

        DB::table('jenis_surat_persyaratan')->delete();
    }
};

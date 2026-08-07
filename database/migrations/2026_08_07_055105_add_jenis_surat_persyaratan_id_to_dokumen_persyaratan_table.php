<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dokumen_persyaratan', function (Blueprint $table) {
            // Nama syarat fleksibel (bukan lagi enum pendek KTP/KK saja).
            $table->string('jenis_dokumen', 255)->change();

            $table->foreignId('jenis_surat_persyaratan_id')
                ->nullable()
                ->after('pengajuan_id')
                ->constrained('jenis_surat_persyaratan')
                ->nullOnDelete();

            $table->dropUnique(['pengajuan_id', 'jenis_dokumen']);
            $table->unique(['pengajuan_id', 'jenis_surat_persyaratan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumen_persyaratan', function (Blueprint $table) {
            $table->dropUnique(['pengajuan_id', 'jenis_surat_persyaratan_id']);
            $table->dropConstrainedForeignId('jenis_surat_persyaratan_id');

            $table->string('jenis_dokumen', 10)->change();
            $table->unique(['pengajuan_id', 'jenis_dokumen']);
        });
    }
};

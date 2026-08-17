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
        Schema::create('dokumen_persyaratan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_surat')->cascadeOnDelete();
            $table->foreignId('jenis_surat_persyaratan_id')
                ->nullable()
                ->constrained('jenis_surat_persyaratan')
                ->nullOnDelete();
            $table->string('jenis_dokumen', 255);
            $table->string('file_path', 255);
            $table->timestamps();

            // Nama index dipendekkan: nama otomatis Laravel melebihi batas 64 karakter MySQL.
            $table->unique(
                ['pengajuan_id', 'jenis_surat_persyaratan_id'],
                'dokumen_persyaratan_pengajuan_syarat_unique'
            );
            $table->index('jenis_dokumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_persyaratan');
    }
};

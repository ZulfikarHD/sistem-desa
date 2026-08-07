<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel baris persyaratan terstruktur per jenis surat (US-9.2).
     */
    public function up(): void
    {
        Schema::create('jenis_surat_persyaratan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_surat_id')
                ->constrained('jenis_surat')
                ->cascadeOnDelete();
            $table->string('nama');
            $table->string('cara_pemenuhan', 32);
            $table->boolean('is_wajib')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['jenis_surat_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_surat_persyaratan');
    }
};

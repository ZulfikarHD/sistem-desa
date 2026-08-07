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
        // Tabel singular (konvensi proyek: jenis_surat, surat_terbit).
        Schema::create('pengaturan_desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_desa', 100);
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->string('provinsi', 100);
            $table->string('alamat_kantor', 255);
            $table->string('kode_pos', 10)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('penandatangan_nama', 100);
            $table->string('penandatangan_jabatan', 100);
            $table->string('kode_klasifikasi', 20);
            $table->string('kode_desa', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_desa');
    }
};

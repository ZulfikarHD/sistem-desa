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
        Schema::create('surat_terbit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuan_surat')->cascadeOnDelete();
            $table->string('nomor_surat', 50)->unique();
            $table->string('file_path', 255);
            $table->date('tanggal_terbit');
            $table->date('tanggal_pengambilan')->nullable();
            $table->string('jam_kerja_label', 100)->nullable();
            $table->string('qr_token', 64)->unique();
            $table->string('qr_status', 20)->default('valid');
            $table->timestamp('qr_digunakan_at')->nullable();
            $table->foreignId('qr_digunakan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diterbitkan_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('qr_status');
            $table->index('tanggal_terbit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_terbit');
    }
};

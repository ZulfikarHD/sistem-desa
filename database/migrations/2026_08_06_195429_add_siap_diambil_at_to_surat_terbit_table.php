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
        Schema::table('surat_terbit', function (Blueprint $table) {
            // Dicatat saat admin klik "Siap Diambil" (US-8.6) — sumber waktu timeline US-8.7.
            $table->timestamp('siap_diambil_at')->nullable()->after('tanggal_pengambilan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_terbit', function (Blueprint $table) {
            $table->dropColumn('siap_diambil_at');
        });
    }
};

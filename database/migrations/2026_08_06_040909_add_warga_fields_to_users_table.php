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
        Schema::table('users', function (Blueprint $table) {
            // Field warga sesuai data model Phase 01 (nama tetap memakai kolom `name` bawaan Laravel)
            $table->string('nik', 16)->nullable()->unique()->after('id');
            $table->string('no_telepon', 20)->nullable()->after('email');
            $table->text('alamat')->nullable()->after('no_telepon');
            $table->string('role', 20)->default('warga')->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'no_telepon', 'alamat', 'role']);
        });
    }
};

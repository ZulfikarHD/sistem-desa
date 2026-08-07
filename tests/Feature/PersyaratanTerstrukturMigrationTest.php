<?php

use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use Database\Seeders\JenisSuratSeeder;
use Illuminate\Support\Facades\Schema;

test('migration membuat tabel jenis_surat_persyaratan', function () {
    expect(Schema::hasTable('jenis_surat_persyaratan'))->toBeTrue();
    expect(Schema::hasColumns('jenis_surat_persyaratan', [
        'id',
        'jenis_surat_id',
        'nama',
        'cara_pemenuhan',
        'is_wajib',
        'urutan',
    ]))->toBeTrue();
});

test('parseFromFreeText mengklasifikasi KTP KK opsional dan bawa kantor', function () {
    $teks = implode("\n", [
        '- Fotokopi KTP pemohon',
        '- Fotokopi Kartu Keluarga (KK)',
        '- Surat pengantar RT/RW',
        '- Bukti pendukung (slip gaji, jika ada)',
        '- NPWP atau izin usaha (opsional)',
    ]);

    $rows = JenisSuratPersyaratan::parseFromFreeText($teks);

    expect($rows)->toHaveCount(5)
        ->and($rows[0]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_UNGGAH)
        ->and($rows[0]['is_wajib'])->toBeTrue()
        ->and($rows[1]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_UNGGAH)
        ->and($rows[1]['is_wajib'])->toBeTrue()
        ->and($rows[2]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_BAWA_KANTOR)
        ->and($rows[3]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_BAWA_KANTOR)
        ->and($rows[4]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_BAWA_KANTOR);
});

test('parseFromFreeText KTP dengan frasa jika ada menjadi unggah opsional', function () {
    $rows = JenisSuratPersyaratan::parseFromFreeText('- Fotokopi KTP almarhum (jika ada)');

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_UNGGAH)
        ->and($rows[0]['is_wajib'])->toBeFalse();
});

test('parseFromFreeText teks kosong menghasilkan fallback info', function () {
    $rows = JenisSuratPersyaratan::parseFromFreeText('');

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['cara_pemenuhan'])->toBe(JenisSuratPersyaratan::CARA_INFO)
        ->and($rows[0]['nama'])->toContain('Persyaratan belum diatur');
});

test('migrasi data mengisi baris terstruktur dari persyaratan_dokumen lama', function () {
    // Buat tanpa factory afterCreating agar belum ada baris terstruktur.
    $jenisSurat = JenisSurat::query()->create([
        'nama_surat' => 'Surat Migrasi Manual '.uniqid(),
        'deskripsi' => 'Untuk uji migrasi',
        'persyaratan_dokumen' => implode("\n", [
            '- Fotokopi KTP',
            '- Fotokopi KK',
            '- Surat pengantar RT/RW',
        ]),
    ]);

    expect($jenisSurat->persyaratan()->count())->toBe(0);

    // Simulasikan logic migrasi data US-9.2 (satu kali jalan).
    $rows = JenisSuratPersyaratan::parseFromFreeText($jenisSurat->persyaratan_dokumen);

    foreach ($rows as $row) {
        JenisSuratPersyaratan::query()->create([
            'jenis_surat_id' => $jenisSurat->id,
            ...$row,
        ]);
    }

    $jenisSurat->forceFill([
        'persyaratan_dokumen' => JenisSuratPersyaratan::generateRingkasan($rows),
    ])->save();

    $jenisSurat->refresh();

    expect($jenisSurat->persyaratan)->toHaveCount(3)
        ->and($jenisSurat->persyaratan[0]->cara_pemenuhan)->toBe(JenisSuratPersyaratan::CARA_UNGGAH)
        ->and($jenisSurat->persyaratan[1]->cara_pemenuhan)->toBe(JenisSuratPersyaratan::CARA_UNGGAH)
        ->and($jenisSurat->persyaratan[2]->cara_pemenuhan)->toBe(JenisSuratPersyaratan::CARA_BAWA_KANTOR)
        ->and($jenisSurat->persyaratan_dokumen)->toContain('Fotokopi KTP');
});

test('seeder Domisili dan SKTM menghasilkan baris terstruktur', function () {
    $this->seed(JenisSuratSeeder::class);

    $domisili = JenisSurat::query()->where('nama_surat', 'Surat Keterangan Domisili')->first();
    $sktm = JenisSurat::query()->where('nama_surat', 'Surat Keterangan Tidak Mampu (SKTM)')->first();

    expect($domisili)->not->toBeNull()
        ->and($sktm)->not->toBeNull();

    expect($domisili->persyaratan)->toHaveCount(3)
        ->and($domisili->persyaratan->where('cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)->count())->toBe(2)
        ->and($domisili->persyaratan->where('cara_pemenuhan', JenisSuratPersyaratan::CARA_BAWA_KANTOR)->count())->toBe(1)
        ->and($domisili->persyaratan_dokumen)->not->toBeEmpty();

    expect($sktm->persyaratan->count())->toBeGreaterThanOrEqual(4)
        ->and($sktm->persyaratan->contains(
            fn (JenisSuratPersyaratan $row): bool => $row->cara_pemenuhan === JenisSuratPersyaratan::CARA_UNGGAH
                && $row->is_wajib === false
        ))->toBeTrue()
        ->and($sktm->persyaratan_dokumen)->toContain('Fotokopi KTP');
});

<?php

use App\Models\JenisSurat;

test('jenis surat factory creates valid record', function () {
    $jenisSurat = JenisSurat::factory()->create();

    expect($jenisSurat)->toBeInstanceOf(JenisSurat::class)
        ->and($jenisSurat->nama_surat)->not->toBeEmpty()
        ->and($jenisSurat->exists)->toBeTrue();
});

test('jenis surat uses jenis_surat table', function () {
    expect((new JenisSurat)->getTable())->toBe('jenis_surat');
});

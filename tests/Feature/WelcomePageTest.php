<?php

use App\Models\User;

test('halaman beranda menampilkan brand dan tautan auth untuk guest', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(config('app.name'), false)
        ->assertSee(__('Ajukan surat keterangan desa secara daring, tanpa antre di kantor.'), false)
        ->assertSee(__('Masuk'), false)
        ->assertSee(__('Daftar'), false);
});

test('halaman beranda menampilkan tautan dashboard untuk user terautentikasi', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee(__('Dashboard'), false)
        ->assertDontSee('data-test="welcome-login"', false);
});

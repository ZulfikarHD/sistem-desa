<?php

use App\Livewire\Pengguna\DataPengguna;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from pengguna page', function () {
    $this->get(route('pengguna.index'))
        ->assertRedirect(route('login'));
});

test('warga cannot visit pengguna admin page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('pengguna.index'))
        ->assertForbidden();
});

test('admin can visit pengguna page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('pengguna.index'))
        ->assertOk()
        ->assertSeeLivewire(DataPengguna::class);
});

test('admin can search pengguna by name', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Budi Santoso Khusus']);
    User::factory()->create(['name' => 'Siti Rahayu']);

    Livewire::actingAs($admin)
        ->test(DataPengguna::class)
        ->set('search', 'Budi Santoso')
        ->assertSee('Budi Santoso Khusus')
        ->assertDontSee('Siti Rahayu');
});

test('admin can filter pengguna by role warga', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Warga Filter Test', 'role' => 'warga']);
    User::factory()->admin()->create(['name' => 'Admin Filter Test']);

    Livewire::actingAs($admin)
        ->test(DataPengguna::class)
        ->set('roleFilter', 'warga')
        ->assertSee('Warga Filter Test')
        ->assertDontSee('Admin Filter Test');
});

test('empty search shows empty state when no users match', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(DataPengguna::class)
        ->set('search', 'tidak-ada-nama-ini')
        ->assertSee('Tidak ada hasil untuk filter Anda');
});

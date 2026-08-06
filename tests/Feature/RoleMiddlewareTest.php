<?php

use App\Models\User;

test('guests are redirected to login from warga dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('guests are redirected to login from admin dashboard', function () {
    $this->get(route('dashboard.admin'))
        ->assertRedirect(route('login'));
});

test('warga can visit warga dashboard', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('admin can visit admin dashboard', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard.admin'))
        ->assertOk();
});

test('warga cannot visit admin dashboard', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('dashboard.admin'))
        ->assertForbidden();
});

test('admin cannot visit warga dashboard', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('both roles can visit shared profile settings', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($warga)
        ->get(route('profile.edit'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk();
});

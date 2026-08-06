<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('guests are redirected to the login page from admin dashboard', function () {
    $response = $this->get(route('dashboard.admin'));
    $response->assertRedirect(route('login'));
});

test('authenticated warga can visit the warga dashboard', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Dashboard Warga', false);
});

test('authenticated admin can visit the admin dashboard', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard.admin'));
    $response->assertOk();
    $response->assertSee('Dashboard Admin', false);
});

<?php

use App\Livewire\Pengguna\FormPengguna;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from create pengguna page', function () {
    $this->get(route('pengguna.create'))
        ->assertRedirect(route('login'));
});

test('warga cannot visit create pengguna page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('pengguna.create'))
        ->assertForbidden();
});

test('admin can visit create pengguna page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('pengguna.create'))
        ->assertOk()
        ->assertSeeLivewire(FormPengguna::class);
});

test('admin can create new warga user', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(FormPengguna::class)
        ->set('nik', '3201010101010099')
        ->set('name', 'Warga Baru Admin')
        ->set('no_telepon', '081234567899')
        ->set('alamat', 'Jl. Contoh No. 99')
        ->set('email', 'warga-baru-admin@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('pengguna.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'warga-baru-admin@example.com',
        'role' => 'warga',
        'nik' => '3201010101010099',
        'name' => 'Warga Baru Admin',
    ]);
});

test('admin can create new admin user', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(FormPengguna::class)
        ->set('role', 'admin')
        ->set('nik', '3201010101010088')
        ->set('name', 'Petugas Desa Baru')
        ->set('no_telepon', '081234567888')
        ->set('alamat', 'Kantor Desa Widodaren')
        ->set('email', 'petugas-baru@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('pengguna.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'petugas-baru@example.com',
        'role' => 'admin',
        'nik' => '3201010101010088',
    ]);
});

test('create pengguna rejects duplicate nik', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['nik' => '3201010101010077']);

    Livewire::actingAs($admin)
        ->test(FormPengguna::class)
        ->set('role', 'warga')
        ->set('nik', '3201010101010077')
        ->set('name', 'Duplikat NIK')
        ->set('no_telepon', '081234567877')
        ->set('alamat', 'Jl. Duplikat')
        ->set('email', 'duplikat-nik@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasErrors(['nik']);
});

test('create pengguna rejects duplicate email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'duplikat@example.com']);

    Livewire::actingAs($admin)
        ->test(FormPengguna::class)
        ->set('role', 'warga')
        ->set('nik', '3201010101010066')
        ->set('name', 'Duplikat Email')
        ->set('no_telepon', '081234567866')
        ->set('alamat', 'Jl. Duplikat')
        ->set('email', 'duplikat@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('create pengguna rejects nik that is not 16 digits', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(FormPengguna::class)
        ->set('nik', '12345')
        ->set('name', 'NIK Salah')
        ->set('no_telepon', '081234567855')
        ->set('alamat', 'Jl. Salah')
        ->set('email', 'nik-salah@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasErrors(['nik']);
});

test('create pengguna rejects password confirmation mismatch', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(FormPengguna::class)
        ->set('role', 'warga')
        ->set('nik', '3201010101010055')
        ->set('name', 'Password Beda')
        ->set('no_telepon', '081234567855')
        ->set('alamat', 'Jl. Beda')
        ->set('email', 'password-beda@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'beda-password')
        ->call('save')
        ->assertHasErrors(['password']);
});

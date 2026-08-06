<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertSee('NIK');
    $response->assertSee('Registrasi Akun Warga');
});

test('new warga users can register and are redirected to login', function () {
    $response = $this->post(route('register.store'), [
        'nik' => '3201010101010001',
        'name' => 'Budi Santoso',
        'no_telepon' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1, Desa Contoh',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $user = User::where('email', 'budi@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->nik)->toBe('3201010101010001')
        ->and($user->name)->toBe('Budi Santoso')
        ->and($user->no_telepon)->toBe('081234567890')
        ->and($user->alamat)->toBe('Jl. Merdeka No. 1, Desa Contoh')
        ->and($user->role)->toBe('warga')
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

test('registration rejects nik that is not 16 digits', function () {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'nik' => '12345',
        'name' => 'Budi Santoso',
        'no_telepon' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('nik')
        ->assertRedirect(route('register'));

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('registration rejects duplicate nik', function () {
    User::factory()->create(['nik' => '3201010101010001']);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'nik' => '3201010101010001',
        'name' => 'Budi Lain',
        'no_telepon' => '081234567891',
        'alamat' => 'Jl. Lain No. 2',
        'email' => 'budi.lain@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('nik');
    $this->assertGuest();
});

test('registration rejects duplicate email', function () {
    User::factory()->create(['email' => 'budi@example.com']);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'nik' => '3201010101010002',
        'name' => 'Budi Santoso',
        'no_telepon' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration rejects invalid email format', function () {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'nik' => '3201010101010003',
        'name' => 'Budi Santoso',
        'no_telepon' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'email' => 'bukan-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration rejects password confirmation mismatch', function () {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'nik' => '3201010101010004',
        'name' => 'Budi Santoso',
        'no_telepon' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'beda-password',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

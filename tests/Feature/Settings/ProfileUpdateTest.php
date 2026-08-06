<?php

use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $user = User::factory()->create([
        'alamat' => 'Jl. Merdeka No. 10 Desa Contoh',
    ]);

    $this->actingAs($user);

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Profil')
        ->assertSee($user->nik)
        ->assertSee($user->no_telepon)
        ->assertSee($user->alamat)
        ->assertSee($user->email)
        ->assertSee($user->name);
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('no_telepon', '081298765432')
        ->set('alamat', 'Jl. Baru No. 99')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->no_telepon)->toEqual('081298765432');
    expect($user->alamat)->toEqual('Jl. Baru No. 99');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('nik and role cannot be changed from profile form', function () {
    $user = User::factory()->create([
        'nik' => '3201010101010001',
        'role' => 'warga',
    ]);

    $this->actingAs($user);

    $component = Livewire::test('pages::settings.profile');

    expect(fn () => $component->set('nik', '9999999999999999'))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    expect(fn () => $component->set('role', 'admin'))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    $component
        ->set('name', 'Nama Baru')
        ->set('no_telepon', $user->no_telepon)
        ->set('alamat', $user->alamat)
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->nik)->toBe('3201010101010001');
    expect($user->role)->toBe('warga');
    expect($user->name)->toBe('Nama Baru');
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('no_telepon', $user->no_telepon)
        ->set('alamat', $user->alamat)
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('profile update requires phone and address', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('no_telepon', '')
        ->set('alamat', '')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasErrors(['no_telepon', 'alamat']);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

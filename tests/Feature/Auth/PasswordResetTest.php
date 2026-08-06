<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Lupa Password')
        ->assertSee('Kirim Tautan Reset Password');
});

test('password reset token expires in sixty minutes', function () {
    expect(config('auth.passwords.users.expire'))->toBe(60);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $this->get(route('password.reset', $notification->token))
            ->assertOk()
            ->assertSee('Reset Password');

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create();

    $response = $this->from(route('password.reset', 'token-tidak-valid'))
        ->post(route('password.update'), [
            'token' => 'token-tidak-valid',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasErrors('email');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

test('user can login with new password after reset', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertRedirect(route('login', absolute: false));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password-baru-123',
        ])->assertRedirect(route($user->homeRouteName(), absolute: false));

        return true;
    });
});

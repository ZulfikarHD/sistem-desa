<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Setelah registrasi berhasil: logout (Fortify auto-login) lalu arahkan ke halaman login.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        // Fortify sudah login user; US-1.1 mensyaratkan redirect ke login sebagai guest
        auth()->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()
                ->route('login')
                ->with('status', __('Registrasi berhasil. Silakan masuk dengan akun Anda.'));
    }
}

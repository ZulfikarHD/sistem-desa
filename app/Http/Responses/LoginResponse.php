<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Setelah login berhasil: arahkan ke dashboard sesuai role (US-1.2).
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $home = route($user->homeRouteName());

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'redirect' => $home], 200)
            : redirect()->intended($home);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pastikan user terautentikasi memiliki salah satu role yang diizinkan.
     *
     * Dipakai sebagai alias middleware `role:warga` atau `role:admin`
     * (bisa beberapa role dipisah koma, mis. `role:warga,admin`).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Guest ditangani oleh middleware `auth` di depan — jika tetap sampai sini, tolak.
        if ($user === null) {
            abort(403);
        }

        $allowed = collect($roles)
            ->flatMap(fn (string $role): array => explode(',', $role))
            ->map(fn (string $role): string => trim($role))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($allowed === [] || ! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotGuest
{
    /**
     * Membatasi akun Guest agar hanya memiliki akses baca.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
         * GET, HEAD, dan OPTIONS adalah request baca.
         * Guest tetap diperbolehkan membuka halaman dan melihat data.
         */
        $readOnlyMethods = [
            'GET',
            'HEAD',
            'OPTIONS',
        ];

        if (
            in_array(
                $request->method(),
                $readOnlyMethods,
                true
            )
        ) {
            return $next($request);
        }

        $user = $request->user();

        $isGuest =
            $request->session()->get('access_mode') === 'guest'
            || strtolower((string) $user?->role) === 'guest';

        /*
         * Menolak POST, PUT, PATCH, dan DELETE milik Guest.
         */
        if ($isGuest) {
            abort(
                403,
                'Akun Guest hanya memiliki akses untuk melihat data dan tidak dapat melakukan perubahan.'
            );
        }

        return $next($request);
    }
}
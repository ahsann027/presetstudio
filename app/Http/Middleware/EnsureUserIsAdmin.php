<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Hanya pengguna dengan is_admin = true yang boleh masuk ke area /admin. */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() && $request->user()->is_admin, 403, 'Akses khusus admin.');

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Enforce the app's custom session-based authentication.
     *
     * Optional role parameter restricts to a specific role, e.g.:
     *   'auth.session'        -> any authenticated user or admin
     *   'auth.session:admin'  -> admin only
     *   'auth.session:user'   -> user only
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (! session('auth_role')) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login')->withErrors(['email' => 'Silakan login terlebih dahulu.']);
        }

        if ($role !== null && session('auth_role') !== $role) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Forbidden.'], 403)
                : redirect('/')->withErrors(['email' => 'Anda tidak memiliki akses.']);
        }

        return $next($request);
    }
}

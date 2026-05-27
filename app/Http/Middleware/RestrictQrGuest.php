<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictQrGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('qr_restricted_token') && !session()->has('auth_role')) {
            $allowedToken = session('qr_restricted_token');
            
            // Check if this token still exists in the database
            $exists = \App\Models\AudioBuku::where('qr_token', $allowedToken)->exists();
            if (!$exists) {
                session()->forget('qr_restricted_token');
            } else {
                // Allow landing page (/), login, register, katalog/*, logout, API, static assets
                if (!str_contains($request->path(), '.') && !$request->is('/') && !$request->is('login') && !$request->is('register') && !$request->is('katalog/*') && !$request->is('katalog-audio/*') && !$request->is('logout') && !$request->is('api/*')) {
                    return redirect()->route('audio-books.play', ['slug' => $allowedToken]);
                }
            }
        }

        return $next($request);
    }
}

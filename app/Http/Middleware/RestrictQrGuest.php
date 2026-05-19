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
            
            // Allow any QR play route (qr-audio/*), logout, or API requests, block catalog or other pages
            if (!$request->is('qr-audio/*') && !$request->is('logout') && !$request->is('api/*')) {
                return redirect()->route('audio-books.play', $allowedToken);
            }
        }

        return $next($request);
    }
}

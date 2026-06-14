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
            
            // Check if this token still exists in the database and retrieve the book
            $book = \App\Models\AudioBuku::where('qr_token', $allowedToken)->first();
            if (!$book) {
                session()->forget('qr_restricted_token');
            } else {
                $allowed = false;
                
                // 1. Static files or containing dot
                if (str_contains($request->path(), '.')) {
                    $allowed = true;
                }
                // 2. Global system routes
                elseif ($request->is('/') || $request->is('login') || $request->is('register') || $request->is('logout')) {
                    $allowed = true;
                }
                // 3. Scan route
                elseif ($request->is('scan/*')) {
                    $allowed = true;
                }
                // 4. API and progress sync routes
                elseif ($request->is('api/*') || $request->is('progress/sync')) {
                    $allowed = true;
                }
                // 5. Allowed pages and assets for the specific book
                elseif ($request->is("katalog-audio/{$book->id}") || 
                        $request->is("katalog/{$book->qr_token}") || 
                        $request->is("audio-stream/{$book->id}") || 
                        $request->is("progress/{$book->id}")) {
                    $allowed = true;
                }
                
                if (!$allowed) {
                    // Force redirect back to the book show page they scanned
                    return redirect()->route('katalog.show', ['id' => $book->id]);
                }
            }
        }

        return $next($request);
    }
}

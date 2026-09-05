<?php

namespace App\Http\Middleware;

use App\Models\AudioBuku;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictQrGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('qr_restricted_token') && ! session()->has('auth_role')) {
            $allowedToken = session('qr_restricted_token');

            // Check if this token still exists in the database and retrieve the book
            $book = AudioBuku::where('qr_token', $allowedToken)->first();
            if (! $book) {
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
                // 4. API routes (currently no api/* routes exist; kept for future use)
                elseif ($request->is('api/*')) {
                    $allowed = true;
                }
                // 4b. Read-assist (guest may still analyze text after scanning a book)
                elseif ($request->is('read-assist') || $request->is('proses-teks')) {
                    $allowed = true;
                }
                // 5. Allowed pages and assets for the specific book.
                //    Progress/audio endpoints resolve the book id from the URL param,
                //    so only the scanned book's id is permitted to block IDOR.
                elseif ($request->is("katalog-audio/{$book->id}") ||
                        $request->is("katalog/{$book->qr_token}") ||
                        $request->is("katalog/{$book->slug}") ||
                        $request->is('katalog/*') ||
                        $request->is("audio-stream/{$book->id}") ||
                        $request->is("audio-progress/{$book->id}") ||
                        $request->is("progress/sync/{$book->id}") ||
                        $request->is("progress/{$book->id}")) {
                    $allowed = true;
                }

                if (! $allowed) {
                    // Force redirect back to the book show page they scanned
                    return redirect()->route('katalog.show', ['id' => $book->id]);
                }
            }
        }

        return $next($request);
    }
}

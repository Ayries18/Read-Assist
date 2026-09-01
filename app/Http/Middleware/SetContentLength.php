<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetContentLength
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->headers->has('Content-Length') && method_exists($response, 'getContent')) {
            $content = $response->getContent();

            if ($content !== '') {
                $response->headers->set('Content-Length', (string) strlen($content));
            }
        }

        return $response;
    }
}

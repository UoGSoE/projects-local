<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($bearerToken !== config('projects.api_key')) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

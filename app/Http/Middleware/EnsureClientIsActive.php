<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureClientIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // When authenticating with Sanctum personal access tokens,
        // the authenticated user is the tokenable model (ApiClient).
        $client = $request->user();
        if ($client && $client->status !== 'active') {
            return response()->json(['message' => 'Client is not active.'], 403);
        }

        return $next($request);
    }
}


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
        if (Auth::check()) {
            $client = Auth::user()->tokenable; // Access the ApiClient model via the token relationship
            if ($client && $client->status !== 'active') {
                return response()->json(['message' => 'Client is not active.'], 403);
            }
        }

        return $next($request);
    }
}


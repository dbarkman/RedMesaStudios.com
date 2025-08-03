<?php

namespace App\Http\Middleware;

use App\Models\DeniedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForBannedIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deniedIp = DeniedIp::where('ip_address', $request->ip())->first();

        if ($deniedIp && $deniedIp->banned_until && $deniedIp->banned_until->isFuture()) {
            return response()->json(['message' => 'This IP address has been banned.'], 403);
        }

        return $next($request);
    }
}


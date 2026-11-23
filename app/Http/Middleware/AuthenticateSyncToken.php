<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a sync request through only when it sends
 * `Authorization: Bearer <SYNC_DEV_TOKEN>`. T16 replaces this shared token
 * with a per-terminal token.
 */
class AuthenticateSyncToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.sync.dev_token');
        $given = $request->bearerToken();

        if (! is_string($expected) || $expected === '' || ! is_string($given)
            || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}

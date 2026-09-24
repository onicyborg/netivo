<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CronTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.cron.secret', '');
        $authorization = (string) $request->header('Authorization', '');
        $token = str_starts_with($authorization, 'Bearer ')
            ? substr($authorization, 7)
            : '';

        if ($secret === '' || $token === '' || ! hash_equals($secret, $token)) {
            return response()->json(['message' => 'Token cron tidak valid.'], 401);
        }

        return $next($request);
    }
}

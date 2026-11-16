<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsHrd
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isHrd()) {
            abort(403, 'Akses hanya tersedia untuk tim HRD.');
        }

        return $next($request);
    }
}

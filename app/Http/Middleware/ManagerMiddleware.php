<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->is_manager) {
            abort(403, 'Managers only.');
        }
        return $next($request);
    }
}

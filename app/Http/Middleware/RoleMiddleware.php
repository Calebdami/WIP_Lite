<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect('login');
        }

        $userRole = $request->user()->role->name ?? '';

        if (! in_array($userRole, $roles)) {
            return redirect()->route($userRole . '.dashboard');
        }

        return $next($request);
    }
}

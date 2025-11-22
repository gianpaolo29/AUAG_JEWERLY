<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
       
        if (! $request->user()) {
            return redirect()->route('login');
        }

       
        if ($request->user()->role !== $role) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}

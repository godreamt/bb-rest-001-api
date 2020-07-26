<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $currentUser = \Auth::user();
        if (in_array($currentUser->roles, $roles)) {
            return $next($request);
        }

        return response()->json(['msg' => 'You are not allowed for this operation.'], 400);
    }
}

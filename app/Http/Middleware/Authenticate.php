<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API, return null to prevent redirect and handle with JSON response
        if ($request->expectsJson()) {
            return null;
        }

        // For non-API requests, fallback to a login route if defined
        return route('auth.login');
    }

    /**
     * Handle unauthenticated requests for API.
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            abort(response()->json(['error' => 'Unauthenticated.'], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}

// class Authenticate extends Middleware
// {
//     /**
//      * Get the path the user should be redirected to when they are not authenticated.
//      */
//     protected function redirectTo(Request $request): ?string
//     {
//         return $request->expectsJson() ? null : route('login');
//     }
// }

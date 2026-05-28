<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Use the role effective on the active tenant (falls back to the global column
        // outside tenant context). See docs/multi-tenant-migration.md (Q-ROLES).
        if (! $user || ! in_array($user->contextualRole(), $roles, true)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}

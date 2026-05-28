<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;

class TrainingAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Les admin et sadmin ont toujours accès
        if ($user->role === Role::RemAdmin->value || $user->role === Role::RemSuperAdmin->value) {
            return $next($request);
        }

        // Les client, sclient et aclient ont accès uniquement si is_student est true
        if (in_array($user->role, [Role::Client->value, Role::SClient->value, Role::AClient->value], true) && ! $user->is_student) {
            return response()->json(['message' => 'Unauthorized - No training access'], 403);
        }

        return $next($request);
    }
}

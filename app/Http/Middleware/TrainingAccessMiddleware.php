<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrainingAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Les admin et sadmin ont toujours accès
        if ($user->role === 'admin' || $user->role === 'sadmin') {
            return $next($request);
        }
        
        // Les client et sclient ont accès uniquement si is_student est true
        if (($user->role === 'client' || $user->role === 'sclient') && !$user->is_student) {
            return response()->json(['message' => 'Unauthorized - No training access'], 403);
        }
        
        return $next($request);
    }
}
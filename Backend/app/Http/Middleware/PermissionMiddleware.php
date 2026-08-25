<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
  public function handle(Request $request, Closure $next, $permission)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // Ensure role is loaded properly
    $user->loadMissing('role');

    // Superadmin bypasses all permission checks
    if ($user->is_superadmin) {
        return $next($request);
    }

    // Safe admin check
    if ($user->role && $user->role->name === 'admin') {
        return $next($request);
    }

    if (!$user->hasPermission($permission)) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    return $next($request);
}
}
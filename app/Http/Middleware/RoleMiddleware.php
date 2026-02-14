<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $userRole = Auth::user()->role;

        // Support multi-role: 'admin,kasir'
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        \Log::info('User role: ' . $userRole . ', Allowed roles: ' . implode(',', $allowedRoles));

        if (!in_array($userRole, $allowedRoles)) {
            if ($userRole === 'kasir') {
                return redirect()->route('dashboard_kasir');
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Ambil peran pengguna yang sedang login
        $userRole = Auth::user()->role;
        
        // Log informasi untuk memeriksa peran yang dimiliki pengguna
        \Log::info('User role: ' . $userRole . ', Expected role: ' . $role);

        // Cek apakah peran pengguna sesuai dengan yang diminta
        if ($userRole !== $role) {
            return redirect('/');  // Jika tidak sesuai, arahkan ke halaman utama
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Menampilkan form login
    public function showLoginForm()
    {
        return view('login');
    }

    // Menangani proses login
    public function login(Request $request)
    {
        // Validasi input username dan password
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek apakah login berhasil
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            
            // Pengarahan berdasarkan role pengguna
            if ($user->role == 'admin') {
                return redirect()->route('dashboard');  // Admin dashboard
            } elseif ($user->role == 'kasir') {
                return redirect()->route('dashboard_kasir');  // Kasir dashboard
            }
        }

        // Jika login gagal
        return back()->withErrors(['username' => 'Username atau Password salah!']);
    }

    // Logout pengguna
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}






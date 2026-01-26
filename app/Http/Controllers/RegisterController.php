<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validasi data pendaftaran
        $validated = $request->validate([
            'username' => 'required|unique:users,username|max:50',
            'password' => 'required|min:8|confirmed',
        ]);

        // Enkripsi password sebelum menyimpannya
        $user = new User();
        $user->username = $validated['username'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Setelah berhasil mendaftar, redirect ke halaman login atau home
        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan login.');
    }
}

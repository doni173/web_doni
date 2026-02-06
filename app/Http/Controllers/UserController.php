<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        
        $users = User::when($q, function ($query) use ($q) {
            return $query->where('nama_user', 'like', "%{$q}%")
                         ->orWhere('username', 'like', "%{$q}%");
        })
        ->orderBy('id_user', 'asc')
        ->get();

        return view('user', compact('users', 'q'));
    }

    /**
     * Menampilkan form untuk menambah pengguna.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(Request $request)
    {
        try {
            // Validasi data input
            $request->validate([
                'nama_user' => 'required|string|max:50',
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,kasir',
            ]);

            // Mendapatkan ID terakhir dan membuat ID user baru dengan format USxxx
            $last = User::orderBy('id_user', 'desc')->first();
            $number = $last ? (int) substr($last->id_user, 2) + 1 : 1;
            $id_user = 'US' . str_pad($number, 3, '0', STR_PAD_LEFT);

            // Membuat pengguna baru
            $user = User::create([
                'id_user' => $id_user,
                'nama_user' => $request->nama_user,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Log untuk debugging
            Log::info('User created successfully', ['id_user' => $id_user]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error validasi
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (\Exception $e) {
            // Error lainnya
            Log::error('Error creating user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pengguna: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit pengguna.
     */
    public function edit($id_user)
    {
        $user = User::where('id_user', $id_user)->firstOrFail();
        return view('users.edit', compact('user'));
    }

    /**
     * Mengupdate data pengguna.
     */
    public function update(Request $request, $id_user)
    {
        try {
            // Temukan pengguna berdasarkan id_user
            $user = User::where('id_user', $id_user)->firstOrFail();

            // Validasi data input
            $request->validate([
                'nama_user' => 'required|string|max:50',
                'username' => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
                'password' => 'nullable|string|min:8',
                'role' => 'required|in:admin,kasir',
            ]);

            // Siapkan data untuk update
            $dataToUpdate = [
                'nama_user' => $request->nama_user,
                'username' => $request->username,
                'role' => $request->role,
            ];

            // Jika password diisi, tambahkan password terenkripsi
            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            // Update data pengguna
            $user->update($dataToUpdate);

            Log::info('User updated successfully', ['id_user' => $id_user]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diupdate!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate pengguna: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy($id_user)
    {
        try {
            // Mencari pengguna berdasarkan id_user
            $user = User::where('id_user', $id_user)->firstOrFail();

            // Menghapus pengguna
            $user->delete();

            Log::info('User deleted successfully', ['id_user' => $id_user]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus!');
            
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        }
    }
}
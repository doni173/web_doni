<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ambil nilai pencarian dari query string
        $q = $request->query('q');
        
        // Query untuk mencari pengguna berdasarkan pencarian
        $users = User::when($q, function ($query) use ($q) {
            return $query->where('nama_user', 'like', "%{$q}%")  // Pencarian berdasarkan nama_user
                         ->orWhere('username', 'like', "%{$q}%");  // Atau berdasarkan username
        })
        ->orderBy('id_user', 'asc')  // Urutkan berdasarkan id_user
        ->get();

        return view('user', compact('users', 'q'));  // Mengirim data pengguna ke view 'user'
    }

    /**
     * Menampilkan form untuk menambah pengguna.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');  // Tampilan form untuk menambah pengguna
    }

    /**
     * Menyimpan pengguna baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi data input
        $validated = $request->validate([
            'nama_user' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users,username',  // Pastikan 'username' unik
            'password' => 'required|string|min:8',  // Pastikan password memiliki panjang minimum
            'role' => 'required|in:admin,kasir',  // Role yang diizinkan
        ]);

        // Mendapatkan ID terakhir dan membuat ID user baru dengan format USxxx
        $last = User::orderBy('id_user', 'desc')->first();
        $number = $last ? (int) substr($last->id_user, 2) + 1 : 1;
        $id_user = 'US' . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Enkripsi password sebelum disimpan
        $validated['password'] = bcrypt($request->password);  // Enkripsi password

        User::create([
            'id_user' => $id_user,
            'nama_user' => $request->nama_user,
            'username' => $request->username,
            'password' => $request->password,
            'role' => $request->role,
        ]);
        // Menyimpan pengguna baru  
        User::create($validated);  // Menambahkan data pengguna ke database

        // Redirect kembali dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit pengguna.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Mencari pengguna berdasarkan id_user
        $user = User::findOrFail($id);

        // Menampilkan form edit pengguna dengan data pengguna yang sudah ada
        return view('users.edit', compact('user'));
    }

    /**
     * Mengupdate data pengguna.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi data input
        $validated = $request->validate([
            'nama_user' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users,username', // Menambahkan pengecekan unik untuk username dengan kecuali ID saat update
            'password' => 'nullable|string|min:8', // Password boleh kosong pada saat update
            'role' => 'required|in:admin,kasir',  // Role yang diizinkan
        ]);

        // Temukan pengguna berdasarkan ID
        $user = User::findOrFail($id);

        // Jika password diubah, enkripsi password baru
        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);  // Enkripsi password baru
        } else {                
            unset($validated['password']);  // Jika password tidak diubah, hapus kolom password
        }

        // Update data pengguna
        $user->update($validated);

        // Redirect ke halaman users dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diupdate!');
    }

    /**
     * Menghapus pengguna.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Mencari pengguna berdasarkan id_user
        $user = User::findOrFail($id);

        // Menghapus pengguna
        $user->delete();

        // Redirect ke halaman users dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus!');
    }
}

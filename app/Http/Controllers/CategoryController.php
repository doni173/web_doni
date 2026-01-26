<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Menampilkan daftar kategori
    public function index(Request $request)
    {
        // Ambil nilai pencarian dari query string
        $q = $request->query('q');

        // Query untuk mencari kategori berdasarkan pencarian
        $categories = Category::when($q, function ($query) use ($q) {
            return $query->where('kategori', 'like', "%{$q}%"); // Menyesuaikan untuk pencarian berdasarkan nama kategori
        })
        ->orderBy('id_kategori', 'asc')  // Urutkan berdasarkan Id_Kategori
        ->get();

        // Kembalikan view dengan data kategori dan query string
        return view('category', compact('categories', 'q'));
    }

    // Menyimpan kategori baru
    public function store(Request $request)
    {
        // Validasi data kategori
        $request->validate([
            'kategori' => 'required|string|max:50',
        ]);

        // Menyimpan kategori baru ke database
        $last = Category::orderBy('id_kategori', 'desc')->first();
        $number = $last ? (int) substr($last->id_kategori, 2) + 1 : 1;
        $id_kategori = 'KT' . str_pad($number, 3, '0', STR_PAD_LEFT);

        Category::create([
            'id_kategori' => $id_kategori,
            'kategori' => $request->kategori,
        ]);

        // Redirect ke halaman kategori dengan pesan sukses
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    // Menyimpan perubahan kategori
    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori' => 'required|string|max:50',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui');
    }

    // Menghapus kategori
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus');
    }
}

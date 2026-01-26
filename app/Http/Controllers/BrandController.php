<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // Menampilkan daftar brand
    public function index(Request $request)
    {
        // Ambil nilai pencarian dari query string
        $q = $request->query('q');

        // Query untuk mencari brand berdasarkan pencarian
        $brands = Brand::when($q, function ($query) use ($q) {
            return $query->where('brand', 'like', "%{$q}%"); // Pastikan 'brand' sesuai dengan nama kolom
        })
        ->orderBy('Id_Brand', 'asc')  // Urutkan berdasarkan id_brand
        ->get();

        // Kembalikan view dengan data brand
        return view('brand', compact('brands', 'q')); // Pastikan nama view 'brand.index' sesuai dengan struktur folder Anda
    }

    // Menampilkan form untuk menambah brand
        public function create()
    {
        return view('brands.create'); // Pastikan view 'brands.create' ada
    }

    // Menyimpan brand baru
    public function store(Request $request)
    {
        // Validasi data brand
        $request->validate([
            'brand' => 'required|string|max:50|', // Pastikan kolom sesuai dengan yang ada di tabel
        ]);
        $last = Brand::orderBy('id_brand', 'desc')->first();

        $number = $last
            ? (int) substr($last->id_brand, 2) + 1
            : 1;

        // Membuat id_brand baru dengan format MRxxx
        $id_brand = 'BN' . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Menyimpan brand baru ke database
        Brand::create([
            'id_brand' => $id_brand,
            'brand'    => $request->brand,
        ]);

        // Redirect ke halaman brand dengan pesan sukses
        return redirect()->route('brands.index')->with('success', 'Brand berhasil ditambahkan');
    }

    // Menampilkan form untuk mengedit brand
    public function edit($id)
    {
        // Mencari brand berdasarkan id_brand
        $brand = Brand::findOrFail($id);

        // Menampilkan form edit brand
        return view('brands.edit', compact('brand')); // Pastikan view 'brands.edit' ada
    }

    // Menyimpan perubahan brand
    public function update(Request $request, $id)
    {
        // Validasi data brand
        $request->validate([
            'brand' => 'required|string|max:50', // Pastikan kolom sesuai dengan yang ada di tabel
        ]);

        // Mencari brand berdasarkan id_brand
        $brand = Brand::findOrFail($id);

        // Update data brand
        $brand->update([
            'brand' => $request->brand,
        ]);

        // Redirect ke halaman brand dengan pesan sukses
        return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui');
    }

    // Menghapus brand
    public function destroy($id)
    {
        // Mencari brand berdasarkan id_brand
        $brand = Brand::findOrFail($id);

        // Menghapus brand
        $brand->delete();

        // Redirect ke halaman brand dengan pesan sukses
        return redirect()->route('brands.index')->with('success', 'Brand berhasil dihapus');
    }
}

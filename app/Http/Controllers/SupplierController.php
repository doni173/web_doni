<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Menampilkan daftar supplier dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        // Ambil query pencarian
        $q = $request->query('q');

        // Query supplier dengan kondisi pencarian
        $suppliers = Supplier::when($q, function ($query) use ($q) {
                return $query->where('nama_supplier', 'like', "%{$q}%")
                             ->orWhere('id_supplier', 'like', "%{$q}%")
                             ->orWhere('no_hp', 'like', "%{$q}%");
            })
            ->orderBy('nama_supplier', 'asc')
            ->get();

        // 🔴 PERBAIKAN: Hapus line ini yang meng-overwrite hasil search
        // $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('supplier', compact('suppliers', 'q'));
    }

    /**
     * Menyimpan supplier baru dengan AUTO INCREMENT ID (SP001, SP002, dst).
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:100',
            'no_hp'         => 'required|string|max:20',
        ]);

        // Generate ID SUPPLIER otomatis (SP001, SP002, dst)
        $last = Supplier::orderBy('id_supplier', 'desc')->first();
        $number = $last ? (int) substr($last->id_supplier, 2) + 1 : 1;
        $id_supplier = 'SP' . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Simpan ke database
        Supplier::create([
            'id_supplier'   => $id_supplier,
            'nama_supplier' => $validated['nama_supplier'],
            'no_hp'         => $validated['no_hp'],
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan');
    }

    /**
     * Update data supplier.
     */
    public function update(Request $request, $id_supplier)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:100',
            'no_hp'         => 'required|string|max:20',
        ]);

        // Cari supplier dan update
        $supplier = Supplier::findOrFail($id_supplier);
        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui');
    }

    /**
     * Hapus supplier dari database.
     */
    public function destroy($id_supplier)
    {
        // Cari supplier dan hapus
        $supplier = Supplier::findOrFail($id_supplier);
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus');
    }
}
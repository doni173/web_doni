<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Menampilkan daftar service.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ambil nilai pencarian dari query string
        $q = $request->query('q');
        
        // Query untuk mencari service berdasarkan pencarian
        $services = Service::when($q, function ($query) use ($q) {
            return $query->where('service', 'like', "%{$q}%"); // Pencarian berdasarkan nama service
        })
        ->orderBy('id_service', 'asc')  // Urutkan berdasarkan id_service
        ->get();

        return view('service', compact('services', 'q'));
    }

    /**
     * Menampilkan form untuk menambah service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('services.create'); // Pastikan view 'services.create' ada
    }

    /**
     * Menyimpan service baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi data service
        $request->validate([
            'service' => 'required|string|max:50', // Validasi nama service
            'harga_jual' => 'required|numeric',   // Validasi harga service
            'diskon' => 'required|numeric|min:0|max:100', // Validasi diskon
        ]);

        // Hitung harga setelah diskon
        $harga_setelah_diskon = $request->harga_jual - ($request->harga_jual * $request->diskon / 100);

        // Mendapatkan ID terakhir dan membuat ID service baru dengan format SVxxx
        $last = Service::orderBy('id_service', 'desc')->first();
        $number = $last ? (int) substr($last->id_service, 2) + 1 : 1;
        $id_service = 'SV' . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Menyimpan service baru ke database
        Service::create([
            'id_service' => $id_service,
            'service' => $request->service,
            'harga_jual' => $request->harga_jual,
            'diskon' => $request->diskon,
            'harga_setelah_diskon' => $harga_setelah_diskon, // Simpan harga setelah diskon
        ]);

        // Redirect ke halaman services dengan pesan sukses
        return redirect()->route('services.index')->with('success', 'Service berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit service.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Mencari service berdasarkan id_service
        $service = Service::findOrFail($id);

        // Menampilkan form edit service dengan data service yang sudah ada
        return view('services.edit', compact('service')); // Pastikan view 'services.edit' ada
    }

    /**
     * Menyimpan perubahan service.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi data service
        $request->validate([
            'service' => 'required|string|max:50', // Validasi nama service
            'harga_jual' => 'required|numeric',   // Validasi harga service
            'diskon' => 'required|numeric|min:0|max:100', // Validasi diskon
        ]);

        // Hitung harga setelah diskon
        $harga_setelah_diskon = $request->harga_jual - ($request->harga_jual * $request->diskon / 100);

        // Mencari service berdasarkan id_service
        $service = Service::findOrFail($id);

        // Update data service
        $service->update([
            'service' => $request->service,
            'harga_jual' => $request->harga_jual,
            'diskon' => $request->diskon,
            'harga_setelah_diskon' => $harga_setelah_diskon, // Perbarui harga setelah diskon
        ]);

        // Redirect ke halaman services dengan pesan sukses
        return redirect()->route('services.index')->with('success', 'Service berhasil diperbarui.');
    }

    /**
     * Menghapus service.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Mencari service berdasarkan id_service
        $service = Service::findOrFail($id);

        // Menghapus service
        $service->delete();

        // Redirect ke halaman services dengan pesan sukses
        return redirect()->route('services.index')->with('success', 'Service berhasil dihapus.');
    }
}

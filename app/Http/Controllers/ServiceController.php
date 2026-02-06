<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Menampilkan daftar service dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        // Ambil query pencarian
        $q = $request->query('q');

        // Query service dengan kondisi pencarian
        $services = Service::when($q, function ($query) use ($q) {
                return $query->where('service', 'like', "%{$q}%")
                             ->orWhere('id_service', 'like', "%{$q}%")
                             ->orWhere('harga_jual', 'like', "%{$q}%");
            })
            ->orderBy('service', 'asc')
            ->get();

        // ✅ PASTIKAN tidak ada query lain yang meng-overwrite $services
        // Jangan tambahkan: $services = Service::orderBy('service')->get();

        return view('service', compact('services', 'q'));
    }

    /**
     * Menyimpan service baru dengan AUTO INCREMENT ID (SV001, SV002, dst).
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'service'    => 'required|string|max:100',
            'harga_jual' => 'required|numeric|min:0',
            'diskon'     => 'nullable|numeric|min:0|max:100', // ✅ Ubah ke nullable
        ]);

        // Generate ID SERVICE otomatis (SV001, SV002, dst)
        $last = Service::orderBy('id_service', 'desc')->first();
        $number = $last ? (int) substr($last->id_service, 2) + 1 : 1;
        $id_service = 'SV' . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Hitung harga setelah diskon
        $harga_jual = $validated['harga_jual'];
        $diskon = $validated['diskon'] ?? 0; // ✅ Default 0 jika tidak diisi
        $harga_setelah_diskon = $harga_jual - ($harga_jual * $diskon / 100);

        // Simpan ke database
        Service::create([
            'id_service'           => $id_service,
            'service'              => $validated['service'],
            'harga_jual'           => $harga_jual,
            'diskon'               => $diskon,
            'harga_setelah_diskon' => $harga_setelah_diskon,
        ]);

        return redirect()
            ->route('services.index')
            ->with('success', 'Service berhasil ditambahkan');
    }

    /**
     * Update data service.
     */
    public function update(Request $request, $id_service)
    {
        // Validasi input
        $validated = $request->validate([
            'service'    => 'required|string|max:100',
            'harga_jual' => 'required|numeric|min:0',
            'diskon'     => 'nullable|numeric|min:0|max:100', // ✅ Ubah ke nullable
        ]);

        // Hitung harga setelah diskon
        $harga_jual = $validated['harga_jual'];
        $diskon = $validated['diskon'] ?? 0; // ✅ Default 0 jika tidak diisi
        $harga_setelah_diskon = $harga_jual - ($harga_jual * $diskon / 100);

        // Cari service dan update
        $service = Service::findOrFail($id_service);
        $service->update([
            'service'              => $validated['service'],
            'harga_jual'           => $harga_jual,
            'diskon'               => $diskon,
            'harga_setelah_diskon' => $harga_setelah_diskon,
        ]);

        return redirect()
            ->route('services.index')
            ->with('success', 'Service berhasil diperbarui');
    }

    /**
     * Hapus service dari database.
     */
    public function destroy($id_service)
    {
        // Cari service dan hapus
        $service = Service::findOrFail($id_service);
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Service berhasil dihapus');
    }
}
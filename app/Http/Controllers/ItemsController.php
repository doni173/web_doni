<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    // Menampilkan daftar item
    public function index(Request $request)
    {
        // Ambil nilai pencarian dari query string
        $q = $request->query('q');

        // Query untuk mencari item berdasarkan pencarian
        $items = Item::when($q, function ($query) use ($q) {
            return $query->where('nama_produk', 'like', "%{$q}%");
        })
        ->orderBy('id_produk', 'asc')  // Urutkan berdasarkan id_produk
        ->with(['kategori', 'brand']) // Pastikan kategori dan brand dimuat
        ->get();

        // Ambil semua kategori dan brand
        $categories = Category::all();
        $brands = Brand::all();

        return view('item', compact('items', 'q', 'categories', 'brands'));
    }

    // Menyimpan item baru
    public function store(Request $request)
    {
        // Validasi data item
        $request->validate([
            'nama_produk' => 'required|string|max:100',
            'harga_jual' => 'required|numeric',
            'id_kategori' => 'required|exists:categories,id_kategori',
            'id_brand' => 'required|exists:brands,id_brand',
            'satuan' => 'required|string|max:30',
            'modal' => 'required|numeric',
            'FSN' => 'required|in:F,S,N',
            'diskon' => 'required|numeric|min:0|max:100', // Validasi diskon antara 0 dan 100
        ]);

        // Menyimpan item baru ke database
        $last = Item::orderBy('id_produk', 'desc')->first();
        $number = $last ? (int) substr($last->id_produk, 2) + 1 : 1;
        $id_produk = 'BR' . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Menghitung harga setelah diskon
        $harga_setelah_diskon = $request->harga_jual - ($request->harga_jual * ($request->diskon / 100));

        Item::create([
            'id_produk' => $id_produk,
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $request->id_kategori,
            'id_brand' => $request->id_brand,
            'harga_jual' => $request->harga_jual,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
            'modal' => $request->modal,
            'FSN' => $request->FSN,
            'diskon' => $request->diskon,
            'harga_setelah_diskon' => $harga_setelah_diskon, // Menyimpan harga setelah diskon
        ]);

        return redirect()->route('items.index')->with('success', 'Produk berhasil ditambahkan');
    }

    // Menyimpan perubahan item
    public function update(Request $request, $id)
    {
        // Validasi data item
        $request->validate([
            'nama_produk' => 'required|string|max:100',
            'harga_jual' => 'required|numeric',
            'id_kategori' => 'required|exists:categories,id_kategori',
            'id_brand' => 'required|exists:brands,id_brand',
            'satuan' => 'required|string|max:30',
            'modal' => 'required|numeric',
            'FSN' => 'required|in:F,S,N',
            'diskon' => 'required|numeric|min:0|max:100', // Validasi diskon antara 0 dan 100
        ]);

        $item = Item::findOrFail($id);

        // Menghitung harga setelah diskon
        $harga_setelah_diskon = $request->harga_jual - ($request->harga_jual * ($request->diskon / 100));

        // Update data item
        $item->update([
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $request->id_kategori,
            'id_brand' => $request->id_brand,
            'harga_jual' => $request->harga_jual,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
            'modal' => $request->modal,
            'FSN' => $request->FSN,
            'diskon' => $request->diskon,
            'harga_setelah_diskon' => $harga_setelah_diskon, // Menyimpan harga setelah diskon
        ]);

        return redirect()->route('items.index')->with('success', 'Produk berhasil diperbarui');
    }

    // Menghapus item
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Produk berhasil dihapus');
    }

    public function updateFSN()
    {
        // Ambil semua barang
        $items = Item::all();

        // Hitung total penjualan untuk semua barang dalam periode bulan ini
        $salesData = Sale::with('saleDetails')
            ->whereMonth('tanggal_transaksi', Carbon::now()->month) // Penjualan bulan ini
            ->get();

        // Menghitung jumlah unit terjual per produk
        $salesCountPerItem = [];

        foreach ($salesData as $sale) {
            foreach ($sale->saleDetails as $detail) {
                $productId = $detail->id_produk;
                if (!isset($salesCountPerItem[$productId])) {
                    $salesCountPerItem[$productId] = 0;
                }
                $salesCountPerItem[$productId] += $detail->jumlah;
            }
        }

        // Urutkan produk berdasarkan jumlah penjualan (dari yang terbanyak ke yang sedikit)
        arsort($salesCountPerItem);

        // Hitung total barang dan total penjualan
        $totalItems = count($salesCountPerItem);
        $totalSold = array_sum($salesCountPerItem);

        // Tentukan kategori berdasarkan distribusi persentase
        $fastThreshold = ceil($totalItems * 0.2);  // 20% pertama = Fast
        $slowThreshold = ceil($totalItems * 0.7);  // 50% berikutnya = Slow (sisa 30% menjadi Non-moving)

        // Kategorikan barang berdasarkan distribusi penjualannya
        $rank = 1; // Urutan barang yang dijual

        foreach ($salesCountPerItem as $productId => $sold) {
            // Tentukan kategori FSN berdasarkan posisi dalam urutan
            if ($rank <= $fastThreshold) {
                $fsn = 'F'; // Fast
            } elseif ($rank <= $slowThreshold) {
                $fsn = 'S'; // Slow
            } else {
                $fsn = 'N'; // Non-moving
            }

            // Perbarui kategori FSN untuk item
            $item = Item::find($productId);
            $item->FSN = $fsn;
            $item->save();

            $rank++; // Naikkan urutan untuk produk berikutnya
        }

        return redirect()->route('items.index')->with('success', 'FSN berhasil diupdate.');
    }
}

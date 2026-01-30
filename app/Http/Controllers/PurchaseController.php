<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Menampilkan halaman pembelian
     */
    public function index(Request $request)
    {
        $query = $request->query('q');

        // Ambil data produk dengan pencarian
        $items = Item::when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('nama_produk', 'like', "%{$query}%")
                                   ->orWhere('id_produk', 'like', "%{$query}%");
            })
            ->orderBy('stok', 'asc') // Urutkan berdasarkan stok terendah
            ->paginate(15);

        return view('purchase', compact('items'));
    }

    /**
     * Menampilkan history pembelian
     */
    public function history(Request $request)
    {
        $tanggal = $request->query('tanggal');

        $purchases = Purchase::with('user', 'purchaseDetails.produk')
                    ->when($tanggal, function ($query) use ($tanggal) {
                        return $query->whereDate('tanggal_pembelian', $tanggal);
                    })
                    ->orderBy('tanggal_pembelian', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        return view('purchase_history', compact('purchases'));
    }

    /**
     * Menampilkan detail pembelian
     */
    public function show($id)
    {
        $purchase = Purchase::with(['user', 'purchaseDetails.produk'])
                    ->findOrFail($id);

        return view('purchase_detail', compact('purchase'));
    }

    /**
     * Menyimpan data pembelian
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'tanggal_pembelian' => 'required|date',
            'supplier' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'total_biaya' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.nama' => 'required|string',
            'items.*.stok_lama' => 'required|integer|min:0',
            'items.*.jumlah_beli' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
        ]);

        try {
            // Mulai database transaction
            DB::beginTransaction();

            // ========================================
            // STEP 1: VALIDASI PRODUK
            // ========================================
            foreach ($validated['items'] as $item) {
                $produk = Item::find($item['id']);
                
                if (!$produk) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk "' . $item['nama'] . '" tidak ditemukan di database!'
                    ], 404);
                }
            }

            // ========================================
            // STEP 2: GENERATE ID PEMBELIAN
            // ========================================
            $lastPurchase = Purchase::orderBy('id_pembelian', 'desc')->first();
            $lastNumber = $lastPurchase ? intval(substr($lastPurchase->id_pembelian, 2)) : 0;
            $idPembelian = 'PB' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            // ========================================
            // STEP 3: SIMPAN KE TABEL PURCHASE
            // ========================================
            $purchase = Purchase::create([
                'id_pembelian' => $idPembelian,
                'id_user' => Auth::id() ?? 'USR01',
                'tanggal_pembelian' => $validated['tanggal_pembelian'],
                'supplier' => $validated['supplier'],
                'total_biaya' => $validated['total_biaya'],
                'keterangan' => $validated['keterangan'],
            ]);

            // ========================================
            // STEP 4: SIMPAN DETAIL & UPDATE STOK
            // ========================================
            foreach ($validated['items'] as $item) {
                // Generate ID Detail
                $lastDetail = PurchaseDetail::orderBy('id_detail_pembelian', 'desc')->first();
                $lastDetailNumber = $lastDetail ? intval(substr($lastDetail->id_detail_pembelian, 3)) : 0;
                $idDetail = 'PBD' . str_pad($lastDetailNumber + 1, 5, '0', STR_PAD_LEFT);

                // Simpan detail pembelian
                PurchaseDetail::create([
                    'id_detail_pembelian' => $idDetail,
                    'id_pembelian' => $idPembelian,
                    'id_produk' => $item['id'],
                    'jumlah_beli' => $item['jumlah_beli'],
                    'harga_beli' => $item['harga_beli'],
                    'stok_sebelum' => $item['stok_lama'],
                    'stok_sesudah' => $item['stok_lama'] + $item['jumlah_beli'],
                ]);

                // UPDATE STOK PRODUK - TAMBAH SESUAI JUMLAH PEMBELIAN
                $produk = Item::find($item['id']);
                if ($produk) {
                    $stokLama = $produk->stok;
                    $produk->stok += $item['jumlah_beli'];
                    $produk->save();
                    
                    // Log untuk tracking
                    \Log::info("Stok produk {$produk->nama_produk} ditambah {$item['jumlah_beli']}. Stok lama: {$stokLama}, Stok baru: {$produk->stok}");
                }
            }

            // ========================================
            // STEP 5: COMMIT TRANSACTION
            // ========================================
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan dan stok telah diperbarui!',
                'data' => [
                    'id_pembelian' => $idPembelian,
                    'total_biaya' => $validated['total_biaya'],
                    'total_items' => count($validated['items']),
                ]
            ], 201);

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollBack();

            \Log::error('Error pembelian: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembelian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus data pembelian (opsional)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::with('purchaseDetails')->findOrFail($id);

            // Kembalikan stok produk
            foreach ($purchase->purchaseDetails as $detail) {
                $produk = Item::find($detail->id_produk);
                if ($produk) {
                    $produk->stok -= $detail->jumlah_beli;
                    $produk->save();
                }
            }

            // Hapus detail dan purchase
            PurchaseDetail::where('id_pembelian', $id)->delete();
            $purchase->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dihapus dan stok telah dikembalikan!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pembelian: ' . $e->getMessage()
            ], 500);
        }
    }
}
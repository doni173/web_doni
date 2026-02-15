<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Display purchase history
     */
    public function history(Request $request)
    {
        $query = Purchase::with(['supplier']);

        // Search functionality
        if ($request->has('q') && $request->q != '') {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('id_pembelian', 'like', "%{$searchTerm}%")
                  ->orWhereHas('supplier', function($q) use ($searchTerm) {
                      $q->where('nama_supplier', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Date filter
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tgl_pembelian', $request->tanggal);
        }

        $purchases = $query->orderBy('tgl_pembelian', 'desc')->paginate(15);

        return view('purchase_history', compact('purchases'));
    }

    /**
     * Display purchase detail
     * PENTING: Harus menggunakan eager loading untuk relasi produk!
     */
    public function show($id)
    {
        // CRITICAL: Load semua relasi dengan eager loading
        $purchase = Purchase::with([
            'supplier',
            'purchaseDetails.produk',      // Load relasi produk dari purchase details
            'purchaseDetails.supplier'      // Load relasi supplier dari purchase details
        ])->findOrFail($id);

        return view('purchase_detail_history', compact('purchase'));
    }

    /**
     * Store new purchase
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validasi
            $validated = $request->validate([
                'total_pembelian' => 'required|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required',
                'items.*.nama' => 'required',
                'items.*.supplier_id' => 'required',
                'items.*.stok_lama' => 'required|integer|min:0',
                'items.*.jumlah_beli' => 'required|integer|min:1',
                'items.*.harga' => 'required|numeric|min:0',
            ]);

            // Generate ID Pembelian
            $lastPurchase = Purchase::orderBy('id_pembelian', 'desc')->first();
            $lastNumber = $lastPurchase ? intval(substr($lastPurchase->id_pembelian, 1)) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $idPembelian = 'P' . $newNumber;

            // Ambil supplier dari item pertama (asumsi semua item dari supplier yang sama)
            $firstItem = $validated['items'][0];
            $supplierId = $firstItem['supplier_id'];

            // Create Purchase
            $purchase = Purchase::create([
                'id_pembelian' => $idPembelian,
                'tgl_pembelian' => Carbon::now('Asia/Jakarta'),
                'id_supplier' => $supplierId,
                'total_pembelian' => $validated['total_pembelian']
            ]);

            // Create Purchase Details dan Update Stok
            foreach ($validated['items'] as $index => $item) {
                // Generate ID Detail
                $detailNumber = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $idDetail = $idPembelian . '-' . $detailNumber;

                $stokBaru = $item['stok_lama'] + $item['jumlah_beli'];
                $subtotal = $item['jumlah_beli'] * $item['harga'];

                // Create Purchase Detail
                PurchaseDetail::create([
                    'id_detail_pembelian' => $idDetail,
                    'id_pembelian' => $idPembelian,
                    'id_produk' => $item['id'],
                    'id_supplier' => $item['supplier_id'],
                    'stok_lama' => $item['stok_lama'],
                    'jumlah_beli' => $item['jumlah_beli'],
                    'stok_baru' => $stokBaru,
                    'harga_beli' => $item['harga'],
                    'subtotal' => $subtotal
                ]);

                // Update Stok Produk
                Item::where('id_produk', $item['id'])->increment('stok', $item['jumlah_beli']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan',
                'data' => [
                    'id_pembelian' => $idPembelian
                ],
                'redirect' => route('purchase.history')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembelian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show purchase form
     */
    public function index()
    {
        $items = Item::with('supplier')->where('stok', '>=', 0)->get();
        $suppliers = Supplier::all();
        
        return view('purchase.index', compact('items', 'suppliers'));
    }

    /**
     * Delete purchase transaction
     * PENTING: Stok harus dikurangi kembali sesuai jumlah pembelian
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Load purchase dengan details
            $purchase = Purchase::with('purchaseDetails')->findOrFail($id);

            // Kurangi stok produk sesuai jumlah pembelian
            foreach ($purchase->purchaseDetails as $detail) {
                Item::where('id_produk', $detail->id_produk)
                    ->decrement('stok', $detail->jumlah_beli);
            }

            // Hapus purchase details
            PurchaseDetail::where('id_pembelian', $id)->delete();

            // Hapus purchase
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchase.history')
                ->with('success', 'Transaksi pembelian berhasil dihapus dan stok telah dikurangi kembali');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('purchase.history')
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
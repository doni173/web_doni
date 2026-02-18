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
    public function history(Request $request)
    {
        $query = Purchase::with(['supplier']);

        if ($request->has('q') && $request->q != '') {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('id_pembelian', 'like', "%{$searchTerm}%")
                  ->orWhereHas('supplier', function($q) use ($searchTerm) {
                      $q->where('nama_supplier', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tgl_pembelian', $request->tanggal);
        }

        $purchases = $query->orderBy('tgl_pembelian', 'desc')->paginate(15);

        return view('purchase_history', compact('purchases'));
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'purchaseDetails.produk',
            'purchaseDetails.supplier'
        ])->findOrFail($id);

        return view('purchase_detail_history', compact('purchase'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'total_pembelian'          => 'required|numeric|min:0',
                'items'                    => 'required|array|min:1',
                'items.*.id'               => 'required',
                'items.*.nama'             => 'required',
                'items.*.supplier_id'      => 'required',
                'items.*.stok_lama'        => 'required|integer|min:0',
                'items.*.jumlah_beli'      => 'required|integer|min:1',
                'items.*.harga'            => 'required|numeric|min:0',
            ]);

            // ✅ Generate ID Pembelian — 5 angka: 00001, 00002, dst
            $lastPurchase = Purchase::orderBy('created_at', 'desc')->first();
            $lastNumber   = $lastPurchase ? intval($lastPurchase->id_pembelian) : 0;
            $idPembelian  = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            $firstItem  = $validated['items'][0];
            $supplierId = $firstItem['supplier_id'];

            $purchase = Purchase::create([
                'id_pembelian'    => $idPembelian,
                'tgl_pembelian'   => Carbon::now('Asia/Jakarta'),
                'id_supplier'     => $supplierId,
                'total_pembelian' => $validated['total_pembelian'],
                'id_user'         => auth()->id(),
            ]);

            foreach ($validated['items'] as $index => $item) {
                $idDetail = 'D' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);

                $stokBaru = $item['stok_lama'] + $item['jumlah_beli'];
                $subtotal = $item['jumlah_beli'] * $item['harga'];

                PurchaseDetail::create([
                    'id_detail_pembelian' => $idDetail,
                    'id_pembelian'        => $idPembelian,
                    'id_produk'           => $item['id'],
                    'id_supplier'         => $item['supplier_id'],
                    'stok_lama'           => $item['stok_lama'],
                    'jumlah_beli'         => $item['jumlah_beli'],
                    'stok_baru'           => $stokBaru,
                    'harga_beli'          => $item['harga'],
                    'subtotal'            => $subtotal
                ]);

                Item::where('id_produk', $item['id'])->increment('stok', $item['jumlah_beli']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan',
                'data'    => ['id_pembelian' => $idPembelian],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembelian: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $items     = Item::with('supplier')->where('stok', '>=', 0)->get();
        $suppliers = Supplier::all();

        return view('purchase', compact('items', 'suppliers'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::with('purchaseDetails')->findOrFail($id);

            foreach ($purchase->purchaseDetails as $detail) {
                Item::where('id_produk', $detail->id_produk)
                    ->decrement('stok', $detail->jumlah_beli);
            }

            PurchaseDetail::where('id_pembelian', $id)->delete();
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
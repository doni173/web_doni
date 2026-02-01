<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('q');

        $items = Item::when($query, function ($q) use ($query) {
                $q->where('nama_produk', 'like', "%{$query}%")
                  ->orWhere('id_produk', 'like', "%{$query}%");
            })
            ->orderBy('stok', 'asc')
            ->paginate(15);

        return view('purchase', compact('items'));
    }

    public function store(Request $request)
{
    try {
        DB::beginTransaction();

       $purchase = Purchase::create([
    'tanggal_pembelian' => $request->tanggal_pembelian,
    'nama_supplier'     => $request->supplier,
    'notes'             => $request->keterangan,
    'total_pembelian'   => $request->total_biaya,
    'status'            => 'selesai',
    'created_by'        => Auth::id(),
]);

        foreach ($request->items as $item) {
          PurchaseDetail::create([
    'id_pembelian' => $purchase->id_pembelian,
    'id_produk'    => $item['id'],
    'jumlah'       => $item['jumlah_beli'],
    'harga_beli'   => $item['harga_beli'],
    'subtotal'     => $item['harga_beli'] * $item['jumlah_beli'],
]);

            Item::where('id', $item['id'])->increment(
                'stok',
                $item['jumlah_beli']
            );
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pembelian berhasil disimpan'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

}

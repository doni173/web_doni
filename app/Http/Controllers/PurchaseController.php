<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
    {
        $items = Item::with('supplier')->orderBy('nama_produk', 'asc')->get();
        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();
        
        return view('purchase', compact('items', 'suppliers'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Purchase Store Request:', $request->all());

            $validated = $request->validate([
                'total_pembelian' => 'required|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|string',
                'items.*.nama' => 'required|string',
                'items.*.supplier_id' => 'required|string',
                'items.*.stok_lama' => 'required|integer|min:0',
                'items.*.jumlah_beli' => 'required|integer|min:1',
                'items.*.harga' => 'required|numeric|min:0',
            ], [
                'items.required' => 'Keranjang pembelian tidak boleh kosong!',
                'items.min' => 'Minimal harus ada 1 item dalam keranjang!',
                'total_pembelian.required' => 'Total pembelian harus diisi!',
                'total_pembelian.min' => 'Total pembelian tidak boleh negatif!',
            ]);

            DB::beginTransaction();

            foreach ($validated['items'] as $item) {
                $produk = Item::find($item['id']);
                
                if (!$produk) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk tidak ditemukan!'
                    ], 404);
                }
            }

            $lastPurchase = Purchase::orderBy('id_pembelian', 'desc')->first();
            $lastNumber = $lastPurchase ? intval(substr($lastPurchase->id_pembelian, 1)) : 0;
            $idPembelian = 'P' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            // PERBAIKAN DI SINI - Gunakan format yang eksplisit
            $tanggalPembelian = Carbon::now('Asia/Jakarta');
            $idSupplierUtama = $validated['items'][0]['supplier_id'];

            // Log untuk debugging
            Log::info('Tanggal Pembelian:', [
                'raw' => $tanggalPembelian,
                'formatted' => $tanggalPembelian->format('Y-m-d H:i:s'),
                'timezone' => $tanggalPembelian->timezone->getName()
            ]);

            // GUNAKAN DB::table() untuk insert dengan format eksplisit
            DB::table('purchases')->insert([
                'id_pembelian' => $idPembelian,
                'id_user' => Auth::id() ?? 'US001',
                'id_supplier' => $idSupplierUtama,
                'tgl_pembelian' => $tanggalPembelian->format('Y-m-d H:i:s'), // Format eksplisit
                'total_pembelian' => $validated['total_pembelian'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $index => $item) {
                $lastDetail = PurchaseDetail::orderBy('id_detail_pembelian', 'desc')->first();
                $lastDetailNumber = $lastDetail ? intval(substr($lastDetail->id_detail_pembelian, 2)) : 0;
                $idDetail = 'DP' . str_pad($lastDetailNumber + 1, 4, '0', STR_PAD_LEFT);

                $stokBaru = $item['stok_lama'] + $item['jumlah_beli'];
                $subtotal = $item['jumlah_beli'] * $item['harga'];

                PurchaseDetail::create([
                    'id_detail_pembelian' => $idDetail,
                    'id_pembelian' => $idPembelian,
                    'id_produk' => $item['id'],
                    'id_supplier' => $item['supplier_id'],
                    'stok_lama' => $item['stok_lama'],
                    'jumlah_beli' => $item['jumlah_beli'],
                    'stok_baru' => $stokBaru,
                    'harga_beli' => $item['harga'],
                    'subtotal' => $subtotal,
                ]);

                $produk = Item::find($item['id']);
                if ($produk) {
                    $produk->stok = $stokBaru;
                    $produk->modal = $item['harga'];
                    $produk->save();
                }
            }

            DB::commit();

            Log::info('Pembelian berhasil disimpan:', [
                'id' => $idPembelian,
                'waktu' => $tanggalPembelian->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan!',
                'data' => [
                    'id_pembelian' => $idPembelian,
                    'total_pembelian' => $validated['total_pembelian'],
                    'tanggal_pembelian' => $tanggalPembelian->format('d F Y H:i:s'),
                ],
                'redirect' => route('purchase.index')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation Error:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid!',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembelian: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $tanggal = $request->query('tanggal');

        $purchases = Purchase::with(['user', 'supplier'])
                    ->when($tanggal, function ($query) use ($tanggal) {
                        return $query->whereDate('tgl_pembelian', $tanggal);
                    })
                    ->orderBy('tgl_pembelian', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        return view('purchase_history', compact('purchases'));
    }

    public function show($id)
    {
        $purchase = Purchase::with(['user', 'supplier', 'purchaseDetails.produk', 'purchaseDetails.supplier'])
                    ->findOrFail($id);

        return view('purchase_detail_history', compact('purchase'));
    }
}
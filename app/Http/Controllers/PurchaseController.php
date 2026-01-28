<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Query untuk produk dengan search
            $query = DB::table('produk')
                ->select('id_produk', 'nama_produk', 'harga_beli', 'stok')
                ->where('status', 'aktif');

            // Search functionality
            if ($request->has('q_barang') && $request->q_barang != '') {
                $query->where('nama_produk', 'like', '%' . $request->q_barang . '%');
            }

            $items = $query->orderBy('nama_produk', 'asc')->get();

            return view('purchase.index', compact('items'));
        } catch (Exception $e) {
            Log::error('Error in PurchaseController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'tanggal_pembelian' => 'required|date',
                'nama_supplier' => 'required|string|max:255',
                'no_invoice' => 'required|string|max:100',
                'metode_pembayaran' => 'required|in:tunai,transfer,kredit',
                'total_pembelian' => 'required|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required',
                'items.*.type' => 'required|string',
                'items.*.nama' => 'required|string',
                'items.*.harga_beli' => 'required|numeric|min:0',
                'items.*.jumlah' => 'required|integer|min:1',
                'items.*.total' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Generate ID Pembelian
            $lastPurchase = DB::table('pembelian')
                ->orderBy('id_pembelian', 'desc')
                ->first();

            if ($lastPurchase) {
                $lastNumber = intval(substr($lastPurchase->id_pembelian, 3));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            $idPembelian = 'PB-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

            // Insert ke tabel pembelian
            DB::table('pembelian')->insert([
                'id_pembelian' => $idPembelian,
                'tanggal_pembelian' => $validated['tanggal_pembelian'],
                'nama_supplier' => $validated['nama_supplier'],
                'no_invoice' => $validated['no_invoice'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'total_pembelian' => $validated['total_pembelian'],
                'status' => 'selesai',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert detail pembelian dan update stok
            foreach ($validated['items'] as $item) {
                // Insert detail pembelian
                DB::table('detail_pembelian')->insert([
                    'id_pembelian' => $idPembelian,
                    'id_produk' => $item['id'],
                    'nama_produk' => $item['nama'],
                    'harga_beli' => $item['harga_beli'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update stok produk (tambah stok)
                DB::table('produk')
                    ->where('id_produk', $item['id'])
                    ->increment('stok', $item['jumlah']);

                // Log pergerakan stok
                DB::table('log_stok')->insert([
                    'id_produk' => $item['id'],
                    'jenis_transaksi' => 'pembelian',
                    'referensi_id' => $idPembelian,
                    'jumlah' => $item['jumlah'],
                    'stok_sebelum' => DB::table('produk')->where('id_produk', $item['id'])->value('stok') - $item['jumlah'],
                    'stok_sesudah' => DB::table('produk')->where('id_produk', $item['id'])->value('stok'),
                    'keterangan' => 'Pembelian dari ' . $validated['nama_supplier'],
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembelian berhasil disimpan',
                'data' => [
                    'id_pembelian' => $idPembelian,
                    'tanggal_pembelian' => $validated['tanggal_pembelian'],
                    'nama_supplier' => $validated['nama_supplier'],
                    'total_pembelian' => $validated['total_pembelian'],
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error in PurchaseController@store: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in PurchaseController@store: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $purchase = DB::table('pembelian')
                ->where('id_pembelian', $id)
                ->first();

            if (!$purchase) {
                return back()->with('error', 'Data pembelian tidak ditemukan');
            }

            $details = DB::table('detail_pembelian')
                ->where('id_pembelian', $id)
                ->get();

            return view('purchase.show', compact('purchase', 'details'));
        } catch (Exception $e) {
            Log::error('Error in PurchaseController@show: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Display purchase history/list
     */
    public function history(Request $request)
    {
        try {
            $query = DB::table('pembelian')
                ->orderBy('tanggal_pembelian', 'desc')
                ->orderBy('created_at', 'desc');

            // Search by supplier or invoice
            if ($request->has('q') && $request->q != '') {
                $query->where(function($q) use ($request) {
                    $q->where('nama_supplier', 'like', '%' . $request->q . '%')
                      ->orWhere('no_invoice', 'like', '%' . $request->q . '%')
                      ->orWhere('id_pembelian', 'like', '%' . $request->q . '%');
                });
            }

            // Filter by date range
            if ($request->has('start_date') && $request->start_date != '') {
                $query->where('tanggal_pembelian', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date != '') {
                $query->where('tanggal_pembelian', '<=', $request->end_date);
            }

            // Filter by payment method
            if ($request->has('metode') && $request->metode != '') {
                $query->where('metode_pembayaran', $request->metode);
            }

            $purchases = $query->paginate(20);

            return view('purchase.history', compact('purchases'));
        } catch (Exception $e) {
            Log::error('Error in PurchaseController@history: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Get purchase details before deleting
            $details = DB::table('detail_pembelian')
                ->where('id_pembelian', $id)
                ->get();

            // Restore stock for each item
            foreach ($details as $detail) {
                DB::table('produk')
                    ->where('id_produk', $detail->id_produk)
                    ->decrement('stok', $detail->jumlah);

                // Log stock movement
                DB::table('log_stok')->insert([
                    'id_produk' => $detail->id_produk,
                    'jenis_transaksi' => 'pembatalan_pembelian',
                    'referensi_id' => $id,
                    'jumlah' => -$detail->jumlah,
                    'stok_sebelum' => DB::table('produk')->where('id_produk', $detail->id_produk)->value('stok') + $detail->jumlah,
                    'stok_sesudah' => DB::table('produk')->where('id_produk', $detail->id_produk)->value('stok'),
                    'keterangan' => 'Pembatalan pembelian ' . $id,
                    'created_at' => now(),
                ]);
            }

            // Delete details
            DB::table('detail_pembelian')->where('id_pembelian', $id)->delete();

            // Delete purchase
            DB::table('pembelian')->where('id_pembelian', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembelian berhasil dihapus'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in PurchaseController@destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus transaksi'
            ], 500);
        }
    }
}